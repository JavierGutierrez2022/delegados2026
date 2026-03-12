<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // 1) Borra el índice anterior (ajusta el nombre si en tu BD es otro)
        try {
            Schema::table('assignments', function (Blueprint $table) {
                $table->dropUnique('uniq_scope_recinto_role');
                // Si no recuerdas el nombre exacto, puedes usar:
                // $table->dropUnique(['scope','electoral_precinct_id','role']);
            });
        } catch (\Throwable $e) {
            // si ya no existe, seguimos
        }

        // 2) Normaliza datos existentes: table_id = 0 para scope=RECINTO (evitar NULL)
        DB::statement("UPDATE assignments SET table_id = 0 WHERE scope='RECINTO' AND (table_id IS NULL OR table_id=0)");

        // 3) Crea el índice nuevo
        Schema::table('assignments', function (Blueprint $table) {
            $table->unique(
                ['scope','electoral_precinct_id','table_id','role'],
                'uniq_scope_precinct_table_role'
            );
        });
    }

    public function down(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            $table->dropUnique('uniq_scope_precinct_table_role');
            $table->unique(['scope','electoral_precinct_id','role'], 'uniq_scope_recinto_role');
        });
    }
};
