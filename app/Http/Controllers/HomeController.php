<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index()
    {
        // === contadores que ya usas en tus tarjetas ===
        $usuarios           = DB::table('users')->get();
        $miembros           = DB::table('miembros')->get();
        $provinces          = DB::table('provinces')->get();
        $municipalities     = DB::table('municipalities')->where('state', 'ACTIVO')->get();
        $electoralprecincts = DB::table('electoral_precincts')->where('state', 'ACTIVO')->get();
        $tables             = DB::table('tables as t')
            ->join('electoral_precincts as e', 'e.id', '=', 't.electoral_precinct_id')
            ->join('municipalities as mu', 'mu.id', '=', 'e.municipality_id')
            ->where('t.state', 'ACTIVO')
            ->where('e.state', 'ACTIVO')
            ->where('mu.state', 'ACTIVO')
            ->select('t.*')
            ->get();

        // === MÉTRICAS PARA LOS DONUTS ===

        // Mesas cubiertas: mesa activa que tiene propietario y su recinto tiene jefe
        $totalMesas = DB::table('tables as t')
            ->join('electoral_precincts as e', 'e.id', '=', 't.electoral_precinct_id')
            ->join('municipalities as mu', 'mu.id', '=', 'e.municipality_id')
            ->where('t.state', 'ACTIVO')
            ->where('e.state', 'ACTIVO')
            ->where('mu.state', 'ACTIVO')
            ->count();

        $mesasCubiertas = DB::table('assignments as a')
            ->join('tables as t', 't.id', '=', 'a.table_id')
            ->where('a.scope', 'MESA')
            ->whereNotNull('a.table_id')
            ->where('t.state', 'ACTIVO')
            ->select('a.table_id')
            ->groupBy('a.table_id')
            ->havingRaw("SUM(a.role = 'DELEGADO_PROPIETARIO') >= 1")
            ->whereExists(function ($q) {
                $q->selectRaw('1')
                    ->from('assignments as ar')
                    ->whereColumn('ar.electoral_precinct_id', 'a.electoral_precinct_id')
                    ->where('ar.scope', 'RECINTO')
                    ->where('ar.role', 'JEFE_DE_RECINTO');
            })
            ->count();

        // Recintos cubiertos: recinto con JEFE_DE_RECINTO
        $totalRecintos = DB::table('electoral_precincts')->where('state', 'ACTIVO')->count();

        $recintosCubiertos = DB::table('assignments')
            ->join('miembros as mm', 'mm.id', '=', 'assignments.miembro_id')
            ->where('scope', 'RECINTO')
            ->where('role', 'JEFE_DE_RECINTO')
            ->distinct('electoral_precinct_id')
            ->count('electoral_precinct_id');

        $mesasPct = $totalMesas > 0 ? (($mesasCubiertas / $totalMesas) * 100) : 0;
        if ($mesasCubiertas < $totalMesas) {
            $mesasPct = min(99.99, $mesasPct);
        }
        $mesasPct = round($mesasPct, 2);

        $recintosPct = $totalRecintos > 0 ? (($recintosCubiertos / $totalRecintos) * 100) : 0;
        if ($recintosCubiertos < $totalRecintos) {
            $recintosPct = min(99.99, $recintosPct);
        }
        $recintosPct = round($recintosPct, 2);

        $metrics = [
            'mesas' => [
                'total'     => $totalMesas,
                'cubiertas' => $mesasCubiertas,
                'pct'       => $mesasPct,
            ],
            'recintos' => [
                'total'     => $totalRecintos,
                'cubiertos' => $recintosCubiertos,
                'pct'       => $recintosPct,
            ],
        ];

        return view('admin.index', compact(
            'usuarios','miembros','provinces','municipalities',
            'electoralprecincts','tables','metrics'
        ));
    }
}
