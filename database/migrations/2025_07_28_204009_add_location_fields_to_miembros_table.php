<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('miembros', function (Blueprint $table) {
        $table->unsignedBigInteger('province_id')->nullable();
        $table->unsignedBigInteger('municipality_id')->nullable();
        $table->unsignedBigInteger('electoral_precinct_id')->nullable();
        $table->unsignedBigInteger('table_id')->nullable();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('miembros', function (Blueprint $table) {
            //
        });
    }
};
