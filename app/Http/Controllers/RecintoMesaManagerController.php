<?php

namespace App\Http\Controllers;

use App\Models\ElectoralPrecinct;
use App\Models\Table;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class RecintoMesaManagerController extends Controller
{
    public function index(Request $request)
    {
        $provincias = DB::table('provinces')->orderBy('name')->get();

        $municipios = collect();
        $recintosFiltro = collect();

        if ($request->filled('province_id')) {
            $municipios = DB::table('municipalities')
                ->where('province_id', $request->integer('province_id'))
                ->orderBy('name')
                ->get();
        }

        if ($request->filled('municipality_id')) {
            $recintosFiltro = DB::table('electoral_precincts')
                ->where('municipality_id', $request->integer('municipality_id'))
                ->orderBy('name')
                ->get(['id', 'name']);
        }

        $recintos = DB::table('electoral_precincts as ep')
            ->leftJoin('municipalities as mu', 'mu.id', '=', 'ep.municipality_id')
            ->leftJoin('provinces as p', 'p.id', '=', 'mu.province_id')
            ->leftJoin('districts as d', 'd.id', '=', 'ep.district_id')
            ->leftJoin('tables as t', function ($join) {
                $join->on('t.electoral_precinct_id', '=', 'ep.id')
                    ->where('t.state', '=', 'ACTIVO');
            })
            ->selectRaw('
                ep.id,
                ep.name,
                ep.state,
                ep.electoral_seat,
                ep.distric_number,
                mu.id as municipality_id,
                mu.name as municipality_name,
                p.id as province_id,
                p.name as province_name,
                d.name as district_name,
                COUNT(t.id) as current_tables
            ')
            ->when($request->filled('province_id'), fn ($q) => $q->where('p.id', $request->integer('province_id')))
            ->when($request->filled('municipality_id'), fn ($q) => $q->where('mu.id', $request->integer('municipality_id')))
            ->when($request->filled('precinct_id'), fn ($q) => $q->where('ep.id', $request->integer('precinct_id')))
            ->when($request->filled('estado'), fn ($q) => $q->where('ep.state', $request->input('estado')))
            ->when($request->filled('buscar'), function ($q) use ($request) {
                $term = trim((string) $request->input('buscar'));
                $q->where(function ($sub) use ($term) {
                    $sub->where('ep.name', 'like', "%{$term}%")
                        ->orWhere('ep.electoral_seat', 'like', "%{$term}%")
                        ->orWhere('mu.name', 'like', "%{$term}%")
                        ->orWhere('p.name', 'like', "%{$term}%");
                });
            })
            ->groupBy(
                'ep.id',
                'ep.name',
                'ep.state',
                'ep.electoral_seat',
                'ep.distric_number',
                'mu.id',
                'mu.name',
                'p.id',
                'p.name',
                'd.name'
            )
            ->orderBy('p.name')
            ->orderBy('mu.name')
            ->orderBy('ep.name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.recintos.manage_tables', compact('provincias', 'municipios', 'recintosFiltro', 'recintos'));
    }

    public function update(Request $request, ElectoralPrecinct $precinct): RedirectResponse
    {
        $data = $request->validate([
            'mesa_total' => ['nullable', 'integer', 'min:0'],
            'table_ids' => ['nullable', 'array'],
            'table_ids.*' => ['nullable', 'integer'],
            'table_numbers' => ['nullable', 'array'],
            'table_numbers.*' => ['nullable', 'integer', 'min:1'],
        ]);

        try {
            $result = DB::transaction(function () use ($precinct, $data) {
                $tableNumbers = array_values(array_filter($data['table_numbers'] ?? [], fn ($value) => $value !== null && $value !== ''));

                if (!empty($tableNumbers)) {
                    return $this->syncTableLayout(
                        $precinct,
                        $data['table_ids'] ?? [],
                        $data['table_numbers'] ?? []
                    );
                }

                if (!array_key_exists('mesa_total', $data) || $data['mesa_total'] === null) {
                    throw ValidationException::withMessages([
                        'mesa_total' => 'Debes indicar un total de mesas o editar el detalle de mesas.',
                    ]);
                }

                return $this->syncTableCount($precinct, (int) $data['mesa_total']);
            });
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->with('mensaje', 'No se pudo actualizar la configuracion de mesas.')
                ->with('icono', 'error')
                ->withErrors(['mesa_total' => $e->getMessage()]);
        }

        $mensaje = "Recinto {$precinct->name}: {$result['before']} -> {$result['after']} mesas.";
        if (!empty($result['removed'])) {
            $mensaje .= ' Mesas retiradas: '.implode(', ', $result['removed']).'.';
        }
        if (!empty($result['added'])) {
            $mensaje .= ' Mesas creadas: '.implode(', ', $result['added']).'.';
        }
        if (!empty($result['renamed'])) {
            $mensaje .= ' Renumeradas: '.implode(', ', $result['renamed']).'.';
        }

        return back()->with([
            'mensaje' => $mensaje,
            'icono' => 'success',
        ]);
    }

    private function syncTableLayout(ElectoralPrecinct $precinct, array $tableIds, array $tableNumbers): array
    {
        $currentTables = Table::query()
            ->where('electoral_precinct_id', $precinct->id)
            ->where('state', 'ACTIVO')
            ->orderByRaw('CAST(table_number AS UNSIGNED) ASC')
            ->get(['id', 'table_number'])
            ->keyBy('id');

        $desiredEntries = [];
        $usedIds = [];
        $usedNumbers = [];

        $rows = max(count($tableIds), count($tableNumbers));
        for ($i = 0; $i < $rows; $i++) {
            $rawNumber = $tableNumbers[$i] ?? null;
            if ($rawNumber === null || $rawNumber === '') {
                continue;
            }

            $number = (int) $rawNumber;
            if ($number < 1) {
                throw ValidationException::withMessages([
                    'table_numbers' => 'Todos los numeros de mesa deben ser mayores a 0.',
                ]);
            }

            $rawId = $tableIds[$i] ?? null;
            $id = ($rawId === null || $rawId === '') ? null : (int) $rawId;

            if ($id !== null) {
                if (!$currentTables->has($id)) {
                    throw ValidationException::withMessages([
                        'table_numbers' => 'Se detecto una mesa invalida para este recinto.',
                    ]);
                }
                if (isset($usedIds[$id])) {
                    throw ValidationException::withMessages([
                        'table_numbers' => 'No puedes repetir la misma mesa en la edicion.',
                    ]);
                }
                $usedIds[$id] = true;
            }

            if (isset($usedNumbers[$number])) {
                throw ValidationException::withMessages([
                    'table_numbers' => 'No puedes repetir el numero de mesa '.$number.'.',
                ]);
            }
            $usedNumbers[$number] = true;

            $desiredEntries[] = [
                'id' => $id,
                'number' => $number,
            ];
        }

        if (empty($desiredEntries)) {
            throw ValidationException::withMessages([
                'table_numbers' => 'Debes dejar al menos una mesa configurada.',
            ]);
        }

        $before = $currentTables->count();
        $removed = [];
        $added = [];
        $renamed = [];

        $desiredIds = collect($desiredEntries)
            ->pluck('id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->all();

        $toDelete = $currentTables
            ->filter(fn ($table) => !in_array((int) $table->id, $desiredIds, true))
            ->values();

        foreach ($toDelete as $table) {
            if (!$this->canDeleteTable((int) $table->id)) {
                throw ValidationException::withMessages([
                    'table_numbers' => 'No se puede quitar la mesa '.$table->table_number.' porque tiene asignaciones o vinculos.',
                ]);
            }
            $removed[] = $table->table_number;
        }

        $tempBase = max(
            (int) ($currentTables->max(fn ($table) => (int) $table->table_number) ?? 0),
            (int) (collect($desiredEntries)->max('number') ?? 0)
        ) + 1000;

        foreach ($desiredEntries as $index => $entry) {
            if ($entry['id'] !== null) {
                DB::table('tables')
                    ->where('id', $entry['id'])
                    ->update([
                        'table_number' => $tempBase + $index,
                        'updated_at' => now(),
                    ]);
            }
        }

        if (!empty($removed)) {
            $this->deactivateTables($toDelete->pluck('id')->all());
        }

        foreach ($desiredEntries as $entry) {
            if ($entry['id'] !== null) {
                $currentNumber = (string) $currentTables[$entry['id']]->table_number;

                DB::table('tables')
                    ->where('id', $entry['id'])
                    ->update([
                        'table_number' => (string) $entry['number'],
                        'updated_at' => now(),
                    ]);

                if ($currentNumber !== (string) $entry['number']) {
                    $renamed[] = $currentNumber.'->'.$entry['number'];
                }

                continue;
            }

            DB::table('tables')->insert([
                'electoral_precinct_id' => $precinct->id,
                'table_number' => (string) $entry['number'],
                'code' => '',
                'state' => 'ACTIVO',
                'slug' => (string) Str::uuid(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $added[] = (string) $entry['number'];
        }

        $after = Table::where('electoral_precinct_id', $precinct->id)->where('state', 'ACTIVO')->count();
        $this->syncPrecinctSummaryFields($precinct->id, $after);

        return [
            'before' => $before,
            'after' => $after,
            'added' => $added,
            'removed' => $removed,
            'renamed' => $renamed,
        ];
    }

    private function syncTableCount(ElectoralPrecinct $precinct, int $targetCount): array
    {
        $currentCount = Table::where('electoral_precinct_id', $precinct->id)->where('state', 'ACTIVO')->count();

        if ($currentCount === $targetCount) {
            $this->syncPrecinctSummaryFields($precinct->id, $currentCount);

            return [
                'before' => $currentCount,
                'after' => $currentCount,
                'added' => [],
                'removed' => [],
                'renamed' => [],
            ];
        }

        $added = [];
        $removed = [];

        if ($targetCount > $currentCount) {
            $delta = $targetCount - $currentCount;
            $maxNumber = (int) (Table::where('electoral_precinct_id', $precinct->id)->max(DB::raw('CAST(table_number AS UNSIGNED)')) ?? 0);

            for ($i = 1; $i <= $delta; $i++) {
                $tableNumber = (string) ($maxNumber + $i);

                DB::table('tables')->insert([
                    'electoral_precinct_id' => $precinct->id,
                    'table_number' => $tableNumber,
                    'code' => '',
                    'state' => 'ACTIVO',
                    'slug' => (string) Str::uuid(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $added[] = $tableNumber;
            }
        } else {
            $toRemove = $currentCount - $targetCount;
            $tables = Table::query()
                ->where('electoral_precinct_id', $precinct->id)
                ->where('state', 'ACTIVO')
                ->orderByRaw('CAST(table_number AS UNSIGNED) DESC')
                ->get(['id', 'table_number']);

            $blocked = [];
            $removableIds = [];

            foreach ($tables as $table) {
                if (count($removableIds) >= $toRemove) {
                    break;
                }

                if (!$this->canDeleteTable((int) $table->id)) {
                    $blocked[] = $table->table_number;
                    continue;
                }

                $removableIds[] = $table->id;
                $removed[] = $table->table_number;
            }

            if (count($removableIds) < $toRemove) {
                $message = 'No se pudo reducir a '.$targetCount.' mesas.';
                if (!empty($blocked)) {
                    $message .= ' Mesas protegidas por uso actual: '.implode(', ', $blocked).'.';
                }
                $message .= ' Libera primero sus asignaciones o vinculos.';

                throw ValidationException::withMessages([
                    'mesa_total' => $message,
                ]);
            }

            $this->deactivateTables($removableIds);
        }

        $finalCount = Table::where('electoral_precinct_id', $precinct->id)->where('state', 'ACTIVO')->count();
        $this->syncPrecinctSummaryFields($precinct->id, $finalCount);

        return [
            'before' => $currentCount,
            'after' => $finalCount,
            'added' => $added,
            'removed' => $removed,
            'renamed' => [],
        ];
    }

    private function canDeleteTable(int $tableId): bool
    {
        return true;
    }

    private function deactivateTables(array $tableIds): void
    {
        if (empty($tableIds)) {
            return;
        }

        DB::table('assignments')
            ->whereIn('table_id', $tableIds)
            ->delete();

        DB::table('miembro_table')
            ->whereIn('table_id', $tableIds)
            ->delete();

        DB::table('miembros')
            ->whereIn('table_id', $tableIds)
            ->update([
                'table_id' => null,
                'updated_at' => now(),
            ]);

        DB::table('tables')
            ->whereIn('id', $tableIds)
            ->update([
                'state' => 'INACTIVO',
                'updated_at' => now(),
            ]);
    }

    private function syncPrecinctSummaryFields(int $precinctId, int $count): void
    {
        $payload = [
            'table' => $count,
            'updated_at' => now(),
        ];

        if (Schema::hasColumn('electoral_precincts', 'enabled')) {
            $payload['enabled'] = $count;
        }

        if (Schema::hasColumn('electoral_precincts', 'disabled')) {
            $payload['disabled'] = 0;
        }

        DB::table('electoral_precincts')
            ->where('id', $precinctId)
            ->update($payload);
    }
}
