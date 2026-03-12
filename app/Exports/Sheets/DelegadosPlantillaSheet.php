<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class DelegadosPlantillaSheet implements FromArray, WithHeadings, ShouldAutoSize, WithTitle
{
    public function title(): string
    {
        return 'Plantilla';
    }

    public function headings(): array
    {
        return [
            'ci',
            'nombres',
            'app',
            'apm',
            'genero',
            'fecnac',
            'celular',
            'correo_electronico',
            'obs',
            'province_id',
            'municipality_id',
            'electoral_precinct_id',
            'provincia',
            'municipio',
            'recinto',
            'table_id',
            'mesa_numero',
            'assignment_scope',
            'assignment_role',
            'estado',
            'agrupa',
            'delegado',
        ];
    }

    public function array(): array
    {
        return [
            [
                '1234567',
                'JUAN CARLOS',
                'PEREZ',
                'GOMEZ',
                'MASCULINO',
                '1990-05-30',
                '77777777',
                'juan.perez@correo.com',
                'Sin observaciones',
                '',
                '',
                '',
                'Cercado',
                'Tarija',
                'Recinto Central',
                '',
                '12',
                'MESA',
                'DELEGADO_PROPIETARIO',
                'ACTIVO',
                '',
                '',
            ],
            [
                '8910111',
                'MARIA ELENA',
                'ROJAS',
                'LOPEZ',
                'FEMENINO',
                '1992-11-15',
                '71112223',
                'maria.rojas@correo.com',
                '',
                '',
                '',
                '',
                'Arce',
                'Padcaya',
                'Recinto 12 de Octubre',
                '',
                '',
                'RECINTO',
                'JEFE_DE_RECINTO',
                'ACTIVO',
                '',
                '',
            ],
        ];
    }
}
