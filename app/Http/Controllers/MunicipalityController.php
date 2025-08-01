<?php

namespace App\Http\Controllers;


use App\Models\Municipality;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;


class MunicipalityController extends Controller
{
  
        public function getByProvince($id)
            {
                $municipalities = Municipality::where('province_id', $id)->orderBy('name')->get();

                return response()->json($municipalities);
            }

}
