<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StagingController extends Controller
{
    private const STAGING_TAG = '[STAGING]';

    public function index()
    {
        $stagingMiembros = DB::table('miembros')
            ->where('obs', 'like', self::STAGING_TAG . '%')
            ->count();

        $stagingAsignaciones = DB::table('assignments as a')
            ->join('miembros as m', 'm.id', '=', 'a.miembro_id')
            ->where('m.obs', 'like', self::STAGING_TAG . '%')
            ->count();

        $stagingMesasVinculadas = DB::table('miembro_table as mt')
            ->join('miembros as m', 'm.id', '=', 'mt.miembro_id')
            ->where('m.obs', 'like', self::STAGING_TAG . '%')
            ->count();

        return view('admin.configuracion.staging', [
            'stagingMiembros' => $stagingMiembros,
            'stagingAsignaciones' => $stagingAsignaciones,
            'stagingMesasVinculadas' => $stagingMesasVinculadas,
        ]);
    }

    public function seed(Request $request)
    {
        $request->validate([
            'cantidad' => ['required', 'integer', 'min:1', 'max:1000'],
        ]);

        $cantidad = (int) $request->cantidad;
        [$inserted, $newMiembroIds] = $this->seedPostulantesInterno($cantidad);

        $jefesCreados = $this->seedJefesInterno($newMiembroIds);
        [$titularesCreados, $suplentesCreados] = $this->seedDelegadosInterno($newMiembroIds);

        return redirect()
            ->route('staging.index')
            ->with('mensaje', "Se generaron {$inserted} datos de prueba. Asignaciones creadas -> Jefes: {$jefesCreados}, Titulares: {$titularesCreados}, Suplentes: {$suplentesCreados}.")
            ->with('icono', 'success');
    }

    public function seedPostulantes(Request $request)
    {
        $request->validate([
            'cantidad' => ['required', 'integer', 'min:1', 'max:1000'],
        ]);

        $cantidad = (int) $request->cantidad;
        [$inserted,] = $this->seedPostulantesInterno($cantidad);

        return redirect()
            ->route('staging.index')
            ->with('mensaje', "Postulantes de prueba creados: {$inserted}.")
            ->with('icono', 'success');
    }

    public function seedJefes()
    {
        $count = $this->seedJefesInterno();

        return redirect()
            ->route('staging.index')
            ->with('mensaje', "Jefes de recinto creados: {$count}.")
            ->with('icono', 'success');
    }

    public function seedDelegados()
    {
        [$titulares, $suplentes] = $this->seedDelegadosInterno();

        return redirect()
            ->route('staging.index')
            ->with('mensaje', "Delegados creados -> Titulares: {$titulares}, Suplentes: {$suplentes}.")
            ->with('icono', 'success');
    }

    public function clear()
    {
        $deleted = 0;

        DB::transaction(function () use (&$deleted) {
            $ids = DB::table('miembros')
                ->where('obs', 'like', self::STAGING_TAG . '%')
                ->pluck('id');

            if ($ids->isEmpty()) {
                return;
            }

            DB::table('assignments')->whereIn('miembro_id', $ids)->delete();
            DB::table('miembro_table')->whereIn('miembro_id', $ids)->delete();
            $deleted = DB::table('miembros')->whereIn('id', $ids)->delete();
        });

        return redirect()
            ->route('staging.index')
            ->with('mensaje', "Limpieza de staging completada. Registros eliminados: {$deleted}.")
            ->with('icono', 'success');
    }

    private function seedPostulantesInterno(int $cantidad): array
    {
        $recintos = $this->recintosActivos();
        if ($recintos->isEmpty()) {
            return [0, []];
        }

        $inserted = 0;
        $newMiembroIds = [];

        DB::transaction(function () use ($cantidad, $recintos, &$inserted, &$newMiembroIds) {
            for ($i = 1; $i <= $cantidad; $i++) {
                $r = $recintos->random();
                $miembroId = $this->crearMiembroStaging($r, 'POSTULANTE', $i);

                $mesa = DB::table('tables')
                    ->where('electoral_precinct_id', $r->electoral_precinct_id)
                    ->inRandomOrder()
                    ->first();

                if ($mesa) {
                    DB::table('miembro_table')->insert([
                        'miembro_id' => $miembroId,
                        'table_id' => $mesa->id,
                    ]);
                }

                $newMiembroIds[] = $miembroId;
                $inserted++;
            }
        });

        return [$inserted, $newMiembroIds];
    }

    private function seedJefesInterno(array $newMiembroIds = []): int
    {
        $jefesCreados = 0;
        $recintos = $this->recintosActivos();
        if ($recintos->isEmpty()) {
            return 0;
        }

        DB::transaction(function () use ($recintos, &$jefesCreados, $newMiembroIds) {
            foreach ($recintos as $r) {
                $precinctId = (int) $r->electoral_precinct_id;
                
                // Tomar primero postulantes nuevos del mismo recinto; si no hay, usar cualquiera del recinto.
                $baseQ = DB::table('miembros')
                    ->where('electoral_precinct_id', $precinctId);
                if (!empty($newMiembroIds)) {
                    $baseQ->whereIn('id', $newMiembroIds);
                }
                $candidatoJefe = $baseQ->inRandomOrder()->first();
                if (!$candidatoJefe) {
                    $candidatoJefe = DB::table('miembros')
                        ->where('electoral_precinct_id', $precinctId)
                        ->inRandomOrder()
                        ->first();
                }
                if (!$candidatoJefe) {
                    $nuevoId = $this->crearMiembroStaging($r, 'JEFE');
                    $candidatoJefe = (object) ['id' => $nuevoId];
                }

                $yaTieneJefe = DB::table('assignments')
                    ->where('scope', 'RECINTO')
                    ->where('electoral_precinct_id', $precinctId)
                    ->where('role', 'JEFE_DE_RECINTO')
                    ->exists();
                if ($yaTieneJefe) {
                    continue;
                }

                DB::table('assignments')->insert([
                    'miembro_id' => (int) $candidatoJefe->id,
                    'scope' => 'RECINTO',
                    'electoral_precinct_id' => $precinctId,
                    'table_id' => null,
                    'table_key' => 0,
                    'role' => 'JEFE_DE_RECINTO',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $jefesCreados++;
            }
        });

        return $jefesCreados;
    }

    private function seedDelegadosInterno(array $newMiembroIds = []): array
    {
        $titularesCreados = 0;
        $suplentesCreados = 0;

        $mesas = DB::table('tables as t')
            ->join('electoral_precincts as e', 'e.id', '=', 't.electoral_precinct_id')
            ->join('municipalities as m', 'm.id', '=', 'e.municipality_id')
            ->where('e.state', 'ACTIVO')
            ->where('m.state', 'ACTIVO')
            ->select('t.id as mesa_id', 'e.id as precinct_id', 'e.name as recinto', 'm.id as municipality_id', 'm.name as municipio')
            ->get();

        DB::transaction(function () use ($mesas, &$titularesCreados, &$suplentesCreados, $newMiembroIds) {
            // uniq_scope_member evita reutilizar el mismo miembro dentro del mismo scope.
            // Para scope=MESA, cada miembro debe quedar en una sola mesa.
            $usedMesaMemberIds = DB::table('assignments')
                ->where('scope', 'MESA')
                ->pluck('miembro_id')
                ->map(fn ($id) => (int) $id)
                ->values();

            foreach ($mesas as $mesa) {
                $mesaId = (int) $mesa->mesa_id;
                $precinctId = (int) $mesa->precinct_id;

                $poolQ = DB::table('miembros')
                    ->where('electoral_precinct_id', $precinctId)
                    ->whereNotIn('id', $usedMesaMemberIds->all());

                if (!empty($newMiembroIds)) {
                    $poolQ->whereIn('id', $newMiembroIds);
                }

                $pool = $poolQ->pluck('id')->map(fn ($id) => (int) $id)->values();

                if ($pool->count() < 2) {
                    $pool = DB::table('miembros')
                        ->where('electoral_precinct_id', $precinctId)
                        ->whereNotIn('id', $usedMesaMemberIds->all())
                        ->pluck('id')
                        ->map(fn ($id) => (int) $id)
                        ->values();
                }

                if ($pool->count() < 2) {
                    $recintoObj = (object) [
                        'electoral_precinct_id' => $precinctId,
                        'recinto' => $mesa->recinto,
                        'municipality_id' => $mesa->municipality_id,
                        'municipio' => $mesa->municipio,
                        'province_id' => null,
                        'provincia' => null,
                    ];
                    while ($pool->count() < 2) {
                        $pool->push((int) $this->crearMiembroStaging($recintoObj, 'DELEGADO'));
                    }
                    $pool = $pool->unique()->values();
                }

                $yaTieneJefe = DB::table('assignments')
                    ->where('scope', 'RECINTO')
                    ->where('electoral_precinct_id', $precinctId)
                    ->where('role', 'JEFE_DE_RECINTO')
                    ->exists();
                if (!$yaTieneJefe) {
                    DB::table('assignments')->insert([
                        'miembro_id' => (int) $pool->random(),
                        'scope' => 'RECINTO',
                        'electoral_precinct_id' => $precinctId,
                        'table_id' => null,
                        'table_key' => 0,
                        'role' => 'JEFE_DE_RECINTO',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                $propExiste = DB::table('assignments')
                    ->where('scope', 'MESA')
                    ->where('table_id', $mesaId)
                    ->where('role', 'DELEGADO_PROPIETARIO')
                    ->exists();

                $suplExiste = DB::table('assignments')
                    ->where('scope', 'MESA')
                    ->where('table_id', $mesaId)
                    ->where('role', 'DELEGADO_SUPLENTE')
                    ->exists();

                $propId = null;
                if (!$propExiste) {
                    $propId = (int) $pool->random();
                    $ins = DB::table('assignments')->insertOrIgnore([
                        'miembro_id' => $propId,
                        'scope' => 'MESA',
                        'electoral_precinct_id' => (int) $precinctId,
                        'table_id' => $mesaId,
                        'table_key' => $mesaId,
                        'role' => 'DELEGADO_PROPIETARIO',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    if ($ins > 0) {
                        $usedMesaMemberIds->push($propId);
                        $titularesCreados++;
                    }
                } else {
                    $propId = (int) DB::table('assignments')
                        ->where('scope', 'MESA')
                        ->where('table_id', $mesaId)
                        ->where('role', 'DELEGADO_PROPIETARIO')
                        ->value('miembro_id');
                }

                if (!$suplExiste) {
                    $poolSupl = $pool
                        ->filter(fn ($id) => (int) $id !== (int) $propId)
                        ->values();

                    if ($poolSupl->isEmpty()) {
                        $recintoObj = (object) [
                            'electoral_precinct_id' => $precinctId,
                            'recinto' => $mesa->recinto,
                            'municipality_id' => $mesa->municipality_id,
                            'municipio' => $mesa->municipio,
                            'province_id' => null,
                            'provincia' => null,
                        ];
                        $nuevoSupl = (int) $this->crearMiembroStaging($recintoObj, 'DELEGADO');
                        $poolSupl = collect([$nuevoSupl]);
                    }

                    $suplId = (int) $poolSupl->random();
                    $ins = DB::table('assignments')->insertOrIgnore([
                        'miembro_id' => $suplId,
                        'scope' => 'MESA',
                        'electoral_precinct_id' => (int) $precinctId,
                        'table_id' => $mesaId,
                        'table_key' => $mesaId,
                        'role' => 'DELEGADO_SUPLENTE',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    if ($ins > 0) {
                        $usedMesaMemberIds->push($suplId);
                        $suplentesCreados++;
                    }
                }
            }
        });

        return [$titularesCreados, $suplentesCreados];
    }

    private function recintosActivos()
    {
        return DB::table('electoral_precincts as e')
            ->join('municipalities as m', 'm.id', '=', 'e.municipality_id')
            ->leftJoin('provinces as p', 'p.id', '=', 'm.province_id')
            ->where('e.state', 'ACTIVO')
            ->where('m.state', 'ACTIVO')
            ->select([
                'e.id as electoral_precinct_id',
                'e.name as recinto',
                'm.id as municipality_id',
                'm.name as municipio',
                'p.id as province_id',
                'p.name as provincia',
            ])
            ->get();
    }

    private function crearMiembroStaging(object $r, string $tipo, ?int $i = null): int
    {
        $sufijo = $i ? '_' . $i : '';
        $seq = now()->format('His') . str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);
        $ci = '98' . $seq;

        return DB::table('miembros')->insertGetId([
            'nombres' => 'PRUEBA_' . $tipo . $sufijo,
            'app' => 'STAGING',
            'apm' => 'AUTO',
            'genero' => (random_int(0, 1) === 1) ? 'MASCULINO' : 'FEMENINO',
            'ci' => $ci,
            'fecnac' => '1990-01-01',
            'celular' => '700' . str_pad((string) random_int(1, 999999), 6, '0', STR_PAD_LEFT),
            'recintovot' => $r->recinto ?? null,
            'mesavot' => null,
            'agrupa' => 'STAGING',
            'obs' => self::STAGING_TAG . ' ' . $tipo,
            'estado' => 'ACTIVO',
            'delegado' => null,
            'province_id' => $r->province_id ?? null,
            'municipality_id' => $r->municipality_id ?? null,
            'electoral_precinct_id' => $r->electoral_precinct_id ?? null,
            'table_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
