<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('districts')) {
            return;
        }

        Schema::create('districts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('municipality_id');
            $table->string('name', 120);
            $table->string('state', 20)->default('ACTIVO');
            $table->timestamps();

            $table->unique(['municipality_id', 'name'], 'districts_municipality_name_unique');
            $table->index(['municipality_id', 'state'], 'districts_municipality_state_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('districts');
    }
};

