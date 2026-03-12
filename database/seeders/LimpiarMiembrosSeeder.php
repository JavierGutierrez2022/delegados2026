<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Miembro;
use Illuminate\Support\Facades\DB;

class LimpiarMiembrosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       // Desactivar claves foráneas
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Limpiar tabla pivote primero
        DB::table('miembro_table')->truncate();

        // Luego limpiar tabla miembros
        Miembro::truncate();

        // Activar nuevamente claves foráneas
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
