<?php

namespace App\Exports\Sheets;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class DistrictsCatalogoTarijaSheet implements FromArray, ShouldAutoSize, WithTitle
{
    public function title(): string
    {
        return 'Catalogo Tarija';
    }

    public function array(): array
    {
        $rows = [];
        $rows[] = ['RECINTOS DEL MUNICIPIO TARIJA'];
        $rows[] = ['municipio', 'recinto', 'state_recinto', 'distrito_actual'];

        $query = DB::table('electoral_precincts as ep')
            ->join('municipalities as mu', 'mu.id', '=', 'ep.municipality_id')
            ->whereRaw('LOWER(TRIM(mu.name)) = ?', ['tarija'])
            ->select('mu.name as municipio', 'ep.name as recinto', 'ep.state');

        if (Schema::hasTable('districts') && Schema::hasColumn('electoral_precincts', 'district_id')) {
            $query->leftJoin('districts as d', 'd.id', '=', 'ep.district_id');
            $query->addSelect(DB::raw('COALESCE(d.name, ep.distric_number, "") as distrito_actual'));
        } elseif (Schema::hasColumn('electoral_precincts', 'distric_number')) {
            $query->addSelect(DB::raw('COALESCE(ep.distric_number, "") as distrito_actual'));
        } else {
            $query->addSelect(DB::raw('"" as distrito_actual'));
        }

        $items = $query
            ->orderBy('ep.name')
            ->get();

        foreach ($items as $i) {
            $rows[] = [
                $i->municipio,
                $i->recinto,
                $i->state,
                $i->distrito_actual,
            ];
        }

        return $rows;
    }
}

