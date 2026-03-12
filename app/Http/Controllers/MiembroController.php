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
use Illuminate\Validation\Rule;
use Illuminate\Database\QueryException;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\DelegadosImport;
use App\Exports\DelegadosTemplateExport;

class MiembroController extends Controller
{
     public function index()
    {
       /*  $miembros = Miembro :: all()->sortByDesc('id'); */
        $miembros = Miembro::with(['province','municipality','electoralPrecinct','tables'])
        ->orderBy('created_at', 'desc') // ordenar por fecha de creación, descendente
        ->get();

        return view('admin.delegados.index', compact('miembros'));
         

       
    }

    public function create()
        {
            
            $provinces = Province::all();
            return view('admin.delegados.create', compact('provinces'));
        }
    
                public function store(Request $request)
            {
                // 1) Validación
                $request->validate([
                    'ci'                  => ['required','string','max:20','unique:miembros,ci'],
                    'nombres'             => ['required','string','max:100'],
                    'app'                 => ['nullable','string','max:100'],
                    'apm'                 => ['nullable','string','max:100'],
                    'genero'              => ['required','in:MASCULINO,FEMENINO'],
                    // si tu input es <input type="date">:
                    'fecnac'              => ['nullable','date'],
                    // si envías dd/mm/YYYY, usa: 'fecnac' => ['nullable','date_format:d/m/Y'],
                    'celular'             => ['nullable','string','max:20'],
                    'correo_electronico'  => ['nullable','email','max:150'],
                    'obs'                 => ['nullable','string','max:255'],
                    'province_id'         => ['nullable','integer','exists:provinces,id'],
                    'municipality_id'     => ['nullable','integer','exists:municipalities,id'],
                    'electoral_precinct_id' => ['nullable','integer','exists:electoral_precincts,id'],
                ],[
                    'ci.unique' => 'El C.I. ingresado ya existe en el sistema.',
                ]);

                $miembro = new Miembro();
                $miembro->ci      = mb_strtoupper(trim((string) $request->ci));
                $miembro->nombres = mb_strtoupper(trim((string) $request->nombres));
                $miembro->app     = $request->filled('app') ? mb_strtoupper(trim((string) $request->app)) : null;
                $miembro->apm     = $request->filled('apm') ? mb_strtoupper(trim((string) $request->apm)) : null;
                $miembro->genero  = $request->genero;

                // Si usas dd/mm/YYYY descomenta esta línea:
                // $miembro->fecnac = $request->filled('fecnac') ? Carbon::createFromFormat('d/m/Y',$request->fecnac)->format('Y-m-d') : null;
                // Si usas <input type="date"> (YYYY-MM-DD):
                $miembro->fecnac  = $request->fecnac;

                $miembro->celular = $request->celular;
                $miembro->correo_electronico = $request->filled('correo_electronico')
                    ? mb_strtolower(trim((string) $request->correo_electronico))
                    : null;
                $miembro->obs     = $request->filled('obs') ? mb_strtoupper(trim((string) $request->obs)) : null;
                $miembro->province_id         = $request->province_id;
                $miembro->municipality_id     = $request->municipality_id;
                $miembro->electoral_precinct_id = $request->electoral_precinct_id;

                try {
                    $miembro->save();
                } catch (QueryException $e) {
                    // “cinturón de seguridad” por si se cuela un duplicado concurrente
                    if (($e->errorInfo[1] ?? null) === 1062) {
                        return back()->withInput()
                            ->with('mensaje','El C.I. ingresado ya existe.')
                            ->with('icono','error');
                    }
                    throw $e;
                }

                return redirect()->route('admin.delegados.index')
                    ->with('mensaje','Se registró correctamente')
                    ->with('icono','success');
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
    $municipalities = Municipality::where('province_id', $miembro->province_id)->where('state', 'ACTIVO')->get();
    $recintos = ElectoralPrecinct::where('municipality_id', $miembro->municipality_id)->where('state', 'ACTIVO')->get();
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

                public function update(Request $request, $id)
            {
                $miembro = Miembro::findOrFail($id);

                // 1) Validación (ignora el propio registro en unique)
                $request->validate([
                    'ci'                  => ['required','string','max:20', Rule::unique('miembros','ci')->ignore($miembro->id)],
                    'nombres'             => ['required','string','max:100'],
                    'app'                 => ['nullable','string','max:100'],
                    'apm'                 => ['nullable','string','max:100'],
                    'genero'              => ['required','in:MASCULINO,FEMENINO'],
                    // si tu input es <input type="date">:
                    'fecnac'              => ['nullable','date'],
                    // si envías dd/mm/YYYY, usa: 'fecnac' => ['nullable','date_format:d/m/Y'],
                    'celular'             => ['nullable','string','max:20'],
                    'correo_electronico'  => ['nullable','email','max:150'],
                    'obs'                 => ['nullable','string','max:255'],
                    'province_id'         => ['nullable','integer','exists:provinces,id'],
                    'municipality_id'     => ['nullable','integer','exists:municipalities,id'],
                    'electoral_precinct_id' => ['nullable','integer','exists:electoral_precincts,id'],
                ],[
                    'ci.unique' => 'El C.I. ingresado ya existe en el sistema.',
                ]);

                $miembro->ci      = trim($request->ci);
                $miembro->nombres = $request->nombres;
                $miembro->app     = $request->app;
                $miembro->apm     = $request->apm;
                $miembro->genero  = $request->genero;

                // Si usas dd/mm/YYYY:
                // $miembro->fecnac = $request->filled('fecnac') ? Carbon::createFromFormat('d/m/Y',$request->fecnac)->format('Y-m-d') : null;
                // Si usas <input type="date">:
                $miembro->fecnac  = $request->fecnac;

                $miembro->celular = $request->celular;
                $miembro->correo_electronico = $request->correo_electronico;
                $miembro->obs     = $request->obs;
                $miembro->province_id         = $request->province_id;
                $miembro->municipality_id     = $request->municipality_id;
                $miembro->electoral_precinct_id = $request->electoral_precinct_id;

                try {
                    $miembro->save();
                } catch (QueryException $e) {
                    if (($e->errorInfo[1] ?? null) === 1062) {
                        return back()->withInput()
                            ->with('mensaje','El C.I. ingresado ya existe.')
                            ->with('icono','error');
                    }
                    throw $e;
                }

                return redirect()->route('admin.delegados.index')
                    ->with('mensaje','Se actualizó correctamente')
                    ->with('icono','success');
            }

        public function reporteAgrupacion()
                {
                    $agrupaciones = DB::table('miembros')
                        ->select('agrupa', DB::raw('count(*) as total'))
                        ->groupBy('agrupa')
                        ->orderByDesc('total')
                        ->get();

                    return view('reportes.reporte-agrupacion', compact('agrupaciones'));
                }

                public function importForm()
                {
                    return view('admin.delegados.import');
                }

                public function downloadTemplate()
                {
                    return Excel::download(new DelegadosTemplateExport(), 'plantilla_delegados.xlsx');
                }

                public function importPreview(Request $request)
                {
                    $request->validate([
                        'archivo' => ['required', 'file', 'mimes:xlsx,xls'],
                    ], [
                        'archivo.required' => 'Debe seleccionar un archivo Excel.',
                        'archivo.mimes' => 'El archivo debe ser .xlsx o .xls',
                    ]);

                    $import = new DelegadosImport(true);
                    Excel::import($import, $request->file('archivo'));

                    $resumen = [
                        'delegados_nuevos' => $import->inserted,
                        'delegados_actualizados' => $import->updated,
                        'delegados_omitidos' => $import->skipped,
                        'asig_nuevas' => $import->assignmentsCreated,
                        'asig_actualizadas' => $import->assignmentsUpdated,
                        'asig_omitidas' => $import->assignmentsSkipped,
                    ];

                    return redirect()
                        ->route('delegados.import.form')
                        ->with('preview_summary', $resumen)
                        ->with('preview_errors', array_slice($import->errors, 0, 100))
                        ->with('mensaje', 'Prevalidacion completada. Revise el resumen antes de importar.')
                        ->with('icono', count($import->errors) > 0 ? 'warning' : 'success');
                }

                public function importStore(Request $request)
                {
                    $request->validate([
                        'archivo' => ['required', 'file', 'mimes:xlsx,xls'],
                    ], [
                        'archivo.required' => 'Debe seleccionar un archivo Excel.',
                        'archivo.mimes' => 'El archivo debe ser .xlsx o .xls',
                    ]);

                    $import = new DelegadosImport();
                    Excel::import($import, $request->file('archivo'));

                    $mensaje = "Importacion finalizada. Delegados -> nuevos: {$import->inserted}, actualizados: {$import->updated}, omitidos: {$import->skipped}. "
                        ."Asignaciones -> nuevas: {$import->assignmentsCreated}, actualizadas: {$import->assignmentsUpdated}, omitidas: {$import->assignmentsSkipped}.";
                    $icono = count($import->errors) > 0 ? 'warning' : 'success';

                    return redirect()
                        ->route('delegados.import.form')
                        ->with('mensaje', $mensaje)
                        ->with('icono', $icono)
                        ->with('import_errors', array_slice($import->errors, 0, 50));
                }
                
                public function destroy ($id)
            {
                Miembro::destroy($id);
                return redirect()->route('admin.delegados.index')-> with('mensaje','Se aelimino correctamente');
            }



}
