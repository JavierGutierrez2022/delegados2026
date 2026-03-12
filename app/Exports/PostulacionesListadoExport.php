<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PostulacionesListadoExport implements FromArray, WithHeadings, ShouldAutoSize
{
    public function __construct(private array $rows)
    {
    }

    public function headings(): array
    {
        return [
            'NRO',
            'NOMBRES',
            'APELLIDO_PATERNO',
            'APELLIDO_MATERNO',
            'GENERO',
            'CI',
            'NACIMIENTO',
            'CELULAR',
            'PROVINCIA',
            'MUNICIPIO',
            'RECINTO',
            'OBSERVACIONES',
        ];
    }

    public function array(): array
    {
        return $this->rows;
    }
}
