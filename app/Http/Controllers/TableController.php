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
                $mesas = Table::where('electoral_precinct_id', $precinct_id)->get();

                return response()->json($mesas);
            }

}
