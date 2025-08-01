<?php

namespace App\Http\Controllers;

use App\Models\Miembro;
use App\Models\Province;
use App\Models\Municipality;
use App\Models\ElectoralPrecinct;
use App\Models\Table;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class MiembroController extends Controller
{
     public function index()
    {
       /*  $miembros = Miembro :: all()->sortByDesc('id'); */
        $miembros = Miembro::with(['province','municipality','electoralPrecinct','tables'])
        ->orderBy('created_at', 'desc') // ordenar por fecha de creación, descendente
        ->get();

        return view('admin.delegados.index', compact('miembros'));
        /* return view('admin.delegados.index',['miembros'=>$miembros]);   */     

       
    }

    public function create()
        {
            /* $provinces = Province::where('state', 'activo')->orderBy('name')->get(); */
            $provinces = Province::all();
            return view('admin.delegados.create', compact('provinces'));
        }
    
    public function store(Request $request)
    {
        
        $miembro = new Miembro();
         $miembro->nombres = $request -> nombres;
         $miembro->app = $request -> app;
         $miembro-> apm = $request -> apm;
         $miembro-> genero = $request -> genero;
         $miembro-> ci = $request -> ci;
         $miembro-> fecnac = $request -> fecnac;
         $miembro-> celular = $request -> celular;
         $miembro-> recintovot = $request -> recintovot;
         $miembro-> agrupa = $request -> agrupa;
         $miembro-> obs = $request -> obs;
         $miembro-> estado =$request -> estado;
         $miembro-> delegado =$request -> delegado;
         $miembro-> province_id = $request->province_id;
         $miembro-> municipality_id =$request->municipality_id;
         $miembro-> electoral_precinct_id = $request->electoral_precinct_id;
    
            $miembro -> save();
            return redirect()->route('admin.delegados.index')-> with('mensaje','Se registro correctamente');
     }
  
     
       
    public function show($id)
        {
            $miembro = Miembro::findOrFail($id);
            return view('admin.delegados.show', compact('miembro'));
        }

     
    public function edit($id)
{
    $miembro = Miembro::findOrFail($id);
    $provinces = Province::all();
    $municipalities = Municipality::where('province_id', $miembro->province_id)->get();
    $recintos = ElectoralPrecinct::where('municipality_id', $miembro->municipality_id)->get();
    $tables = Table::where('electoral_precinct_id', $miembro->electoral_precinct_id)->get();

    return view('admin.delegados.edit', compact(
        'miembro',
        'provinces',
        'municipalities',
        'recintos',
        'tables'
    ));

  
    // Carga todas las mesas ordenadas y sin duplicados
    $tables = Table::orderByRaw('CAST(table_number AS UNSIGNED)')->distinct()->get();

    // También carga otras relaciones que necesites, ejemplo provincias, municipios, etc.

    // Retorna la vista con las variables necesarias
    return view('admin.delegados.edit', compact('miembro', 'tables'));

    
}

    public function update(Request $request, $id){

        $miembro = Miembro::findOrFail($id);
        $miembro -> nombres = $request -> nombres;
        $miembro -> app = $request -> app;
        $miembro -> apm = $request -> apm;
        $miembro -> genero = $request -> genero;
        $miembro -> ci = $request -> ci;
        $miembro -> fecnac = $request -> fecnac;
        $miembro -> celular = $request -> celular;
        $miembro -> recintovot = $request -> recintovot;
        $miembro -> agrupa = $request -> agrupa;
        $miembro -> obs = $request -> obs;
        $miembro -> estado =$request -> estado;
        $miembro -> delegado =$request -> delegado;
        $miembro->province_id = $request->province_id;
        $miembro->municipality_id = $request->municipality_id;
        $miembro->electoral_precinct_id = $request->electoral_precinct_id;
        $miembro->mesas()->sync($request->table_ids ?? []);

        $miembro -> save();
         

        return redirect()->route('admin.delegados.index')-> with('mensaje','Se actualizo correctamente');
        }
        public function destroy ($id){
            Miembro::destroy($id);
            return redirect()->route('admin.delegados.index')-> with('mensaje','Se aelimino correctamente');
        }


        public function reporteAgrupacion()
            {
                $agrupaciones = Miembro::select('agrupa', DB::raw('count(*) as total'))
                    ->groupBy('agrupa')
                    ->orderByDesc('total')
                    ->get();

                return view('admin.delegados.reporte-agrupacion', compact('agrupaciones'));
            }
        



}
