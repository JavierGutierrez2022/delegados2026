<?php

namespace App\Exports;

use App\Exports\Sheets\DistrictsCatalogoTarijaSheet;
use App\Exports\Sheets\DistrictsPlantillaSheet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class DistrictsByRecintoTemplateExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new DistrictsPlantillaSheet(),
            new DistrictsCatalogoTarijaSheet(),
        ];
    }
}

