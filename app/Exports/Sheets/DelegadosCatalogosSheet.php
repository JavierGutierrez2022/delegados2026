<?php

namespace App\Exports\Sheets;

use App\Models\ElectoralPrecinct;
use App\Models\Municipality;
use App\Models\Province;
use App\Models\Table;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class DelegadosCatalogosSheet implements FromArray, ShouldAutoSize, WithTitle
{
    public function title(): string
    {
        return 'Catalogos';
    }

    public function array(): array
    {
        $rows = [];

        $rows[] = ['PROVINCIAS'];
        $rows[] = ['province_id', 'provincia'];
        foreach (Province::orderBy('name')->get(['id', 'name']) as $p) {
            $rows[] = [$p->id, $p->name];
        }

        $rows[] = [];
        $rows[] = ['MUNICIPIOS'];
        $rows[] = ['municipality_id', 'municipio', 'province_id', 'provincia'];
        foreach (Municipality::query()
            ->leftJoin('provinces as p', 'p.id', '=', 'municipalities.province_id')
            ->orderBy('p.name')
            ->orderBy('municipalities.name')
            ->get([
                'municipalities.id as id',
                'municipalities.name as name',
                'municipalities.province_id as province_id',
                'p.name as province_name',
            ]) as $m) {
            $rows[] = [$m->id, $m->name, $m->province_id, $m->province_name];
        }

        $rows[] = [];
        $rows[] = ['RECINTOS'];
        $rows[] = ['electoral_precinct_id', 'recinto', 'municipality_id', 'municipio', 'province_id', 'provincia'];
        foreach (ElectoralPrecinct::query()
            ->leftJoin('municipalities as mu', 'mu.id', '=', 'electoral_precincts.municipality_id')
            ->leftJoin('provinces as p', 'p.id', '=', 'mu.province_id')
            ->orderBy('p.name')
            ->orderBy('mu.name')
            ->orderBy('electoral_precincts.name')
            ->get([
                'electoral_precincts.id as id',
                'electoral_precincts.name as name',
                'electoral_precincts.municipality_id as municipality_id',
                'mu.name as municipality_name',
                'mu.province_id as province_id',
                'p.name as province_name',
            ]) as $r) {
            $rows[] = [$r->id, $r->name, $r->municipality_id, $r->municipality_name, $r->province_id, $r->province_name];
        }

        $rows[] = [];
        $rows[] = ['MESAS'];
        $rows[] = ['table_id', 'mesa_numero', 'electoral_precinct_id', 'recinto', 'municipality_id', 'municipio', 'province_id', 'provincia'];
        foreach (Table::query()
            ->leftJoin('electoral_precincts as ep', 'ep.id', '=', 'tables.electoral_precinct_id')
            ->leftJoin('municipalities as mu', 'mu.id', '=', 'ep.municipality_id')
            ->leftJoin('provinces as p', 'p.id', '=', 'mu.province_id')
            ->orderBy('p.name')
            ->orderBy('mu.name')
            ->orderBy('ep.name')
            ->orderBy('tables.table_number')
            ->get([
                'tables.id as table_id',
                'tables.table_number as table_number',
                'ep.id as precinct_id',
                'ep.name as precinct_name',
                'mu.id as municipality_id',
                'mu.name as municipality_name',
                'p.id as province_id',
                'p.name as province_name',
            ]) as $t) {
            $rows[] = [
                $t->table_id,
                $t->table_number,
                $t->precinct_id,
                $t->precinct_name,
                $t->municipality_id,
                $t->municipality_name,
                $t->province_id,
                $t->province_name,
            ];
        }

        $rows[] = [];
        $rows[] = ['ASIGNACIONES_VALIDAS'];
        $rows[] = ['assignment_scope', 'assignment_role'];
        $rows[] = ['RECINTO', 'JEFE_DE_RECINTO'];
        $rows[] = ['RECINTO', 'MONITOR_RADAR'];
        $rows[] = ['MESA', 'DELEGADO_PROPIETARIO'];
        $rows[] = ['MESA', 'DELEGADO_SUPLENTE'];

        return $rows;
    }
}
