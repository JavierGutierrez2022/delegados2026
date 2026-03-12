<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class AssignmentCrudController extends Controller
{
    public function index()
    {
        $provincias = DB::table('provinces')->orderBy('name')->get();
        return view('admin.asignados.index', compact('provincias'));
    }

    public function data(Request $request)
    {
        $q = DB::table('assignments as a')
            ->join('miembros as m', 'm.id', '=', 'a.miembro_id')
            ->leftJoin('electoral_precincts as r', 'r.id', '=', 'a.electoral_precinct_id')
            ->leftJoin('municipalities as mu', 'mu.id', '=', 'r.municipality_id')
            ->leftJoin('provinces as p', 'p.id', '=', 'mu.province_id')
            ->leftJoin('tables as t', 't.id', '=', 'a.table_id')
            ->leftJoin('assignments as ar', function ($j) {
                $j->on('ar.miembro_id', '=', 'a.miembro_id')
                    ->on('ar.electoral_precinct_id', '=', 'a.electoral_precinct_id')
                    ->where('ar.scope', 'RECINTO');
            })
            ->selectRaw("
                a.id,
                a.scope,
                a.role as role_code,
                ar.role as recinto_role_code,
                m.id  as miembro_id,
                m.nombres, m.app, m.apm, m.celular, m.ci,
                p.name  as provincia,
                mu.name as municipio,
                r.name  as recinto,
                r.circuns as circuns,
                t.table_number as mesa
            ");

        $q->when($request->filled('circuns'), fn($qq) =>
            $qq->where('r.circuns', $request->circuns)
        );
        $q->when($request->filled('province_id'), fn($qq) =>
            $qq->where('p.id', $request->province_id)
        );
        $q->when($request->filled('municipality_id'), fn($qq) =>
            $qq->where('mu.id', $request->municipality_id)
        );
        $q->when($request->filled('precinct_id'), fn($qq) =>
            $qq->where('a.electoral_precinct_id', $request->precinct_id)
        );
        $q->when($request->filled('table_id'), fn($qq) =>
            $qq->where('a.table_id', $request->table_id)
        );
        $q->when($request->filled('cedula'), fn($qq) =>
            $qq->where('m.ci', 'like', '%' . $request->cedula . '%')
        );
        $q->when($request->filled('telefono'), fn($qq) =>
            $qq->where('m.celular', 'like', '%' . $request->telefono . '%')
        );

        return DataTables::of($q)
            ->addIndexColumn()
            ->addColumn('nombre_completo', fn($row) =>
                trim("{$row->nombres} {$row->app} {$row->apm}")
            )
            ->addColumn('ambito', fn($row) => $row->scope === 'MESA' ? 'MESA' : 'RECINTO')
            ->addColumn('tipo_rol', function ($row) {
                $code = $row->role_code ?? null;
                $map = [
                    'JEFE_DE_RECINTO' => 'Jefe de Recinto',
                    'MONITOR_RADAR' => 'Monitor / Radar',
                    'DELEGADO_PROPIETARIO' => 'Delegado Propietario',
                    'DELEGADO_SUPLENTE' => 'Delegado Suplente',
                ];
                return $code ? ($map[$code] ?? $code) : '—';
            })
            ->addColumn('rol_recinto', function ($row) {
                $code = $row->recinto_role_code ?? null;
                if (!$code) {
                    return '—';
                }
                $map = [
                    'JEFE_DE_RECINTO' => 'Jefe de Recinto',
                    'MONITOR_RADAR' => 'Monitor / Radar',
                ];
                return $map[$code] ?? $code;
            })
            ->addColumn('acciones', function ($row) {
                return view('admin.asignados.partials.acciones', compact('row'))->render();
            })
            ->rawColumns(['acciones'])
            ->make(true);
    }

    public function show(Assignment $assignment)
    {
        $assignment->load(['miembro', 'precinct', 'table']);
        return view('admin.asignados.show', compact('assignment'));
    }

    public function edit(Assignment $assignment)
    {
        $assignment->load(['miembro', 'precinct', 'table']);
        $provincias = DB::table('provinces')->orderBy('name')->get();
        $municipios = DB::table('municipalities')
            ->where('province_id', $assignment->miembro->province_id)
            ->where('state', 'ACTIVO')
            ->orderBy('name')
            ->get();
        $recintos = DB::table('electoral_precincts')
            ->where('municipality_id', $assignment->miembro->municipality_id)
            ->where('state', 'ACTIVO')
            ->orderBy('name')
            ->get();
        $mesas = DB::table('tables')
            ->where('electoral_precinct_id', $assignment->electoral_precinct_id)
            ->orderBy('table_number')
            ->get();

        return view('admin.asignados.edit', compact('assignment', 'provincias', 'municipios', 'recintos', 'mesas'));
    }

    public function update(Request $request, Assignment $assignment)
    {
        $request->validate([
            'scope' => ['required', Rule::in(['RECINTO', 'MESA'])],
            'role' => ['required', 'string', 'max:100'],
            'electoral_precinct_id' => ['required', 'exists:electoral_precincts,id'],
            'table_id' => [
                Rule::requiredIf(fn() => $request->scope === 'MESA'),
                'nullable',
                'exists:tables,id',
            ],
            'miembro_id' => [
                'required',
                'exists:miembros,id',
                Rule::unique('assignments', 'miembro_id')
                    ->where(fn($q) => $q->where('scope', $request->scope))
                    ->ignore($assignment->id),
            ],
        ], [
            'miembro_id.unique' => 'Este postulado ya tiene una asignacion en este ambito.',
        ]);

        $assignment->scope = $request->scope;
        $assignment->role = $request->role;
        $assignment->miembro_id = $request->miembro_id;
        $assignment->electoral_precinct_id = $request->electoral_precinct_id;
        $assignment->table_id = $request->scope === 'MESA' ? $request->table_id : null;
        $assignment->save();

        return redirect()->route('asignados.index')
            ->with('mensaje', 'Asignacion actualizada')
            ->with('icono', 'success');
    }

    public function destroy(Assignment $assignment)
    {
        $assignment->delete();
        if (request()->wantsJson()) {
            return response()->json(['ok' => true]);
        }
        return back()->with('mensaje', 'Asignacion eliminada')->with('icono', 'success');
    }
}

