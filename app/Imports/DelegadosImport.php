<?php

namespace App\Imports;

use App\Models\Assignment;
use App\Models\ElectoralPrecinct;
use App\Models\Miembro;
use App\Models\Municipality;
use App\Models\Province;
use App\Models\Table;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class DelegadosImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
{
    public bool $dryRun = false;
    public int $inserted = 0;
    public int $updated = 0;
    public int $skipped = 0;
    public int $assignmentsCreated = 0;
    public int $assignmentsUpdated = 0;
    public int $assignmentsSkipped = 0;
    public array $errors = [];

    public function __construct(bool $dryRun = false)
    {
        $this->dryRun = $dryRun;
    }

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $line = $index + 2;

            try {
                $row = $this->normalizeRow($row);

                $ci = trim((string) $this->readField($row, ['ci', 'cedula', 'c_i']));
                $nombres = trim((string) $this->readField($row, ['nombres', 'nombre']));

                if ($ci === '' || $nombres === '') {
                    $this->skipped++;
                    $this->errors[] = "Fila {$line}: CI y Nombres son obligatorios.";
                    continue;
                }

                $generoRaw = strtoupper(trim((string) $this->readField($row, ['genero'], 'MASCULINO')));
                $genero = in_array($generoRaw, ['MASCULINO', 'FEMENINO'], true) ? $generoRaw : 'MASCULINO';
                $correo = $this->normalizeEmail($this->readField($row, ['correo_electronico', 'email', 'correo']), $line);

                $provinceId = $this->resolveProvinceId($row);
                $municipalityId = $this->resolveMunicipalityId($row, $provinceId);
                $precinctId = $this->resolvePrecinctId($row, $municipalityId);

                $miembro = Miembro::where('ci', $ci)->first();
                $isNew = !$miembro;
                if ($isNew) {
                    $miembro = new Miembro();
                }

                $miembro->ci = $ci;
                $miembro->nombres = $nombres;
                $miembro->app = trim((string) $this->readField($row, ['app', 'apellido_paterno', 'paterno']));
                $miembro->apm = trim((string) $this->readField($row, ['apm', 'apellido_materno', 'materno']));
                $miembro->genero = $genero;
                $miembro->fecnac = $this->normalizeDate($this->readField($row, ['fecnac', 'fecha_nacimiento']), $line);
                $miembro->celular = trim((string) $this->readField($row, ['celular', 'telefono']));
                $miembro->correo_electronico = $correo;
                $miembro->obs = trim((string) $this->readField($row, ['obs', 'observaciones']));

                $miembro->recintovot = trim((string) $this->readField($row, ['recintovot']));
                $miembro->mesavot = trim((string) $this->readField($row, ['mesavot']));
                $miembro->agrupa = trim((string) $this->readField($row, ['agrupa']));
                $miembro->estado = trim((string) $this->readField($row, ['estado'], 'ACTIVO'));
                $miembro->delegado = trim((string) $this->readField($row, ['delegado']));

                $miembro->province_id = $provinceId;
                $miembro->municipality_id = $municipalityId;
                $miembro->electoral_precinct_id = $precinctId;
                $miembro->table_id = null;
                if (!$this->dryRun) {
                    $miembro->save();
                }

                if ($isNew) {
                    $this->inserted++;
                } else {
                    $this->updated++;
                }

                $this->applyAssignmentIfAny($row, $miembro, $precinctId, $line, $isNew);
            } catch (\Throwable $e) {
                $this->skipped++;
                $this->errors[] = "Fila {$line}: {$e->getMessage()}";
            }
        }
    }

    private function applyAssignmentIfAny($row, Miembro $miembro, ?int $precinctId, int $line, bool $isNewMember): void
    {
        [$scopeRaw, $roleRaw] = $this->deriveAssignmentData($row);
        $scope = strtoupper(trim((string) $scopeRaw));

        if ($scope === '' && $roleRaw === '') {
            return;
        }

        $role = $this->normalizeRole($roleRaw);
        if ($scope === '' && $role !== null) {
            $scope = in_array($role, ['JEFE_DE_RECINTO', 'MONITOR_RADAR'], true) ? 'RECINTO' : 'MESA';
        }

        if (!in_array($scope, ['RECINTO', 'MESA'], true)) {
            $this->assignmentsSkipped++;
            $this->errors[] = "Fila {$line}: assignment_scope debe ser RECINTO o MESA.";
            return;
        }

        if (!$precinctId) {
            $this->assignmentsSkipped++;
            $this->errors[] = "Fila {$line}: para asignar rol se requiere electoral_precinct_id o recinto válido.";
            return;
        }

        if ($role === null) {
            $this->assignmentsSkipped++;
            $this->errors[] = "Fila {$line}: assignment_role inválido ({$roleRaw}).";
            return;
        }

        $allowedByScope = [
            'RECINTO' => ['JEFE_DE_RECINTO', 'MONITOR_RADAR'],
            'MESA' => ['DELEGADO_PROPIETARIO', 'DELEGADO_SUPLENTE'],
        ];
        if (!in_array($role, $allowedByScope[$scope], true)) {
            $this->assignmentsSkipped++;
            $this->errors[] = "Fila {$line}: role {$role} no corresponde al scope {$scope}.";
            return;
        }

        $tableId = null;
        if ($scope === 'MESA') {
            $tableId = $this->resolveTableId($row, $precinctId);
            if (!$tableId) {
                $this->assignmentsSkipped++;
                $this->errors[] = "Fila {$line}: para scope MESA debe indicar table_id o mesa_numero válido.";
                return;
            }
        }

        // Evita colisión de rol en la misma ubicación.
        $existsSameRole = Assignment::where('scope', $scope)
            ->where('electoral_precinct_id', $precinctId)
            ->where('table_key', $scope === 'MESA' ? (int) $tableId : 0)
            ->where('role', $role)
            ->when(!$isNewMember, fn($q) => $q->where('miembro_id', '!=', $miembro->id))
            ->exists();

        if ($existsSameRole) {
            $this->assignmentsSkipped++;
            $this->errors[] = "Fila {$line}: el rol {$role} ya está ocupado en esa ubicación.";
            return;
        }

        $assignment = null;
        if (!$isNewMember) {
            $assignment = Assignment::where('scope', $scope)
                ->where('miembro_id', $miembro->id)
                ->first();
        }

        $isNew = !$assignment;
        if ($isNew && !$this->dryRun) {
            $assignment = new Assignment();
            $assignment->scope = $scope;
            $assignment->miembro_id = $miembro->id;
        }

        if (!$this->dryRun) {
            $assignment->electoral_precinct_id = $precinctId;
            $assignment->table_id = $scope === 'MESA' ? $tableId : null;
            $assignment->table_key = $scope === 'MESA' ? (int) $tableId : 0;
            $assignment->role = $role;
            $assignment->save();
        }

        if ($isNew) {
            $this->assignmentsCreated++;
        } else {
            $this->assignmentsUpdated++;
        }
    }

    private function normalizeRole(string $value): ?string
    {
        $text = Str::of($value)->upper()->ascii()->replace(['-', '  '], ['_', ' '])->trim();
        $textUnderscore = str_replace(' ', '_', (string) $text);

        $map = [
            'JEFE_DE_RECINTO' => 'JEFE_DE_RECINTO',
            'JEFE_RECINTO' => 'JEFE_DE_RECINTO',
            'MONITOR_RADAR' => 'MONITOR_RADAR',
            'MONITOR' => 'MONITOR_RADAR',
            'DELEGADO_PROPIETARIO' => 'DELEGADO_PROPIETARIO',
            'DELEGADO_DE_MESA_PROPIETARIO' => 'DELEGADO_PROPIETARIO',
            'DELEGADO_SUPLENTE' => 'DELEGADO_SUPLENTE',
            'DELEGADO_DE_MESA_SUPLENTE' => 'DELEGADO_SUPLENTE',
        ];

        return $map[$textUnderscore] ?? null;
    }

    private function resolveTableId($row, int $precinctId): ?int
    {
        $tableId = $this->toNullableInt($this->readField($row, ['table_id']));
        if ($tableId) {
            $exists = Table::where('id', $tableId)
                ->where('electoral_precinct_id', $precinctId)
                ->exists();
            return $exists ? $tableId : null;
        }

        $tableNumber = trim((string) $this->readField($row, ['mesa_numero', 'mesavot']));
        if ($tableNumber === '') {
            return null;
        }

        $table = Table::where('electoral_precinct_id', $precinctId)
            ->where('table_number', $tableNumber)
            ->first();

        return $table ? (int) $table->id : null;
    }

    private function deriveAssignmentData($row): array
    {
        $scopeRaw = trim((string) $this->readField($row, ['assignment_scope']));
        $roleRaw = trim((string) $this->readField($row, ['assignment_role']));

        if ($scopeRaw !== '' || $roleRaw !== '') {
            return [$scopeRaw, $roleRaw];
        }

        // Compatibilidad con planillas antiguas: columna "delegado"
        $legacy = Str::of((string) $this->readField($row, ['delegado']))->upper()->ascii()->trim()->toString();
        if ($legacy === '') {
            return ['', ''];
        }

        $map = [
            'JEFE RECINTO' => ['RECINTO', 'JEFE_DE_RECINTO'],
            'JEFE DE RECINTO' => ['RECINTO', 'JEFE_DE_RECINTO'],
            'MONITOR RADAR' => ['RECINTO', 'MONITOR_RADAR'],
            'MONITOR / RADAR' => ['RECINTO', 'MONITOR_RADAR'],
            'DELEGADO MESA' => ['MESA', 'DELEGADO_PROPIETARIO'],
            'DELEGADO DE MESA' => ['MESA', 'DELEGADO_PROPIETARIO'],
            'DELEGADO PROPIETARIO' => ['MESA', 'DELEGADO_PROPIETARIO'],
            'DELEGADO SUPLENTE' => ['MESA', 'DELEGADO_SUPLENTE'],
            'SUPLENTE' => ['MESA', 'DELEGADO_SUPLENTE'],
        ];

        return $map[$legacy] ?? ['', ''];
    }

    private function resolveProvinceId($row): ?int
    {
        $id = $this->toNullableInt($this->readField($row, ['province_id']));
        if ($id) {
            return Province::where('id', $id)->exists() ? $id : null;
        }

        $name = trim((string) $this->readField($row, ['provincia', 'province']));
        if ($name === '') {
            return null;
        }

        $province = Province::where('name', $name)->first();
        if (!$province) {
            throw new \RuntimeException("Provincia no encontrada: {$name}");
        }
        return (int) $province->id;
    }

    private function resolveMunicipalityId($row, ?int $provinceId): ?int
    {
        $id = $this->toNullableInt($this->readField($row, ['municipality_id']));
        if ($id) {
            return Municipality::where('id', $id)->exists() ? $id : null;
        }

        $name = trim((string) $this->readField($row, ['municipio', 'municipality']));
        if ($name === '') {
            return null;
        }

        $q = Municipality::where('name', $name);
        if ($provinceId) {
            $q->where('province_id', $provinceId);
        }
        $municipality = $q->first();
        if (!$municipality) {
            throw new \RuntimeException("Municipio no encontrado: {$name}");
        }
        return (int) $municipality->id;
    }

    private function resolvePrecinctId($row, ?int $municipalityId): ?int
    {
        $id = $this->toNullableInt($this->readField($row, ['electoral_precinct_id']));
        if ($id) {
            return ElectoralPrecinct::where('id', $id)->exists() ? $id : null;
        }

        $name = trim((string) $this->readField($row, ['recinto', 'electoral_precinct']));
        if ($name === '') {
            return null;
        }

        $q = ElectoralPrecinct::where('name', $name);
        if ($municipalityId) {
            $q->where('municipality_id', $municipalityId);
        }
        $precinct = $q->first();
        if (!$precinct) {
            throw new \RuntimeException("Recinto no encontrado: {$name}");
        }
        return (int) $precinct->id;
    }

    private function toNullableInt($value): ?int
    {
        if ($value === null) {
            return null;
        }
        $text = trim((string) $value);
        if ($text === '') {
            return null;
        }
        if (!is_numeric($text)) {
            return null;
        }
        return (int) $text;
    }

    private function normalizeEmail($value, int $line): ?string
    {
        $email = trim((string) ($value ?? ''));
        if ($email === '') {
            return null;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \RuntimeException("Correo electronico invalido: {$email}");
        }

        return mb_strtolower($email);
    }

    private function normalizeDate($value, int $line): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_string($value)) {
            $value = trim($value);
            if ($value === '') {
                return null;
            }
        }

        try {
            if (is_numeric($value)) {
                return ExcelDate::excelToDateTimeObject($value)->format('Y-m-d');
            }

            $text = trim((string) $value);
            if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $text)) {
                return Carbon::createFromFormat('d/m/Y', $text)->format('Y-m-d');
            }

            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $text)) {
                return $text;
            }

            return Carbon::parse($text)->format('Y-m-d');
        } catch (\Throwable $e) {
            throw new \RuntimeException("Fecha invalida en fecnac: {$value}");
        }
    }

    private function normalizeRow($row): array
    {
        $data = $row instanceof Collection ? $row->toArray() : (array) $row;
        if ($this->hasNumericKeys($data)) {
            $data = $this->mapNumericRowToTemplateColumns($data);
        }

        $normalized = [];

        foreach ($data as $key => $value) {
            $normalized[$this->normalizeKey((string) $key)] = $value;
        }

        return $normalized;
    }

    private function normalizeKey(string $key): string
    {
        $key = preg_replace('/^\xEF\xBB\xBF/', '', $key) ?? $key;
        $key = trim($key);
        return Str::of($key)
            ->lower()
            ->ascii()
            ->replace([' ', '-', '.', '/', '\\'], '_')
            ->replace('__', '_')
            ->trim('_')
            ->toString();
    }

    private function readField(array $row, array $aliases, $default = null)
    {
        foreach ($aliases as $alias) {
            $key = $this->normalizeKey($alias);
            if (array_key_exists($key, $row)) {
                return $row[$key];
            }
        }

        return $default;
    }

    private function hasNumericKeys(array $data): bool
    {
        if ($data === []) {
            return false;
        }

        foreach (array_keys($data) as $key) {
            if (!is_int($key) && !ctype_digit((string) $key)) {
                return false;
            }
        }

        return true;
    }

    private function mapNumericRowToTemplateColumns(array $data): array
    {
        $columns = [
            'ci',
            'nombres',
            'app',
            'apm',
            'genero',
            'fecnac',
            'celular',
            'correo_electronico',
            'obs',
            'province_id',
            'municipality_id',
            'electoral_precinct_id',
            'provincia',
            'municipio',
            'recinto',
            'table_id',
            'mesa_numero',
            'assignment_scope',
            'assignment_role',
            'estado',
            'agrupa',
            'delegado',
        ];

        $mapped = [];
        foreach (array_values($data) as $index => $value) {
            $mapped[$columns[$index] ?? (string) $index] = $value;
        }

        return $mapped;
    }
}
