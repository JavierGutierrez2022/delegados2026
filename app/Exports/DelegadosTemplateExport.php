<?php

namespace App\Exports;

use App\Exports\Sheets\DelegadosCatalogosSheet;
use App\Exports\Sheets\DelegadosPlantillaSheet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class DelegadosTemplateExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new DelegadosPlantillaSheet(),
            new DelegadosCatalogosSheet(),
        ];
    }
}

