<?php

namespace App\Http\Controllers;

use App\Models\ElectoralPrecinct;
use App\Models\Miembro;
use App\Models\Province;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ElectoralPrecinctController extends Controller
{
       public function porMunicipio($municipality_id)
            {
                $recintos = ElectoralPrecinct::where('municipality_id', $municipality_id)->get();

                return response()->json($recintos);
            }
}
