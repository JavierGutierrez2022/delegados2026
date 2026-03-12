<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Asegura que table_id sea nullable (por RECINTO)
        Schema::table('assignments', function (Blueprint $table) {
            $table->unsignedBigInteger('table_id')->nullable()->change();
        });

        // Borra índices únicos anteriores si existieran
        try { Schema::table('assignments', fn (Blueprint $t) => $t->dropUnique('uniq_scope_recinto_role')); } catch (\Throwable $e) {}
        try { Schema::table('assignments', fn (Blueprint $t) => $t->dropUnique('uniq_scope_precinct_table_role')); } catch (\Throwable $e) {}

        // Columna generada (0 si table_id es NULL)
        // MariaDB/MySQL 8+ soportan columnas generadas STORED
        DB::statement("
            ALTER TABLE assignments
            ADD COLUMN table_key BIGINT
            GENERATED ALWAYS AS (IFNULL(table_id, 0)) STORED
        ");

        // Índice único final: (scope, precinct, table_key, role)
        Schema::table('assignments', function (Blueprint $table) {
            $table->unique(
                ['scope','electoral_precinct_id','table_key','role'],
                'uniq_scope_precinct_tablekey_role'
            );
        });

        // Normaliza datos viejos por si guardaste 0
        DB::statement("UPDATE assignments SET table_id = NULL WHERE scope='RECINTO' AND table_id = 0");
    }

    public function down(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            $table->dropUnique('uniq_scope_precinct_tablekey_role');
        });
        DB::statement("ALTER TABLE assignments DROP COLUMN table_key");
        // (si quieres, re-crea tu índice viejo aquí)
    }
};
