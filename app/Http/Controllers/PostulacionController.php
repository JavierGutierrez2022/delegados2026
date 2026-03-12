<?php

namespace App\Http\Controllers;

use App\Exports\PostulacionesListadoExport;
use App\Models\Miembro;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class PostulacionController extends Controller
{
    public function index()
    {
        $provincias = DB::table('provinces')->orderBy('name')->get();

        return view('admin.postulaciones.index', [
            'provincias' => $provincias,
            'municipios' => collect(),
            'recintos' => collect(),
        ]);
    }

    public function data(Request $request)
    {
        $q = $this->buildListadoQuery($request);

        return DataTables::of($q)
            ->addIndexColumn()
            ->editColumn('fecnac', fn ($row) => optional($row->fecnac) ? Carbon::parse($row->fecnac)->format('Y-m-d') : '')
            ->addColumn('acciones', function ($row) {
                return view('postulaciones.partials.acciones', ['row' => $row])->render();
            })
            ->rawColumns(['acciones'])
            ->make(true);
    }

    public function exportExcel(Request $request)
    {
        $rows = $this->buildListadoQuery($request)
            ->orderBy('m.nombres')
            ->orderBy('m.app')
            ->orderBy('m.apm')
            ->get()
            ->values()
            ->map(function ($row, $index) {
                return [
                    $index + 1,
                    $row->nombres,
                    $row->app,
                    $row->apm,
                    $row->genero,
                    $row->ci,
                    optional($row->fecnac) ? Carbon::parse($row->fecnac)->format('Y-m-d') : '',
                    $row->celular,
                    $row->provincia,
                    $row->municipio,
                    $row->recinto,
                    $row->obs,
                ];
            })
            ->all();

        return Excel::download(
            new PostulacionesListadoExport($rows),
            'postulaciones_' . now()->format('Ymd_His') . '.xlsx'
        );
    }

    public function show(Miembro $miembro)
    {
        $miembro->load('province', 'municipality', 'electoralPrecinct', 'tables');

        return view('postulaciones.show', compact('miembro'));
    }

    public function edit(Miembro $miembro)
    {
        $provincias = DB::table('provinces')->orderBy('name')->get();
        $municipios = DB::table('municipalities')
            ->where('province_id', $miembro->province_id)
            ->where('state', 'ACTIVO')
            ->orderBy('name')
            ->get();
        $recintos = DB::table('electoral_precincts')
            ->where('municipality_id', $miembro->municipality_id)
            ->where('state', 'ACTIVO')
            ->orderBy('name')
            ->get();

        return view('postulaciones.edit', compact('miembro', 'provincias', 'municipios', 'recintos'));
    }

    public function update(Request $request, Miembro $miembro)
    {
        $request->validate([
            'ci' => ['required', 'string', 'max:20', Rule::unique('miembros', 'ci')->ignore($miembro->id)],
            'nombres' => ['required', 'string', 'max:100'],
            'genero' => ['required', 'in:MASCULINO,FEMENINO'],
            'fecnac' => ['nullable', 'date'],
            'celular' => ['nullable', 'string', 'max:20'],
            'obs' => ['nullable', 'string', 'max:255'],
            'province_id' => ['nullable', 'integer', 'exists:provinces,id'],
            'municipality_id' => ['nullable', 'integer', 'exists:municipalities,id'],
            'electoral_precinct_id' => ['nullable', 'integer', 'exists:electoral_precincts,id'],
        ], [
            'ci.unique' => 'El C.I. ingresado ya existe en el sistema.',
        ]);

        $miembro->ci = trim($request->ci);
        $miembro->nombres = $request->nombres;
        $miembro->app = $request->app;
        $miembro->apm = $request->apm;
        $miembro->genero = $request->genero;
        $miembro->fecnac = $request->fecnac;
        $miembro->celular = $request->celular;
        $miembro->obs = $request->obs;
        $miembro->province_id = $request->province_id;
        $miembro->municipality_id = $request->municipality_id;
        $miembro->electoral_precinct_id = $request->electoral_precinct_id;

        try {
            $miembro->save();
        } catch (QueryException $e) {
            if (($e->errorInfo[1] ?? null) === 1062) {
                return back()->withInput()->with('mensaje', 'El C.I. ingresado ya existe.')->with('icono', 'error');
            }
            throw $e;
        }

        return redirect()->route('postulaciones.index')->with('mensaje', 'Miembro actualizado')->with('icono', 'success');
    }

    public function destroy(Miembro $miembro)
    {
        $miembro->delete();

        if (request()->wantsJson()) {
            return response()->json(['ok' => true]);
        }

        return redirect()->route('postulaciones.index')->with('mensaje', 'Miembro eliminado')->with('icono', 'success');
    }

    public function purge(Request $request)
    {
        abort_unless(auth()->user() && auth()->user()->can('menu.usuarios'), 403, 'SIN AUTORIZACION');

        $total = (int) DB::table('miembros')->count();

        DB::transaction(function () {
            $miembroIds = DB::table('miembros')->pluck('id');
            if ($miembroIds->isNotEmpty()) {
                DB::table('assignments')->whereIn('miembro_id', $miembroIds)->delete();
                DB::table('miembro_table')->whereIn('miembro_id', $miembroIds)->delete();
            }
            DB::table('miembros')->delete();
        });

        $msg = "Borrado general completado. Registros eliminados: {$total}.";

        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'deleted' => $total, 'message' => $msg]);
        }

        return redirect()
            ->route('postulaciones.index')
            ->with('mensaje', $msg)
            ->with('icono', 'success');
    }

    private function buildListadoQuery(Request $request)
    {
        $q = DB::table('miembros as m')
            ->leftJoin('provinces as p', 'p.id', '=', 'm.province_id')
            ->leftJoin('municipalities as mu', 'mu.id', '=', 'm.municipality_id')
            ->leftJoin('electoral_precincts as r', 'r.id', '=', 'm.electoral_precinct_id')
            ->leftJoin('tables as t', 't.id', '=', 'm.table_id')
            ->selectRaw('
                m.id,
                m.nombres,
                m.app,
                m.apm,
                m.genero,
                m.ci,
                m.fecnac,
                m.celular,
                p.name as provincia,
                mu.name as municipio,
                r.name as recinto,
                m.obs
            ')
            ->where('mu.state', 'ACTIVO')
            ->where('r.state', 'ACTIVO');

        $q->when($request->filled('province_id'), fn ($qq) => $qq->where('m.province_id', $request->province_id));
        $q->when($request->filled('municipality_id'), fn ($qq) => $qq->where('m.municipality_id', $request->municipality_id));
        $q->when($request->filled('precinct_id'), fn ($qq) => $qq->where('m.electoral_precinct_id', $request->precinct_id));
        $q->when($request->filled('cedula'), fn ($qq) => $qq->where('m.ci', 'like', '%' . $request->cedula . '%'));
        $q->when($request->filled('telefono'), fn ($qq) => $qq->where('m.celular', 'like', '%' . $request->telefono . '%'));

        return $q;
    }
}
