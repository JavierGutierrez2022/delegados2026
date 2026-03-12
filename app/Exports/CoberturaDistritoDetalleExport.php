<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CoberturaDistritoDetalleExport implements FromArray, WithHeadings, ShouldAutoSize
{
    private array $rows;

    public function __construct(array $rows)
    {
        $this->rows = $rows;
    }

    public function headings(): array
    {
        return [
            'DISTRITO',
            'PROVINCIA',
            'MUNICIPIO',
            'RECINTO',
            'MESA',
            'DELEGADO_PROPIETARIO',
            'DATOS_DELEGADO_PROPIETARIO',
            'DELEGADO_SUPLENTE',
            'DATOS_DELEGADO_SUPLENTE',
            'JEFE_RECINTO',
            'DATOS_JEFE_RECINTO',
            'ESTADO',
        ];
    }

    public function array(): array
    {
        return $this->rows;
    }
}
