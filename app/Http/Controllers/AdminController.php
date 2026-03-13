<?php

namespace App\Http\Controllers;

use App\Models\ElectoralPrecinct;
use App\Models\User;
use App\Models\Miembro;
use App\Models\Municipality;
use App\Models\Province;
use App\Models\Table;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function index(){
        $usuarios = User::all();
        $miembros = Miembro::all();
        $provinces = Province::all();
        $municipalities = Municipality::where('state', 'ACTIVO')->get();
        $electoralprecincts = ElectoralPrecinct::where('state', 'ACTIVO')->get();
        $tables = Table::query()
            ->join('electoral_precincts', 'electoral_precincts.id', '=', 'tables.electoral_precinct_id')
            ->join('municipalities', 'municipalities.id', '=', 'electoral_precincts.municipality_id')
            ->where('tables.state', 'ACTIVO')
            ->where('electoral_precincts.state', 'ACTIVO')
            ->where('municipalities.state', 'ACTIVO')
            ->select('tables.*')
            ->get();

        // ===== métricas de cobertura para donuts =====
        $activeTableIds = DB::table('tables as t')
            ->join('electoral_precincts as e', 'e.id', '=', 't.electoral_precinct_id')
            ->join('municipalities as m', 'm.id', '=', 'e.municipality_id')
            ->where('t.state', 'ACTIVO')
            ->where('e.state', 'ACTIVO')
            ->where('m.state', 'ACTIVO')
            ->pluck('t.id');

        $totalMesas = $activeTableIds->count();

        $mesasCubiertas = 0;
        if ($totalMesas > 0) {
            $mesasCubiertas = DB::table('assignments as a')
                ->where('a.scope', 'MESA')
                ->whereIn('a.table_id', $activeTableIds)
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
        }

        $totalRecintos = ElectoralPrecinct::query()
            ->join('municipalities', 'municipalities.id', '=', 'electoral_precincts.municipality_id')
            ->where('electoral_precincts.state', 'ACTIVO')
            ->where('municipalities.state', 'ACTIVO')
            ->count();

        $recintosCubiertos = DB::table('assignments as a')
            ->join('miembros as mm', 'mm.id', '=', 'a.miembro_id')
            ->join('electoral_precincts as e', 'e.id', '=', 'a.electoral_precinct_id')
            ->join('municipalities as m', 'm.id', '=', 'e.municipality_id')
            ->where('a.scope', 'RECINTO')
            ->where('a.role', 'JEFE_DE_RECINTO')
            ->where('e.state', 'ACTIVO')
            ->where('m.state', 'ACTIVO')
            ->distinct('a.electoral_precinct_id')
            ->count('a.electoral_precinct_id');

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

        return view('admin.index', [
            'usuarios' => $usuarios,
            'miembros' => $miembros,
            'provinces' => $provinces,
            'municipalities' => $municipalities,
            'electoralprecincts' => $electoralprecincts,
            'tables' => $tables,
            'metrics' => $metrics,
        ]);
    }

}
