<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class DistrictsPlantillaSheet implements FromArray, WithHeadings, ShouldAutoSize, WithTitle
{
    public function title(): string
    {
        return 'Plantilla';
    }

    public function headings(): array
    {
        return [
            'distrito',
            'recinto',
        ];
    }

    public function array(): array
    {
        return [
            ['Distrito 1', 'U.E. Belgrano'],
            ['Distrito 2', 'U.E. Liceo Tarija'],
        ];
    }
}

