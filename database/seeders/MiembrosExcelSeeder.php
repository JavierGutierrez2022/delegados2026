<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\MiembrosImport;

class MiembrosExcelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
   
    public function run()
    {
        $ruta = storage_path('app/miembros_nuevo.xlsx'); // Asegúrate de que el archivo esté aquí
        Excel::import(new MiembrosImport, $ruta);
        
    }
    

}
