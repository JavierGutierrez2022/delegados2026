<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class RecintosPlantillaSheet implements FromArray, WithHeadings, ShouldAutoSize, WithTitle
{
    public function title(): string
    {
        return 'Plantilla';
    }

    public function headings(): array
    {
        return [
            'provincia',
            'municipio',
            'recinto',
            'cantidad_mesas',
            'state',
        ];
    }

    public function array(): array
    {
        return [
            [
                'Tarija',
                'Cercado',
                'U.E. Belgrano',
                12,
                'ACTIVO',
            ],
            [
                'Tarija',
                'Cercado',
                'U.E. Liceo Tarija',
                10,
                'INACTIVO',
            ],
        ];
    }
}
