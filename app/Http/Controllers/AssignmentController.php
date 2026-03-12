<?php
namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Miembro;
use App\Models\Table;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AssignmentController extends Controller
{
    public function create()
    {
        $provincias = DB::table('provinces')->orderBy('name')->get();

        // columna real de circuns en electoral_precincts (por si cambia nombre)
        $circunsCol = $this->circunsColumn();
        $circunsiones = $circunsCol
            ? DB::table('electoral_precincts')->where('state', 'ACTIVO')->select($circunsCol)->distinct()->orderBy($circunsCol)->pluck($circunsCol)
            : collect();

        return view('admin.asignaciones.create', compact('provincias','circunsiones'));
    }

    /** Guarda asignaciones de un recinto o de una mesa */
   public function store(Request $request)
{
    $request->validate([
        'scope'  => ['required', Rule::in(['RECINTO','MESA'])],
        'province_id' => ['nullable','integer','exists:provinces,id'],
        'municipality_id' => ['nullable','integer','exists:municipalities,id'],
        'electoral_precinct_id' => ['required','integer','exists:electoral_precincts,id'],
        'table_id' => ['nullable','integer','exists:tables,id'], // requerido si scope=MESA
        'roles'   => ['required','array'],
        'assign_jefe_to_tables' => ['nullable', Rule::in(['0', '1', 0, 1])],
        'jefe_table_mode' => ['nullable', Rule::in(['ALL', 'SELECTED'])],
        'jefe_table_ids' => ['nullable', 'array'],
        'jefe_table_ids.*' => ['integer', 'exists:tables,id'],
    ]);

    if ($request->scope === 'MESA' && empty($request->table_id)) {
        return back()->with('mensaje','Debe seleccionar la Mesa')->with('icono','error')->withInput();
    }

    $jefeMiembroId = (int) ($request->input('roles.JEFE_DE_RECINTO') ?? 0);
    $assignJefeToTables = $request->scope === 'RECINTO' && $request->boolean('assign_jefe_to_tables');
    $targetJefeTableIds = [];

    if ($assignJefeToTables) {
        if (!$jefeMiembroId) {
            return back()->with('mensaje','Debe asignar primero un Jefe de Recinto para cargarlo en mesas.')
                ->with('icono','error')->withInput();
        }

        $mode = strtoupper((string) $request->input('jefe_table_mode', ''));
        if (!in_array($mode, ['ALL', 'SELECTED'], true)) {
            return back()->with('mensaje','Debe elegir si el jefe cubrirá todas las mesas o mesas específicas.')
                ->with('icono','error')->withInput();
        }

        if ($mode === 'ALL') {
            $targetJefeTableIds = Table::query()
                ->where('electoral_precinct_id', $request->electoral_precinct_id)
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();
        } else {
            $targetJefeTableIds = collect($request->input('jefe_table_ids', []))
                ->map(fn ($id) => (int) $id)
                ->filter()
                ->unique()
                ->values()
                ->all();
        }

        if (empty($targetJefeTableIds)) {
            return back()->with('mensaje','Debe seleccionar al menos una mesa para asignar al jefe como Delegado Propietario.')
                ->with('icono','error')->withInput();
        }

        $validTableIds = Table::query()
            ->where('electoral_precinct_id', $request->electoral_precinct_id)
            ->whereIn('id', $targetJefeTableIds)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if (count($validTableIds) !== count($targetJefeTableIds)) {
            return back()->with('mensaje','Una o más mesas seleccionadas no pertenecen al recinto elegido.')
                ->with('icono','error')->withInput();
        }

        $occupiedTableIds = Assignment::query()
            ->where('scope', 'MESA')
            ->where('electoral_precinct_id', $request->electoral_precinct_id)
            ->whereIn('table_id', $targetJefeTableIds)
            ->where('role', 'DELEGADO_PROPIETARIO')
            ->where('miembro_id', '!=', $jefeMiembroId)
            ->pluck('table_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if (!empty($occupiedTableIds)) {
            $tableNumbers = Table::query()
                ->whereIn('id', $occupiedTableIds)
                ->orderByRaw('CAST(table_number AS UNSIGNED)')
                ->pluck('table_number')
                ->map(fn ($n) => 'Mesa ' . $n)
                ->implode(', ');

            return back()->with('mensaje', "No se pudo asignar el jefe como Delegado Propietario. Estas mesas ya tienen propietario: {$tableNumbers}")
                ->with('icono','error')->withInput();
        }
    }

    // --- no permitir mismo postulado en dos roles del mismo ámbito en esta acción
    $chosen = array_values(array_filter($request->roles ?? [], fn($v)=>!empty($v)));
    if (count($chosen) !== count(array_unique($chosen))) {
        return back()->with('mensaje','Un mismo postulado no puede ocupar dos roles en el mismo ámbito.')
                     ->with('icono','error')->withInput();
    }

    // --- no permitir si YA está asignado en ese MISMO ámbito (en cualquier lugar)
    $conflicts = Assignment::where('scope', $request->scope)
        ->whereIn('miembro_id', $chosen)
        ->when($request->scope === 'RECINTO', function ($q) use ($request) {
            $q->where(function ($sub) use ($request) {
                $sub->where('electoral_precinct_id', '!=', $request->electoral_precinct_id)
                    ->orWhereNull('electoral_precinct_id');
            });
        })
        ->when($request->scope === 'MESA', function ($q) use ($request) {
            $q->where(function ($sub) use ($request) {
                $sub->where('table_id', '!=', $request->table_id)
                    ->orWhereNull('table_id');
            });
        })
        ->with('miembro:id,nombres,app,apm')
        ->get();

    if ($conflicts->isNotEmpty()) {
        $nombres = $conflicts->map(fn($a) =>
            trim(($a->miembro->nombres ?? '').' '.($a->miembro->app ?? '').' '.($a->miembro->apm ?? ''))
        )->implode(', ');
        return back()->with('mensaje', "Los siguientes postulados ya tienen una asignación en este ámbito: {$nombres}")
                     ->with('icono','error')->withInput();
    }

    // === CLAVE: normaliza table_id (0 para RECINTO, id real para MESA) ===
    $tableId = $request->scope === 'MESA' ? (int)$request->table_id : null;   // <- queda igual
    $tableKey = $request->scope === 'MESA' ? (int)$request->table_id : 0;     // <- NUEVO
    try {
                DB::transaction(function () use ($request, $tableId, $tableKey, $assignJefeToTables, $jefeMiembroId, $targetJefeTableIds) {

            if ($request->scope === 'RECINTO') {
                // Limpia los mismos roles de ese recinto (NO filtres por table_id)
                Assignment::where('scope', 'RECINTO')
                    ->where('electoral_precinct_id', $request->electoral_precinct_id)
                    ->whereIn('role', array_keys($request->roles))
                    ->delete();

                foreach ($request->roles as $role => $miembroId) {
                    if (!$miembroId) continue;

                    Assignment::create([
                        'scope'                 => 'RECINTO',
                        'electoral_precinct_id' => $request->electoral_precinct_id,
                        'table_id'              => null,   // FK intacta
                        'table_key'             => 0,      // ⬅️ AQUI
                        'role'                  => $role,
                        'miembro_id'            => $miembroId,
                    ]);
                }

                if ($assignJefeToTables && $jefeMiembroId) {
                    foreach ($targetJefeTableIds as $targetTableId) {
                        Assignment::firstOrCreate(
                            [
                                'scope' => 'MESA',
                                'electoral_precinct_id' => $request->electoral_precinct_id,
                                'table_id' => $targetTableId,
                                'table_key' => $targetTableId,
                                'role' => 'DELEGADO_PROPIETARIO',
                            ],
                            [
                                'miembro_id' => $jefeMiembroId,
                            ]
                        );
                    }
                }

            } else { // MESA
                Assignment::where('scope', 'MESA')
                    ->where('electoral_precinct_id', $request->electoral_precinct_id)
                    ->where('table_id', $tableId)
                    ->whereIn('role', array_keys($request->roles))
                    ->delete();

                foreach ($request->roles as $role => $miembroId) {
                    if (!$miembroId) continue;

                    Assignment::create([
                        'scope'                 => 'MESA',
                        'electoral_precinct_id' => $request->electoral_precinct_id,
                        'table_id'              => $tableId,   // id real de mesa
                        'table_key'             => $tableKey,  // ⬅️ AQUI
                        'role'                  => $role,
                        'miembro_id'            => $miembroId,
                    ]);
                }
            }
        });
    } catch (\Illuminate\Database\QueryException $e) {
        // captura duplicados por índices únicos y muestra un mensaje más preciso
        if (($e->errorInfo[1] ?? null) === 1062) {
            $duplicateMessage = (string) ($e->errorInfo[2] ?? $e->getMessage());

            if (str_contains($duplicateMessage, 'uniq_scope_member')) {
                return back()->with('mensaje', 'Ese postulado ya tiene otra asignación en este ámbito. La restricción anterior impedía usar al mismo jefe en varias mesas.')
                             ->with('icono', 'error')->withInput();
            }

            return back()->with('mensaje', 'Ya existe una asignación para ese rol en esta ubicación.')
                         ->with('icono', 'error')->withInput();
        }
        throw $e;
    }

    return back()->with('mensaje','Asignación guardada correctamente')->with('icono','success');
}

    /** Lista de postulados (miembros) disponibles filtrados por provincia/municipio/recinto/busqueda */
            public function postulados(Request $request)
            {
                $q = DB::table('miembros as m')
                    ->select('m.id','m.ci','m.nombres','m.app','m.apm','m.celular');

                if ($request->filled('province_id'))     $q->where('m.province_id', $request->province_id);
                if ($request->filled('municipality_id')) $q->where('m.municipality_id', $request->municipality_id);
                if ($request->filled('electoral_precinct_id')) $q->where('m.electoral_precinct_id', $request->electoral_precinct_id);

                if ($request->filled('term')) {
                    $t = '%'.$request->term.'%';
                    $q->where(function($w) use ($t){
                        $w->where('m.ci','like',$t)
                        ->orWhere('m.nombres','like',$t)
                        ->orWhere('m.app','like',$t)
                        ->orWhere('m.apm','like',$t)
                        ->orWhere('m.celular','like',$t);
                    });
                }

                // ⛔ EXCLUIR asignados en cualquier ámbito (recinto o mesa)
                // Si quisieras excluir solo los asignados en el mismo scope, cambia la línea comentada.
                $excludeIds = \App\Models\Assignment::pluck('miembro_id');                 // GLOBAL
                // $excludeIds = \App\Models\Assignment::where('scope',$request->scope)->pluck('miembro_id'); // SOLO MISMO SCOPE
                if ($excludeIds->count() > 0) {
                    $q->whereNotIn('m.id', $excludeIds);
                }

                return response()->json($q->orderBy('m.nombres')->limit(50)->get());
            }

    /** Devuelve asignados actuales (para mostrar en los “slots” de roles) */
    public function actuales(Request $request)
    {
        $request->validate([
            'scope' => ['required', Rule::in(['RECINTO','MESA'])],
            'electoral_precinct_id' => ['required','integer'],
            'table_id' => ['nullable','integer'],
        ]);

        $q = Assignment::with('miembro:id,ci,nombres,app,apm,celular')
            ->where('scope', $request->scope)
            ->where('electoral_precinct_id', $request->electoral_precinct_id);

        if ($request->scope === 'MESA' && $request->filled('table_id')) {
            $q->where('table_id', $request->table_id);
        }

        $items = $q->get()->map(function($a){
            return [
                'id' => $a->id,
                'role' => $a->role,
                'miembro' => [
                    'id' => $a->miembro->id,
                    'ci' => $a->miembro->ci,
                    'nombre' => trim($a->miembro->nombres.' '.$a->miembro->app.' '.$a->miembro->apm),
                    'celular' => $a->miembro->celular,
                ]
            ];
        });

        return response()->json($items);
    }

    public function destroy(Assignment $assignment)
    {
        $assignment->delete();
        return response()->json(['ok'=>true]);
    }

    /** Detecta la columna de circunscripción que tengas en electoral_precincts */
    private function circunsColumn(): ?string
    {
        foreach (['circuns','circunscripcion','circunscripción','circumscription'] as $c) {
            if (\Illuminate\Support\Facades\Schema::hasColumn('electoral_precincts', $c)) {
                return $c;
            }
        }
        return null;
    }
}
