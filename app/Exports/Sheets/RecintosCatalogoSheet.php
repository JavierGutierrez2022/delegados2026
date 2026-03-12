<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Illuminate\Support\Facades\DB;

class RecintosCatalogoSheet implements FromArray, ShouldAutoSize, WithTitle
{
    public function title(): string
    {
        return 'Catalogos';
    }

    public function array(): array
    {
        $rows = [];
        $rows[] = ['RECINTOS VIGENTES'];
        $rows[] = ['provincia', 'municipio', 'recinto', 'cantidad_mesas_actual', 'state_actual'];

        $items = DB::table('electoral_precincts as ep')
            ->leftJoin('municipalities as mu', 'mu.id', '=', 'ep.municipality_id')
            ->leftJoin('provinces as p', 'p.id', '=', 'mu.province_id')
            ->leftJoin('tables as t', 't.electoral_precinct_id', '=', 'ep.id')
            ->selectRaw('p.name as provincia, mu.name as municipio, ep.name as recinto, COUNT(t.id) as cantidad_mesas, ep.state')
            ->groupBy('p.name', 'mu.name', 'ep.name', 'ep.state')
            ->orderBy('p.name')
            ->orderBy('mu.name')
            ->orderBy('ep.name')
            ->get();

        foreach ($items as $i) {
            $rows[] = [
                $i->provincia,
                $i->municipio,
                $i->recinto,
                (int) $i->cantidad_mesas,
                $i->state,
            ];
        }

        return $rows;
    }
}
