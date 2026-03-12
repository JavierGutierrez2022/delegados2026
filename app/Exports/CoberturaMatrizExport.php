<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CoberturaMatrizExport implements FromArray, WithHeadings, ShouldAutoSize
{
    private array $rows;

    public function __construct(array $rows)
    {
        $this->rows = $rows;
    }

    public function headings(): array
    {
        return [
            'NRO',
            'GRUPO',
            'REG_RECINTOS',
            'REG_MESAS',
            'REQ_RECINTOS',
            'DIF_RECINTOS',
            'REQ_MESAS',
            'DIF_MESAS',
            'REQ_TOTAL',
            'COB_TOTAL',
            'PORCENTAJE',
        ];
    }

    public function array(): array
    {
        return $this->rows;
    }
}
