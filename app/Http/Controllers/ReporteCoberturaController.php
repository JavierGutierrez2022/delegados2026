<?php

namespace App\Http\Controllers;

use App\Exports\CoberturaDistritoDetalleExport;
use App\Exports\CoberturaMatrizExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class ReporteCoberturaController extends Controller
{
    public function index()
    {
        $provincias = DB::table('provinces')->orderBy('name')->get();
        return view('reportes.cobertura.index', compact('provincias'));
    }

    public function matriz(Request $r)
    {
        return response()->json($this->buildMatriz($r));
    }

    public function matrizExcel(Request $r)
    {
        if ($r->filled('matriz_distrito')) {
            $target = mb_strtoupper(trim((string) $r->matriz_distrito));
            $slug = $target === 'TODOS'
                ? 'todos'
                : (preg_replace('/[^A-Za-z0-9_-]+/', '_', $target) ?: 'distrito');
            $detalle = $this->buildDistritoDetalle($r, $target);
            return Excel::download(
                new CoberturaDistritoDetalleExport($detalle),
                'reporte_detalle_' . $slug . '.xlsx'
            );
        }

        $matriz = $this->buildMatriz($r);
        $filename = 'reporte_matriz_cobertura.xlsx';

        if ($r->filled('matriz_status')) {
            $status = strtolower(trim((string) $r->matriz_status));
            $matriz['rows'] = $this->filterMatrizRowsByStatus($matriz['rows'], $status);
            if ($status === 'faltantes') {
                $filename = 'reporte_matriz_faltantes.xlsx';
            } elseif ($status === 'completos') {
                $filename = 'reporte_matriz_100.xlsx';
            }
        }

        return Excel::download(new CoberturaMatrizExport($matriz['rows']), $filename);
    }

    public function data(Request $r)
    {
        // base: todas las mesas con su jerarquía
        $q = DB::table('tables as t')
            ->join('electoral_precincts as e', 'e.id', '=', 't.electoral_precinct_id')
            ->leftJoin('municipalities as mu', 'mu.id', '=', 'e.municipality_id')
            ->leftJoin('provinces as p', 'p.id', '=', 'mu.province_id')
            ->where('t.state', 'ACTIVO')
            ->where('e.state', 'ACTIVO')
            ->where('mu.state', 'ACTIVO')
            ->selectRaw("
                p.name  as provincia,
                mu.name as municipio,
                e.name  as recinto,
                t.id    as mesa_id,
                t.table_number as mesa,
                -- flags de cobertura
                EXISTS(
                    SELECT 1
                    FROM assignments a
                    JOIN miembros mm ON mm.id = a.miembro_id
                    WHERE a.scope='MESA' AND a.table_id=t.id AND a.role='DELEGADO_PROPIETARIO'
                ) as has_propietario,
                EXISTS(
                    SELECT 1
                    FROM assignments a
                    JOIN miembros mm ON mm.id = a.miembro_id
                    WHERE a.scope='MESA' AND a.table_id=t.id AND a.role='DELEGADO_SUPLENTE'
                ) as has_suplente,
                EXISTS(
                    SELECT 1
                    FROM assignments a
                    JOIN miembros mm ON mm.id = a.miembro_id
                    WHERE a.scope='RECINTO' AND a.electoral_precinct_id=e.id AND a.role='JEFE_DE_RECINTO'
                ) as has_jefe
            ");

        // filtros
        if ($r->filled('province_id'))     $q->where('p.id',  $r->province_id);
        if ($r->filled('municipality_id')) $q->where('mu.id', $r->municipality_id);
        if ($r->filled('district_id')) {
            $districtIds = array_values(array_filter(array_map('intval', explode(',', (string) $r->district_id))));
            if (!empty($districtIds)) {
                $q->whereIn('e.district_id', $districtIds);
            } else {
                $q->where('e.district_id', (int) $r->district_id);
            }
        }
        if ($r->filled('precinct_id'))     $q->where('e.id',  $r->precinct_id);
        if ($r->filled('table_id'))        $q->where('t.id',  $r->table_id);

        return DataTables::of($q)
            ->addColumn('delegado_prop', fn($row) => $row->has_propietario
                ? '<i class="bi bi-check-circle-fill text-success"></i>'
                : '<i class="bi bi-exclamation-triangle-fill text-warning"></i>')
            ->addColumn('delegado_supl', fn($row) => $row->has_suplente
                ? '<i class="bi bi-check-circle-fill text-success"></i>'
                : '<i class="bi bi-exclamation-triangle-fill text-warning"></i>')
            ->addColumn('jefe_recinto', fn($row) => $row->has_jefe
                ? '<i class="bi bi-check-circle-fill text-success"></i>'
                : '<i class="bi bi-exclamation-triangle-fill text-warning"></i>')
            ->addColumn('cubierta', function($row){
                $covered = $row->has_propietario && $row->has_jefe;
                return $covered
                    ? '<span class="badge bg-success"><i class="bi bi-check-circle"></i> CUBIERTA</span>'
                    : '<span class="badge bg-secondary"><i class="bi bi-exclamation-circle"></i> PENDIENTE</span>';
            })
            ->rawColumns(['delegado_prop','delegado_supl','jefe_recinto','cubierta'])
            ->make(true);
    }

    // KPIs superiores (totales / cubiertas / pendientes)
    public function resumen(Request $r)
    {
        $base = DB::table('tables as t')
            ->join('electoral_precincts as e', 'e.id', '=', 't.electoral_precinct_id')
            ->leftJoin('municipalities as mu', 'mu.id', '=', 'e.municipality_id')
            ->leftJoin('provinces as p', 'p.id', '=', 'mu.province_id')
            ->where('t.state', 'ACTIVO')
            ->where('e.state', 'ACTIVO')
            ->where('mu.state', 'ACTIVO')
            ->select('t.id','e.id as eid');

        if ($r->filled('province_id'))     $base->where('p.id',  $r->province_id);
        if ($r->filled('municipality_id')) $base->where('mu.id', $r->municipality_id);
        if ($r->filled('district_id')) {
            $districtIds = array_values(array_filter(array_map('intval', explode(',', (string) $r->district_id))));
            if (!empty($districtIds)) {
                $base->whereIn('e.district_id', $districtIds);
            } else {
                $base->where('e.district_id', (int) $r->district_id);
            }
        }
        if ($r->filled('precinct_id'))     $base->where('e.id',  $r->precinct_id);
        if ($r->filled('table_id'))        $base->where('t.id',  $r->table_id);

        $mesas = $base->get();

        $total = $mesas->count();
        $cubiertas = 0;

        foreach ($mesas as $m) {
            $hasProp = DB::table('assignments as a')
                ->join('miembros as mm', 'mm.id', '=', 'a.miembro_id')
                ->where('a.scope', 'MESA')
                ->where('a.table_id', $m->id)
                ->where('a.role', 'DELEGADO_PROPIETARIO')
                ->exists();

            $hasJefe = DB::table('assignments as a')
                ->join('miembros as mm', 'mm.id', '=', 'a.miembro_id')
                ->where('a.scope', 'RECINTO')
                ->where('a.electoral_precinct_id', $m->eid)
                ->where('a.role', 'JEFE_DE_RECINTO')
                ->exists();

            if ($hasProp && $hasJefe) {
                $cubiertas++;
            }
        }

        return response()->json([
            'total'     => $total,
            'cubiertas' => $cubiertas,
            'pendientes'=> max($total - $cubiertas, 0),
        ]);
    }

    private function buildMatriz(Request $r): array
    {
        $q = DB::table('tables as t')
            ->join('electoral_precincts as e', 'e.id', '=', 't.electoral_precinct_id')
            ->leftJoin('municipalities as mu', 'mu.id', '=', 'e.municipality_id')
            ->leftJoin('provinces as p', 'p.id', '=', 'mu.province_id')
            ->leftJoin('districts as d', 'd.id', '=', 'e.district_id')
            ->where('t.state', 'ACTIVO')
            ->where('e.state', 'ACTIVO')
            ->where('mu.state', 'ACTIVO')
            ->selectRaw('
                t.id as mesa_id,
                e.id as recinto_id,
                e.distric_number as distric_number,
                e.electoral_seat as electoral_seat,
                d.name as district_name,
                mu.name as municipio
            ');

        if ($r->filled('province_id'))     $q->where('p.id',  $r->province_id);
        if ($r->filled('municipality_id')) $q->where('mu.id', $r->municipality_id);
        if ($r->filled('district_id')) {
            $districtIds = array_values(array_filter(array_map('intval', explode(',', (string) $r->district_id))));
            if (!empty($districtIds)) {
                $q->whereIn('e.district_id', $districtIds);
            } else {
                $q->where('e.district_id', (int) $r->district_id);
            }
        }
        if ($r->filled('precinct_id'))     $q->where('e.id',  $r->precinct_id);
        if ($r->filled('table_id'))        $q->where('t.id',  $r->table_id);

        $items = $q->get();
        if ($items->isEmpty()) {
            return ['rows' => []];
        }

        $groupTables = [];
        $groupRecintos = [];
        $mesaToRecinto = [];

        foreach ($items as $it) {
            $districtLabel = $this->matrizGroupLabel(
                $it->municipio,
                $it->distric_number,
                $it->district_name,
                $it->electoral_seat
            );
            $groupTables[$districtLabel][] = (int) $it->mesa_id;
            $groupRecintos[$districtLabel][] = (int) $it->recinto_id;
            $mesaToRecinto[(int) $it->mesa_id] = (int) $it->recinto_id;
        }

        $allMesaIds = array_values(array_unique(array_map('intval', array_keys($mesaToRecinto))));
        $allRecintoIds = array_values(array_unique(array_map('intval', array_values($mesaToRecinto))));

        $jefeRecintos = DB::table('assignments as a')
            ->join('miembros as mm', 'mm.id', '=', 'a.miembro_id')
            ->where('a.scope', 'RECINTO')
            ->where('a.role', 'JEFE_DE_RECINTO')
            ->whereIn('a.electoral_precinct_id', $allRecintoIds)
            ->pluck('a.electoral_precinct_id')
            ->map(fn($x) => (int) $x)
            ->unique()
            ->values()
            ->all();
        $jefeSet = array_fill_keys($jefeRecintos, true);

        $mesaRoles = DB::table('assignments as a')
            ->join('miembros as mm', 'mm.id', '=', 'a.miembro_id')
            ->where('a.scope', 'MESA')
            ->whereIn('a.table_id', $allMesaIds)
            ->selectRaw("
                a.table_id,
                SUM(a.role='DELEGADO_PROPIETARIO') as prop
            ")
            ->groupBy('a.table_id')
            ->get();

        $coveredMesaSet = [];
        foreach ($mesaRoles as $mr) {
            $tableId = (int) $mr->table_id;
            $recintoId = $mesaToRecinto[$tableId] ?? 0;
            if ((int) $mr->prop >= 1 && isset($jefeSet[$recintoId])) {
                $coveredMesaSet[$tableId] = true;
            }
        }

        $rows = [];
        $i = 1;
        $totRegRec = 0;
        $totRegMes = 0;
        $totReqRec = 0;
        $totReqMes = 0;

        ksort($groupTables);
        foreach ($groupTables as $district => $mesaIds) {
            $mesaIds = array_values(array_unique(array_map('intval', $mesaIds)));
            $recintoIds = array_values(array_unique(array_map('intval', $groupRecintos[$district] ?? [])));

            $reqRec = count($recintoIds);
            $reqMes = count($mesaIds);
            $regRec = count(array_filter($recintoIds, fn($rid) => isset($jefeSet[$rid])));
            $regMes = count(array_filter($mesaIds, fn($mid) => isset($coveredMesaSet[$mid])));

            $difRec = $regRec - $reqRec;
            $difMes = $regMes - $reqMes;
            $reqTotal = $reqRec + $reqMes;
            $regTotal = $regRec + $regMes;
            $cobTotal = $regTotal - $reqTotal;
            $pct = $reqTotal > 0 ? round(($regTotal / $reqTotal) * 100, 2) : 0;

            $rows[] = [
                'nro' => $i++,
                'distrito' => $district,
                'reg_recintos' => $regRec,
                'reg_mesas' => $regMes,
                'req_recintos' => $reqRec,
                'dif_recintos' => $difRec,
                'req_mesas' => $reqMes,
                'dif_mesas' => $difMes,
                'req_total' => $reqTotal,
                'cob_total' => $cobTotal,
                'porcentaje' => number_format($pct, 2, ',', '.') . '%',
            ];

            $totRegRec += $regRec;
            $totRegMes += $regMes;
            $totReqRec += $reqRec;
            $totReqMes += $reqMes;
        }

        $totReqTotal = $totReqRec + $totReqMes;
        $totRegTotal = $totRegRec + $totRegMes;
        $totCobTotal = $totRegTotal - $totReqTotal;
        $totPct = $totReqTotal > 0 ? round(($totRegTotal / $totReqTotal) * 100, 2) : 0;

        $rows[] = [
            'nro' => '',
            'distrito' => 'TOTAL',
            'reg_recintos' => $totRegRec,
            'reg_mesas' => $totRegMes,
            'req_recintos' => $totReqRec,
            'dif_recintos' => $totRegRec - $totReqRec,
            'req_mesas' => $totReqMes,
            'dif_mesas' => $totRegMes - $totReqMes,
            'req_total' => $totReqTotal,
            'cob_total' => $totCobTotal,
            'porcentaje' => number_format($totPct, 2, ',', '.') . '%',
        ];

        return ['rows' => $rows];
    }

    private function districtLabel(?string $municipio, $districNumber, ?string $districtName): string
    {
        $mun = mb_strtolower(trim((string) $municipio));
        $num = null;
        if (preg_match('/\d+/', (string) $districNumber, $m) === 1) {
            $num = (int) $m[0];
        } elseif (preg_match('/\d+/', (string) $districtName, $m2) === 1) {
            $num = (int) $m2[0];
        }

        if ($mun === 'tarija' && $num !== null && $num >= 14 && $num <= 25) {
            return 'RURAL';
        }

        if ($num !== null) {
            return 'D' . $num;
        }

        $name = trim((string) $districtName);
        return $name !== '' ? strtoupper($name) : 'SIN DISTRITO';
    }

    private function matrizGroupLabel(?string $municipio, $districNumber, ?string $districtName, ?string $electoralSeat): string
    {
        $mun = mb_strtolower(trim((string) $municipio));
        if ($mun !== 'tarija') {
            $seat = trim((string) $electoralSeat);
            return $seat !== '' ? strtoupper($seat) : 'SIN ASIENTO';
        }

        return $this->districtLabel($municipio, $districNumber, $districtName);
    }

    private function buildDistritoDetalle(Request $r, string $targetDistrict): array
    {
        $q = DB::table('tables as t')
            ->join('electoral_precincts as e', 'e.id', '=', 't.electoral_precinct_id')
            ->leftJoin('municipalities as mu', 'mu.id', '=', 'e.municipality_id')
            ->leftJoin('provinces as p', 'p.id', '=', 'mu.province_id')
            ->leftJoin('districts as d', 'd.id', '=', 'e.district_id')
            ->where('t.state', 'ACTIVO')
            ->where('e.state', 'ACTIVO')
            ->where('mu.state', 'ACTIVO')
            ->selectRaw("
                t.id as mesa_id,
                e.id as recinto_id,
                p.name as provincia,
                mu.name as municipio,
                e.name as recinto,
                t.table_number as mesa,
                e.distric_number as distric_number,
                e.electoral_seat as electoral_seat,
                d.name as district_name,
                EXISTS(
                    SELECT 1
                    FROM assignments a
                    JOIN miembros mm ON mm.id = a.miembro_id
                    WHERE a.scope='MESA' AND a.table_id=t.id AND a.role='DELEGADO_PROPIETARIO'
                ) as has_propietario,
                EXISTS(
                    SELECT 1
                    FROM assignments a
                    JOIN miembros mm ON mm.id = a.miembro_id
                    WHERE a.scope='MESA' AND a.table_id=t.id AND a.role='DELEGADO_SUPLENTE'
                ) as has_suplente,
                EXISTS(
                    SELECT 1
                    FROM assignments a
                    JOIN miembros mm ON mm.id = a.miembro_id
                    WHERE a.scope='RECINTO' AND a.electoral_precinct_id=e.id AND a.role='JEFE_DE_RECINTO'
                ) as has_jefe
            ");

        if ($r->filled('province_id'))     $q->where('p.id',  $r->province_id);
        if ($r->filled('municipality_id')) $q->where('mu.id', $r->municipality_id);
        if ($r->filled('district_id')) {
            $districtIds = array_values(array_filter(array_map('intval', explode(',', (string) $r->district_id))));
            if (!empty($districtIds)) {
                $q->whereIn('e.district_id', $districtIds);
            } else {
                $q->where('e.district_id', (int) $r->district_id);
            }
        }
        if ($r->filled('precinct_id'))     $q->where('e.id',  $r->precinct_id);
        if ($r->filled('table_id'))        $q->where('t.id',  $r->table_id);

        $target = mb_strtoupper(trim($targetDistrict));
        $exportAll = $target === 'TODOS';
        $baseRows = $q->orderBy('p.name')->orderBy('mu.name')->orderBy('e.name')->orderBy('t.table_number')->get();
        if ($baseRows->isEmpty()) {
            return [];
        }

        $mesaIds = $baseRows->pluck('mesa_id')->map(fn($x) => (int) $x)->unique()->values()->all();
        $recintoIds = $baseRows->pluck('recinto_id')->map(fn($x) => (int) $x)->unique()->values()->all();

        $mesaAssignments = DB::table('assignments as a')
            ->join('miembros as m', 'm.id', '=', 'a.miembro_id')
            ->where('a.scope', 'MESA')
            ->whereIn('a.table_id', $mesaIds)
            ->whereIn('a.role', ['DELEGADO_PROPIETARIO', 'DELEGADO_SUPLENTE'])
            ->selectRaw("
                a.table_id,
                a.role,
                m.nombres,
                m.app,
                m.apm,
                m.ci,
                m.celular
            ")
            ->get();

        $jefeAssignments = DB::table('assignments as a')
            ->join('miembros as m', 'm.id', '=', 'a.miembro_id')
            ->where('a.scope', 'RECINTO')
            ->where('a.role', 'JEFE_DE_RECINTO')
            ->whereIn('a.electoral_precinct_id', $recintoIds)
            ->selectRaw("
                a.electoral_precinct_id as recinto_id,
                m.nombres,
                m.app,
                m.apm,
                m.ci,
                m.celular
            ")
            ->get();

        $mapMesa = [];
        foreach ($mesaAssignments as $a) {
            $tableId = (int) $a->table_id;
            $role = (string) $a->role;
            $mapMesa[$tableId][$role][] = $this->formatPersona(
                $a->nombres,
                $a->app,
                $a->apm,
                $a->ci,
                $a->celular
            );
        }

        $mapJefe = [];
        foreach ($jefeAssignments as $a) {
            $rid = (int) $a->recinto_id;
            $mapJefe[$rid][] = $this->formatPersona(
                $a->nombres,
                $a->app,
                $a->apm,
                $a->ci,
                $a->celular
            );
        }

        $rows = [];
        foreach ($baseRows as $item) {
            $label = $this->matrizGroupLabel(
                $item->municipio,
                $item->distric_number,
                $item->district_name,
                $item->electoral_seat
            );
            if (!$exportAll && mb_strtoupper($label) !== $target) {
                continue;
            }

            $cubierta = (int) $item->has_propietario === 1
                && (int) $item->has_jefe === 1;

            $rows[] = [
                'DISTRITO' => $label,
                'PROVINCIA' => $item->provincia,
                'MUNICIPIO' => $item->municipio,
                'RECINTO' => $item->recinto,
                'MESA' => $item->mesa,
                'DELEGADO_PROPIETARIO' => (int) $item->has_propietario === 1 ? 'SI' : 'NO',
                'DATOS_DELEGADO_PROPIETARIO' => implode(' | ', $mapMesa[(int) $item->mesa_id]['DELEGADO_PROPIETARIO'] ?? []) ?: '-',
                'DELEGADO_SUPLENTE' => (int) $item->has_suplente === 1 ? 'SI' : 'NO',
                'DATOS_DELEGADO_SUPLENTE' => implode(' | ', $mapMesa[(int) $item->mesa_id]['DELEGADO_SUPLENTE'] ?? []) ?: '-',
                'JEFE_RECINTO' => (int) $item->has_jefe === 1 ? 'SI' : 'NO',
                'DATOS_JEFE_RECINTO' => implode(' | ', $mapJefe[(int) $item->recinto_id] ?? []) ?: '-',
                'ESTADO' => $cubierta ? 'CUBIERTA' : 'PENDIENTE',
            ];
        }

        return $rows;
    }

    private function formatPersona($nombres, $app, $apm, $ci, $celular): string
    {
        $fullName = trim(implode(' ', array_filter([
            trim((string) $nombres),
            trim((string) $app),
            trim((string) $apm),
        ])));
        if ($fullName === '') {
            $fullName = 'SIN NOMBRE';
        }

        $ciTxt = trim((string) $ci) !== '' ? trim((string) $ci) : '-';
        $celTxt = trim((string) $celular) !== '' ? trim((string) $celular) : '-';

        return $fullName . ' (CI: ' . $ciTxt . ', CEL: ' . $celTxt . ')';
    }

    private function filterMatrizRowsByStatus(array $rows, string $status): array
    {
        $detail = collect($rows)->filter(function ($row) {
            return mb_strtoupper((string) ($row['distrito'] ?? '')) !== 'TOTAL';
        });

        if ($status === 'faltantes') {
            $detail = $detail->filter(function ($row) {
                return (int) ($row['cob_total'] ?? 0) < 0;
            })->values();
        } elseif ($status === 'completos') {
            $detail = $detail->filter(function ($row) {
                return (int) ($row['req_total'] ?? 0) > 0 && (int) ($row['cob_total'] ?? 0) === 0;
            })->values();
        }

        $data = $detail->values()->all();
        if (empty($data)) {
            return [];
        }

        $totRegRec = 0;
        $totRegMes = 0;
        $totReqRec = 0;
        $totReqMes = 0;

        foreach ($data as $i => &$row) {
            $row['nro'] = $i + 1;
            $totRegRec += (int) ($row['reg_recintos'] ?? 0);
            $totRegMes += (int) ($row['reg_mesas'] ?? 0);
            $totReqRec += (int) ($row['req_recintos'] ?? 0);
            $totReqMes += (int) ($row['req_mesas'] ?? 0);
        }
        unset($row);

        $totReqTotal = $totReqRec + $totReqMes;
        $totRegTotal = $totRegRec + $totRegMes;
        $totCobTotal = $totRegTotal - $totReqTotal;
        $totPct = $totReqTotal > 0 ? round(($totRegTotal / $totReqTotal) * 100, 2) : 0;

        $data[] = [
            'nro' => '',
            'distrito' => 'TOTAL',
            'reg_recintos' => $totRegRec,
            'reg_mesas' => $totRegMes,
            'req_recintos' => $totReqRec,
            'dif_recintos' => $totRegRec - $totReqRec,
            'req_mesas' => $totReqMes,
            'dif_mesas' => $totRegMes - $totReqMes,
            'req_total' => $totReqTotal,
            'cob_total' => $totCobTotal,
            'porcentaje' => number_format($totPct, 2, ',', '.') . '%',
        ];

        return $data;
    }
}
