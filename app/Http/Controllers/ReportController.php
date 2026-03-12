<?php
namespace App\Http\Controllers;

use App\Models\ElectoralPrecinct;
use App\Models\User;
use App\Models\Miembro;
use App\Models\Municipality;
use App\Models\Province;
use App\Models\MiembroTable;
use App\Models\Table;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Exports\PostulacionesExport;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
    {

        public function mesasPorMunicipioYRecinto()
                {
                    $reportes = DB::table('municipalities as m')
                        ->join('electoral_precincts as ep', 'ep.municipality_id', '=', 'm.id')
                        ->join('tables as t', 't.electoral_precinct_id', '=', 'ep.id')
                        ->where('ep.state', 'ACTIVO')
                        ->select(
                            'm.name as municipality_name',
                            'ep.name as precinct_name',
                            DB::raw('COUNT(t.id) as total_tables')
                        )
                        ->groupBy('m.name', 'ep.name')
                        ->orderBy('m.name')
                        ->orderBy('ep.name')
                        ->get();
                
                    return view('reportes.mesas_por_municipio', compact('reportes'));
            }
            
            public function reporteDetalleMesas(Request $request)
                {
                    $municipio_id = $request->input('municipio_id');
                
                    $query = DB::table('miembros')
                        ->join('provinces', 'miembros.province_id', '=', 'provinces.id')
                        ->join('municipalities', 'miembros.municipality_id', '=', 'municipalities.id')
                        ->join('electoral_precincts', 'miembros.electoral_precinct_id', '=', 'electoral_precincts.id')
                        ->join('miembro_table', 'miembros.id', '=', 'miembro_table.miembro_id')
                        ->join('tables', 'miembro_table.table_id', '=', 'tables.id')
                        ->select(
                                    'provinces.name as provincia',
                                    'municipalities.name as municipio',
                                    'electoral_precincts.name as recinto',
                                    'tables.table_number as numero_mesa',
                                    'miembros.agrupa as agrupacion',
                                    DB::raw('COUNT(miembros.id) as cantidad_miembros'),
                                    DB::raw('GROUP_CONCAT(CONCAT(miembros.nombres, " ", miembros.app, " ", miembros.apm) SEPARATOR ", ") as miembros_nombres')
                                )
                                ->groupBy(
                                    'provinces.name',
                                    'municipalities.name',
                                    'electoral_precincts.name',
                                    'tables.table_number',
                                    'miembros.agrupa'
                                )
                        ->orderBy('provincia');
                
                    if ($municipio_id) {
                        $query->where('miembros.municipality_id', $municipio_id);
                    }
                
                    $datos = $query->get();
                    $municipios = Municipality::orderBy('name')->get();
                
                    return view('reportes.detalle_mesas', compact('datos', 'municipios', 'municipio_id'));
                }
                
                public function reporteDelegadosJefes(Request $request)
                    {
                        $municipio_id = $request->get('municipio_id');
                    
                            $datos = DB::table('miembros')
                                ->join('municipalities', 'miembros.municipality_id', '=', 'municipalities.id')
                                ->join('provinces', 'municipalities.province_id', '=', 'provinces.id')
                                ->join('electoral_precincts', 'miembros.electoral_precinct_id', '=', 'electoral_precincts.id')
                                ->select(
                                    'provinces.name as provincia',
                                    'municipalities.name as municipio',
                                    'electoral_precincts.name as recinto',
                                    'miembros.delegado'
                                )
                                ->whereIn('miembros.delegado', ['JEFE DE RECINTO', 'DELEGADO DE MESA'])
                                ->get();
                            
                            dd($datos);
                    
                        return view('reportes.delegados_jefes', compact('datos', 'municipios', 'municipio_id'));
                    }
                    
                    public function delegadosJefesRecinto(Request $request)
                        {
                            $municipioId = $request->input('municipio_id');
                        
                            $query = DB::table('provinces as p')
                                ->join('municipalities as m', 'm.province_id', '=', 'p.id')
                                ->join('electoral_precincts as ep', 'ep.municipality_id', '=', 'm.id')
                                ->leftJoin('miembros as mi', 'mi.electoral_precinct_id', '=', 'ep.id')
                                ->leftJoin('tables as t', 't.electoral_precinct_id', '=', 'ep.id')
                                ->select(
                                    'p.name as provincia',
                                    'm.name as municipio',
                                    'ep.name as recinto',
                                    DB::raw("SUM(CASE WHEN mi.delegado = 'DELEGADO MESA' THEN 1 ELSE 0 END) as cantidad_delegados_mesa"),
                                    DB::raw("SUM(CASE WHEN mi.delegado = 'JEFE RECINTO' THEN 1 ELSE 0 END) as cantidad_jefes_recinto"),
                                    DB::raw("COUNT(DISTINCT t.id) as total_mesas")
                                )
                                ->when($municipioId, function($q) use ($municipioId) {
                                    $q->where('m.id', $municipioId);
                                })
                                ->groupBy('p.name', 'm.name', 'ep.name')
                                ->orderBy('p.name')
                                ->orderBy('m.name')
                                ->orderBy('ep.name');
                        
                            $datos = $query->get();
                        
                            $municipios = Municipality::orderBy('name')->get();
                        
                            return view('reportes.delegados_jefes', compact('datos', 'municipios', 'municipioId'));
                        }
                        
                        public function mesasPorProvincia(Request $request)
                            {
                                $provinciaSeleccionada = $request->input('province_id');
                            
                                $query = \DB::table('provinces as p')
                                    ->join('municipalities as m', 'm.province_id', '=', 'p.id')
                                    ->join('electoral_precincts as ep', 'ep.municipality_id', '=', 'm.id')
                                    ->join('tables as t', 't.electoral_precinct_id', '=', 'ep.id')
                                    ->leftJoin('miembro_table as mt', 'mt.table_id', '=', 't.id')
                                    ->leftJoin('miembros as mi', 'mi.id', '=', 'mt.miembro_id')
                                    ->select(
                                        'p.name as provincia',
                                        'm.name as municipio',
                                        'ep.name as recinto',
                                        't.table_number as nro_mesa',
                                        'mi.agrupa as agrupacion',
                                        \DB::raw("CONCAT(mi.nombres, ' ', mi.app, ' ', mi.apm) as nombre_miembro")
                                    )
                                    ->orderBy('p.name')
                                    ->orderBy('m.name')
                                    ->orderBy('ep.name')
                                    ->orderBy('t.table_number');
                            
                                if ($provinciaSeleccionada) {
                                    $query->where('p.id', $provinciaSeleccionada);
                                }
                            
                                $datos = $query->get();
                            
                                $provincias = \DB::table('provinces')->pluck('name', 'id');
                            
                                return view('reportes.mesas_por_provincia', compact('datos', 'provincias', 'provinciaSeleccionada'));
                            }
                            
                            
                            // Vista principal
                                   // public function totalDelegadosRecinto()
                                   // {
                                    //    $municipios = Municipality::orderBy('name')->get();
                                    //    return view('reportes.total_delegados_recinto', compact('municipios'));
                                   // }
                                
                                    // Datos para DataTables o carga inicial
                                    public function vistaTotalDelegadosRecinto(Request $request)
                                        {
                                            $municipios = Municipality::orderBy('name')->get();
                                            $municipio_id = $request->input('municipio_id');
                                        
                                            $query = DB::table('provinces as p')
                                                ->join('municipalities as m', 'm.province_id', '=', 'p.id')
                                                ->join('electoral_precincts as ep', 'ep.municipality_id', '=', 'm.id')
                                                ->leftJoin('tables as t', 't.electoral_precinct_id', '=', 'ep.id')
                                                ->leftJoin(DB::raw("(
                                                    SELECT DISTINCT mi.id, mi.nombres, mi.app, mi.apm, mi.delegado,
                                                    COALESCE(mi.electoral_precinct_id, t.electoral_precinct_id) AS ep_id
                                                    FROM miembros mi
                                                    LEFT JOIN miembro_table mt ON mt.miembro_id = mi.id
                                                    LEFT JOIN tables t ON t.id = mt.table_id
                                                ) as mip"), 'mip.ep_id', '=', 'ep.id')
                                                ->leftJoin(DB::raw("(
                                                    SELECT ep.id AS recinto_id,
                                                    GROUP_CONCAT(CONCAT('Mesa ', t.table_number, ': ', miembros.mis_miembros)
                                                        ORDER BY t.table_number SEPARATOR ' | ') AS mesas_con_miembros
                                                    FROM electoral_precincts ep
                                                    LEFT JOIN tables t ON t.electoral_precinct_id = ep.id
                                                    LEFT JOIN (
                                                        SELECT mt.table_id,
                                                        GROUP_CONCAT(CONCAT(mi.nombres, ' ', mi.app, ' ', mi.apm)
                                                            ORDER BY mi.nombres SEPARATOR ', ') AS mis_miembros
                                                        FROM miembro_table mt
                                                        LEFT JOIN miembros mi ON mi.id = mt.miembro_id
                                                        GROUP BY mt.table_id
                                                    ) AS miembros ON miembros.table_id = t.id
                                                    GROUP BY ep.id
                                                ) as mesas"), 'mesas.recinto_id', '=', 'ep.id')
                                                ->select(
                                                    'p.name as provincia',
                                                    'm.name as municipio',
                                                    'ep.name as recinto',
                                                    DB::raw('COUNT(DISTINCT t.id) AS total_mesas'),
                                                    DB::raw("COUNT(DISTINCT CASE WHEN mip.delegado = 'DELEGADO MESA' THEN mip.id END) AS cantidad_delegados_mesa"),
                                                    DB::raw("COUNT(DISTINCT CASE WHEN mip.delegado = 'JEFE RECINTO' THEN mip.id END) AS cantidad_jefes_recinto"),
                                                    DB::raw("GROUP_CONCAT(DISTINCT CONCAT(mip.nombres, ' ', mip.app, ' ', mip.apm)
                                                        ORDER BY mip.nombres SEPARATOR ', ') AS miembros"),
                                                    'mesas.mesas_con_miembros'
                                                )
                                                ->groupBy('p.id', 'p.name', 'm.id', 'm.name', 'ep.id', 'ep.name', 'mesas.mesas_con_miembros')
                                                ->orderBy('p.name')
                                                ->orderBy('m.name')
                                                ->orderBy('ep.name');
                                        
                                            if ($municipio_id) {
                                                $query->where('m.id', $municipio_id);
                                            }
                                        
                                            $datos = $query->get();
                                            
                                            $provincias = DB::table('provinces')->orderBy('name')->get();
                                            return view('reportes.total_delegados_recinto', compact('municipios', 'provincias', 'datos', 'municipio_id'));

                                        }                                  
                                
    }   
                                 