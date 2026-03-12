<?php

namespace App\Exports;

use App\Exports\Sheets\RecintosCatalogoSheet;
use App\Exports\Sheets\RecintosPlantillaSheet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class RecintosMesasTemplateExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new RecintosPlantillaSheet(),
            new RecintosCatalogoSheet(),
        ];
    }
}

