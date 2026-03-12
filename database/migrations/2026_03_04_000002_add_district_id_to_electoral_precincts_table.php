<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('electoral_precincts')) {
            return;
        }

        if (!Schema::hasColumn('electoral_precincts', 'district_id')) {
            Schema::table('electoral_precincts', function (Blueprint $table) {
                $table->unsignedBigInteger('district_id')->nullable()->after('municipality_id');
                $table->index('district_id', 'electoral_precincts_district_id_idx');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('electoral_precincts')) {
            return;
        }

        if (Schema::hasColumn('electoral_precincts', 'district_id')) {
            Schema::table('electoral_precincts', function (Blueprint $table) {
                $table->dropIndex('electoral_precincts_district_id_idx');
                $table->dropColumn('district_id');
            });
        }
    }
};

