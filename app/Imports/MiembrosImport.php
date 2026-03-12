<?php

namespace App\Imports;

use App\Models\Miembro;
use Maatwebsite\Excel\Concerns\ToModel;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithHeadingRow;


class MiembrosImport implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        

         if (empty($row['id']) || empty($row['nombres'])) {
        return null;
    }

    return new Miembro([
        /* 'id' => $row['id'], */
        'nombres' => $row['nombres'],
        'app' => $row['app'],
        'apm' => $row['apm'],
        'genero' => $row['genero'],
        'ci' => $row['ci'],
        'fecnac' => $this->convertirFechaExcel($row['fecnac']),
        'celular' => $row['celular'],
        'recintovot' => $row['recintovot'],
        'mesavot' => $row['mesavot'],
        'agrupa' => $row['agrupa'],
        'obs' => $row['obs'],
        'estado' => $row['estado'],
        'delegado' => $row['delegado'],
        'created_at' => now(),
        'updated_at' => now(),
        'province_id' => $row['province_id'],
        'municipality_id' => $row['municipality_id'],
        'electoral_precinct_id' => $row['electoral_precinct_id'],
        'table_id' => $row['table_id'],
    ]);

    }
     private function convertirFechaExcel($valor)
    {
        $valor = trim($valor);

        if (empty($valor) || in_array($valor, ['N/A', '--'])) {
            return null;
        }

        if (is_numeric($valor)) {
            try {
                return Carbon::createFromTimestamp(((int)$valor - 25569) * 86400);
            } catch (\Exception $e) {
                return null;
            }
        }

        try {
            return Carbon::createFromFormat('d/m/Y', $valor);
        } catch (\Exception $e) {
            return null;
        }
    }



}
