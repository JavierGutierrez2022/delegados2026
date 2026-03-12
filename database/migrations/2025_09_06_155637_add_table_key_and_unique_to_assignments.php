<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            // Asegura que table_id acepte NULL para RECINTO
            $table->unsignedBigInteger('table_id')->nullable()->change();
        });

        // Elimina índices únicos viejos si existen (ajusta el nombre si era otro)
        try { Schema::table('assignments', fn (Blueprint $t) => $t->dropUnique('uniq_scope_recinto_role')); } catch (\Throwable $e) {}
        try { Schema::table('assignments', fn (Blueprint $t) => $t->dropUnique('uniq_scope_precinct_table_role')); } catch (\Throwable $e) {}
        try { Schema::table('assignments', fn (Blueprint $t) => $t->dropUnique('uniq_scope_precinct_tablekey_role')); } catch (\Throwable $e) {}

        Schema::table('assignments', function (Blueprint $table) {
            // Columna auxiliar para la unicidad
            $table->unsignedBigInteger('table_key')->default(0)->after('table_id');
        });

        // Inicializa table_key = IFNULL(table_id, 0)
        DB::statement('UPDATE assignments SET table_key = IFNULL(table_id, 0)');

        // Índice único final
        Schema::table('assignments', function (Blueprint $table) {
            $table->unique(
                ['scope','electoral_precinct_id','table_key','role'],
                'uniq_scope_precinct_tablekey_role'
            );
        });
    }

    public function down(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            $table->dropUnique('uniq_scope_precinct_tablekey_role');
            $table->dropColumn('table_key');
        });

        // Si quieres, aquí podrías restaurar tu índice anterior
        // Schema::table('assignments', function (Blueprint $table) {
        //     $table->unique(['scope','electoral_precinct_id','role'], 'uniq_scope_recinto_role');
        // });
    }
};