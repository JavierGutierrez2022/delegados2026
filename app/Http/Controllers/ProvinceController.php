<?php

namespace App\Http\Controllers;

use App\Models\Municipality;
use App\Models\Province;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ProvinceController extends Controller
{
 public function getMunicipalitiesByProvince($provinceId)
{
    $municipalities = Municipality::where('province_id', $provinceId)->get();

    return response()->json($municipalities);
}
}
