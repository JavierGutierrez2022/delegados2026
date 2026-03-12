<?php

namespace App\Http\Controllers;

use App\Models\Table;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class TableController extends Controller
{
                public function porRecinto($precinct_id)
            {
                $mesas = Table::where('electoral_precinct_id', $precinct_id)
                    ->where('state', 'ACTIVO')
                    ->orderByRaw('CAST(table_number AS UNSIGNED)')
                    ->get();

                return response()->json($mesas);
            }

}
