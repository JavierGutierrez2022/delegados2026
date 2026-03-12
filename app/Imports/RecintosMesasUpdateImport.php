<?php

namespace App\Imports;

use App\Models\ElectoralPrecinct;
use App\Models\Municipality;
use App\Models\Table;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class RecintosMesasUpdateImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
{
    public bool $dryRun = false;

    public int $rowsProcessed = 0;
    public int $precinctUpdated = 0;
    public int $precinctCreated = 0;
    public int $tablesAdded = 0;
    public int $tablesRemoved = 0;
    public int $precinctInactivated = 0;
    public int $skipped = 0;
    public array $errors = [];

    public function __construct(bool $dryRun = false)
    {
        $this->dryRun = $dryRun;
    }

    public function collection(Collection $rows): void
    {
        $includedPrecinctIds = [];

        foreach ($rows as $index => $row) {
            $line = $index + 2;
            $this->rowsProcessed++;
            $ctx = $this->rowContext($row);

            try {
                $provName = $this->normalizeText($row['provincia'] ?? null);
                $munName = $this->normalizeText($row['municipio'] ?? null);
                $precName = $this->normalizeText($row['recinto'] ?? null);
                $state = strtoupper($this->normalizeText($row['state'] ?? null));

                if ($provName === '' || $munName === '' || $precName === '' || $state === '') {
                    $this->skipped++;
                    $this->errors[] = "Fila {$line}: provincia, municipio, recinto y state son obligatorios. {$ctx}";
                    continue;
                }

                $targetCount = $this->toNullableInt($row['cantidad_mesas'] ?? null);
                if ($targetCount === null || $targetCount < 0) {
                    $this->skipped++;
                    $this->errors[] = "Fila {$line}: cantidad_mesas es obligatoria y debe ser >= 0. {$ctx}";
                    continue;
                }

                $precinct = $this->resolvePrecinct($provName, $munName, $precName);
                if (!$precinct) {
                    $municipality = $this->resolveMunicipality($provName, $munName);
                    if (!$municipality) {
                        $this->skipped++;
                        $this->errors[] = "Fila {$line}: no existe municipio/provincia para crear el recinto. {$ctx}";
                        continue;
                    }

                    if (!$this->dryRun) {
                        $newId = DB::table('electoral_precincts')->insertGetId([
                            'municipality_id' => $municipality->id,
                            'name' => $precName,
                            'electoral_seat' => $municipality->name,
                            'table' => $targetCount,
                            'enabled' => $targetCount,
                            'disabled' => 0,
                            'state' => strtoupper($state),
                            'slug' => (string) Str::uuid(),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                        $precinct = ElectoralPrecinct::find($newId);
                    } else {
                        $precinct = new ElectoralPrecinct([
                            'id' => -1 * $line,
                            'municipality_id' => $municipality->id,
                            'name' => $precName,
                            'state' => $state,
                        ]);
                    }

                    $this->precinctCreated++;
                }
                $includedPrecinctIds[(int) $precinct->id] = true;

                $run = function () use ($precinct, $targetCount, $state, $line) {
                    $dirty = false;
                    if ($precinct->state !== $state) {
                        $precinct->state = $state;
                        $dirty = true;
                    }

                    if ($dirty) {
                        if (!$this->dryRun) {
                            $precinct->save();
                        }
                        $this->precinctUpdated++;
                    }

                    $currentCount = Table::where('electoral_precinct_id', $precinct->id)->count();
                    $delta = $targetCount - $currentCount;

                    if ($delta > 0) {
                        $maxNumber = (int) (Table::where('electoral_precinct_id', $precinct->id)->max('table_number') ?? 0);
                        for ($i = 1; $i <= $delta; $i++) {
                            if (!$this->dryRun) {
                                DB::table('tables')->insert([
                                    'electoral_precinct_id' => $precinct->id,
                                    'table_number' => (string) ($maxNumber + $i),
                                    'code' => '',
                                    'state' => 'ACTIVO',
                                    'slug' => (string) Str::uuid(),
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ]);
                            }
                            $this->tablesAdded++;
                        }
                    } elseif ($delta < 0) {
                        $toRemove = abs($delta);

                        $candidateIds = Table::query()
                            ->where('electoral_precinct_id', $precinct->id)
                            ->orderByRaw('CAST(table_number AS UNSIGNED) DESC')
                            ->limit($toRemove)
                            ->pluck('id');

                        if (!$this->dryRun) {
                            Table::whereIn('id', $candidateIds)->delete();
                        }
                        $this->tablesRemoved += $candidateIds->count();
                    }
                };

                if ($this->dryRun) {
                    $run();
                } else {
                    DB::transaction($run);
                }
            } catch (\Throwable $e) {
                $this->skipped++;
                $this->errors[] = "Fila {$line}: {$e->getMessage()} {$ctx}";
            }
        }

        $includedIds = array_keys($includedPrecinctIds);
        if (empty($includedIds)) {
            $this->errors[] = 'No se encontraron recintos validos en el Excel; no se aplico inactivacion masiva.';
            return;
        }

        $query = ElectoralPrecinct::query()->whereNotIn('id', $includedIds);

        $toInactivate = (clone $query)
            ->whereRaw("UPPER(COALESCE(state, '')) <> ?", ['INACTIVO'])
            ->count();

        if ($toInactivate > 0) {
            if (!$this->dryRun) {
                $query->update([
                    'state' => 'INACTIVO',
                    'updated_at' => now(),
                ]);
            }
            $this->precinctInactivated = $toInactivate;
        }
    }

    public function summary(): array
    {
        return [
            'filas_procesadas' => $this->rowsProcessed,
            'recintos_actualizados' => $this->precinctUpdated,
            'recintos_creados' => $this->precinctCreated,
            'recintos_inactivados' => $this->precinctInactivated,
            'mesas_agregadas' => $this->tablesAdded,
            'mesas_reducidas' => $this->tablesRemoved,
            'filas_omitidas' => $this->skipped,
        ];
    }

    private function resolvePrecinct(string $provName, string $munName, string $precName): ?ElectoralPrecinct
    {
        $matches = ElectoralPrecinct::query()
            ->whereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower($precName)])
            ->whereHas('municipality', function ($q) use ($munName, $provName) {
                $q->whereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower($munName)])
                    ->whereHas('province', function ($q2) use ($provName) {
                        $q2->whereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower($provName)]);
                    });
            })
            ->get();

        if ($matches->count() === 1) {
            return $matches->first();
        }

        if ($matches->count() > 1) {
            throw new \RuntimeException('Existe mas de un recinto con los mismos datos en provincia/municipio/recinto.');
        }

        return null;
    }

    private function resolveMunicipality(string $provName, string $munName): ?Municipality
    {
        return Municipality::query()
            ->whereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower($munName)])
            ->whereHas('province', function ($q) use ($provName) {
                $q->whereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower($provName)]);
            })
            ->first();
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

    private function normalizeText($value): string
    {
        return trim((string) ($value ?? ''));
    }

    private function rowContext($row): string
    {
        $prov = $this->normalizeText($row['provincia'] ?? null);
        $mun = $this->normalizeText($row['municipio'] ?? null);
        $rec = $this->normalizeText($row['recinto'] ?? null);
        $mes = $this->normalizeText($row['cantidad_mesas'] ?? null);
        $sta = $this->normalizeText($row['state'] ?? null);

        return "[provincia={$prov}; municipio={$mun}; recinto={$rec}; cantidad_mesas={$mes}; state={$sta}]";
    }
}

