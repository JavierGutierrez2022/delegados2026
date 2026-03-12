<?php

namespace App\Imports;

use App\Models\District;
use App\Models\ElectoralPrecinct;
use App\Models\Municipality;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class DistrictsByRecintoImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
{
    public bool $dryRun = false;

    public int $rowsProcessed = 0;
    public int $districtCreated = 0;
    public int $districtUpdated = 0;
    public int $precinctUpdated = 0;
    public int $skipped = 0;
    public array $errors = [];

    public function __construct(bool $dryRun = false)
    {
        $this->dryRun = $dryRun;
    }

    public function collection(Collection $rows): void
    {
        if ($rows->isEmpty()) {
            $this->errors[] = 'El archivo no contiene filas de datos.';
            return;
        }

        $first = $rows->first();
        $headersFound = is_array($first) ? array_keys($first) : $first->keys()->all();
        $headersNormalized = array_map(fn ($h) => $this->normalizeKey((string) $h), $headersFound);

        if (!$this->hasAnyAlias($headersNormalized, ['distrito']) || !$this->hasAnyAlias($headersNormalized, ['recinto'])) {
            $this->errors[] = 'Encabezados invalidos. Deben existir las columnas "distrito" y "recinto". Encabezados detectados: ' . implode(', ', $headersFound);
            return;
        }

        $tarijaMunicipality = $this->resolveTarijaMunicipality();
        if (!$tarijaMunicipality) {
            $this->errors[] = 'No se encontro el municipio "Tarija". Verifique catalogos antes de importar.';
            return;
        }

        $municipalityId = (int) $tarijaMunicipality->id;
        $precinctIndex = $this->buildPrecinctIndex($municipalityId);
        $districtIndex = $this->buildDistrictIndex($municipalityId);
        $seenRecintos = [];

        foreach ($rows as $index => $row) {
            $line = $index + 2;
            $this->rowsProcessed++;
            $ctx = $this->rowContext($row);

            try {
                $districtName = $this->readField($row, ['distrito']);
                $precinctName = $this->readField($row, ['recinto']);

                if ($districtName === '' || $precinctName === '') {
                    $this->skipped++;
                    $this->errors[] = "Fila {$line}: distrito y recinto son obligatorios. {$ctx}";
                    continue;
                }

                $dupKey = $this->normalizeKey($precinctName);
                if (isset($seenRecintos[$dupKey])) {
                    $this->skipped++;
                    $this->errors[] = "Fila {$line}: recinto repetido en archivo (ya cargado en fila {$seenRecintos[$dupKey]}). {$ctx}";
                    continue;
                }
                $seenRecintos[$dupKey] = $line;

                $precinctMatches = $precinctIndex[$dupKey] ?? [];

                if (count($precinctMatches) === 0) {
                    if ($this->precinctExistsInMunicipality($municipalityId, $precinctName)) {
                        $this->skipped++;
                        $this->errors[] = "Fila {$line}: el recinto existe pero no esta ACTIVO en el municipio Tarija. {$ctx}";
                        continue;
                    }
                    $this->skipped++;
                    $this->errors[] = "Fila {$line}: el recinto no existe en el municipio Tarija. {$ctx}";
                    continue;
                }

                if (count($precinctMatches) > 1) {
                    $this->skipped++;
                    $this->errors[] = "Fila {$line}: existe mas de un recinto con ese nombre en el municipio Tarija. {$ctx}";
                    continue;
                }

                $precinct = $precinctMatches[0];
                $districtKey = $this->normalizeKey($districtName);

                if ($this->dryRun) {
                    $district = $districtIndex[$districtKey] ?? null;

                    $districtId = $district ? (int) $district->id : -1 * $line;
                    if (!$district) {
                        $this->districtCreated++;
                    } elseif (strtoupper((string) $district->state) !== 'ACTIVO') {
                        $this->districtUpdated++;
                    }

                    $needsPrecinctUpdate = ((int) ($precinct->district_id ?? 0) !== $districtId);
                    if (!$needsPrecinctUpdate && Schema::hasColumn('electoral_precincts', 'distric_number')) {
                        $needsPrecinctUpdate = mb_strtolower((string) ($precinct->distric_number ?? '')) !== mb_strtolower($districtName);
                    }
                    if ($needsPrecinctUpdate) {
                        $this->precinctUpdated++;
                    }

                    continue;
                }

                DB::transaction(function () use ($municipalityId, $districtName, $districtKey, $precinct, &$districtIndex) {
                    $district = $districtIndex[$districtKey] ?? null;
                    if (!$district) {
                        $district = District::firstOrCreate(
                            [
                                'municipality_id' => $municipalityId,
                                'name' => $districtName,
                            ],
                            [
                                'state' => 'ACTIVO',
                            ]
                        );
                    }

                    if ($district->wasRecentlyCreated) {
                        $this->districtCreated++;
                    } elseif (strtoupper((string) $district->state) !== 'ACTIVO') {
                        $district->state = 'ACTIVO';
                        $district->save();
                        $this->districtUpdated++;
                    }

                    $payload = [];
                    if (Schema::hasColumn('electoral_precincts', 'district_id')) {
                        $payload['district_id'] = (int) $district->id;
                    }
                    if (Schema::hasColumn('electoral_precincts', 'distric_number')) {
                        $districtNumber = $this->extractDistrictNumber($districtName);
                        if ($districtNumber !== null) {
                            $payload['distric_number'] = $districtNumber;
                        }
                    }

                    if (!empty($payload)) {
                        $changed = false;
                        foreach ($payload as $col => $val) {
                            if ((string) ($precinct->{$col} ?? '') !== (string) $val) {
                                $changed = true;
                                break;
                            }
                        }
                        if ($changed) {
                            ElectoralPrecinct::where('id', (int) $precinct->id)->update($payload);
                            $this->precinctUpdated++;
                        }
                    }

                    $districtIndex[$this->normalizeKey($district->name)] = $district;
                    $districtIndex[$districtKey] = $district;
                });
            } catch (\Throwable $e) {
                $this->skipped++;
                $this->errors[] = "Fila {$line}: {$e->getMessage()} {$ctx}";
            }
        }
    }

    public function summary(): array
    {
        return [
            'filas_procesadas' => $this->rowsProcessed,
            'distritos_creados' => $this->districtCreated,
            'distritos_actualizados' => $this->districtUpdated,
            'recintos_actualizados' => $this->precinctUpdated,
            'filas_omitidas' => $this->skipped,
        ];
    }

    private function resolveTarijaMunicipality(): ?Municipality
    {
        return Municipality::query()
            ->whereRaw('LOWER(TRIM(name)) = ?', ['tarija'])
            ->first();
    }

    private function normalizeText($value): string
    {
        return trim((string) ($value ?? ''));
    }

    private function normalizeKey(string $value): string
    {
        $s = Str::ascii($this->normalizeText($value));
        $s = mb_strtolower($s);
        $s = preg_replace('/[^a-z0-9]+/u', ' ', $s) ?? '';
        $s = trim(preg_replace('/\s+/u', ' ', $s) ?? '');
        return $s;
    }

    private function buildPrecinctIndex(int $municipalityId): array
    {
        $items = ElectoralPrecinct::query()
            ->where('municipality_id', $municipalityId)
            ->whereRaw("UPPER(COALESCE(state, '')) = 'ACTIVO'")
            ->get(['id', 'name', 'district_id', 'distric_number']);

        $index = [];
        foreach ($items as $item) {
            $key = $this->normalizeKey((string) $item->name);
            if (!isset($index[$key])) {
                $index[$key] = [];
            }
            $index[$key][] = $item;
        }

        return $index;
    }

    private function precinctExistsInMunicipality(int $municipalityId, string $precinctName): bool
    {
        $target = $this->normalizeKey($precinctName);

        return ElectoralPrecinct::query()
            ->where('municipality_id', $municipalityId)
            ->get(['name'])
            ->contains(function ($p) use ($target) {
                return $this->normalizeKey((string) $p->name) === $target;
            });
    }

    private function buildDistrictIndex(int $municipalityId): array
    {
        $items = District::query()
            ->where('municipality_id', $municipalityId)
            ->get(['id', 'name', 'state']);

        $index = [];
        foreach ($items as $item) {
            $index[$this->normalizeKey((string) $item->name)] = $item;
        }

        return $index;
    }

    private function rowContext($row): string
    {
        $dist = $this->readField($row, ['distrito']);
        $rec = $this->readField($row, ['recinto']);
        return "[distrito={$dist}; recinto={$rec}]";
    }

    private function readField($row, array $aliases): string
    {
        foreach ($row as $k => $v) {
            $key = $this->normalizeKey((string) $k);
            if ($this->hasAnyAlias([$key], $aliases)) {
                return $this->normalizeText($v);
            }
        }
        return '';
    }

    private function hasAnyAlias(array $keys, array $aliases): bool
    {
        foreach ($aliases as $alias) {
            $needle = $this->normalizeKey($alias);
            if (in_array($needle, $keys, true)) {
                return true;
            }
        }
        return false;
    }

    private function extractDistrictNumber(string $districtName): ?int
    {
        if (preg_match('/\d+/', $districtName, $m) === 1) {
            return (int) $m[0];
        }
        return null;
    }
}
