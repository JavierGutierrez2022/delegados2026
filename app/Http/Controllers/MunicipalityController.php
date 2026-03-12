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
                $municipalities = Municipality::where('province_id', $id)
                    ->where('state', 'ACTIVO')
                    ->orderBy('name')
                    ->get();

                return response()->json($municipalities);
            }

        public function getDistrictsByMunicipality($id)
        {
            $municipio = Municipality::find($id);
            if (!$municipio) {
                return response()->json([]);
            }

            if (mb_strtolower(trim((string) $municipio->name)) !== 'tarija') {
                return response()->json([]);
            }

            $districts = DB::table('districts')
                ->where('municipality_id', $id)
                ->whereRaw("UPPER(COALESCE(state, '')) = 'ACTIVO'")
                ->orderBy('name')
                ->get(['id', 'name']);

            $normal = [];
            $ruralIds = [];

            foreach ($districts as $d) {
                $n = $this->districtNumber((string) $d->name);
                if ($n !== null && $n >= 14 && $n <= 25) {
                    $ruralIds[] = (int) $d->id;
                    continue;
                }

                $normal[] = (object) [
                    'id' => (string) $d->id,
                    'name' => (string) $d->name,
                ];
            }

            usort($normal, function ($a, $b) {
                return strcmp((string) $a->name, (string) $b->name);
            });

            $result = collect($normal);
            if (!empty($ruralIds)) {
                sort($ruralIds);
                $result->push((object) [
                    'id' => implode(',', $ruralIds),
                    'name' => 'RURAL',
                ]);
            }

            return response()->json($result->values());
        }

        private function districtNumber(string $name): ?int
        {
            if (preg_match('/\d+/', $name, $m) === 1) {
                return (int) $m[0];
            }

            return null;
        }

}
