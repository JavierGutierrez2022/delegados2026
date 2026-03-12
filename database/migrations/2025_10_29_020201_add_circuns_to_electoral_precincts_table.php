<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('electoral_precincts', function (Blueprint $table) {
            // si no existe ya
            if (!Schema::hasColumn('electoral_precincts', 'circuns')) {
                $table->string('circuns', 100)->nullable()->after('name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('electoral_precincts', function (Blueprint $table) {
            $table->dropColumn('circuns');
        });
    }
};
