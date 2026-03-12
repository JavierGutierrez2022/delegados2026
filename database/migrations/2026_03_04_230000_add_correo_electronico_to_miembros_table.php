<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('miembros')) {
            return;
        }

        if (!Schema::hasColumn('miembros', 'correo_electronico')) {
            Schema::table('miembros', function (Blueprint $table) {
                $table->string('correo_electronico', 150)->nullable()->after('celular');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('miembros')) {
            return;
        }

        if (Schema::hasColumn('miembros', 'correo_electronico')) {
            Schema::table('miembros', function (Blueprint $table) {
                $table->dropColumn('correo_electronico');
            });
        }
    }
};

