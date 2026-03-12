<?php

namespace App\Http\Controllers;

use App\Models\ElectoralPrecinct;
use App\Models\Miembro;
use App\Models\Table;
use App\Models\Province;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ElectoralPrecinctController extends Controller
{
       public function porMunicipio($municipality_id)
            {
                $recintos = ElectoralPrecinct::where('municipality_id', $municipality_id)
                    ->where('state', 'ACTIVO')
                    ->get();

                return response()->json($recintos);
            }

            public function reporte()
                {
                    $recintos = ElectoralPrecinct::where('state', 'ACTIVO')
                        ->orderBy('distric_number')
                        ->get();

                    return view('admin.recintos.reporte', compact('recintos'));
                }

                public function tables()
    {
        return $this->hasMany(Table::class, 'electoral_precinct_id');
    }
    



}
