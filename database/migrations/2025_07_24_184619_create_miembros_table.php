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
        Schema::create('miembros', function (Blueprint $table) {
            $table->id();
            $table->string('nombres',200);
            $table->string('app',200);
            $table->string('apm',200);
            $table->string('genero',50);
            $table->string('ci',50);
            $table->string('fecnac',100);
            $table->string('celular',100);
            $table->string('recintovot',100);
            $table->string('agrupa',50);
            $table->string('obs',255);
            $table->string('estado',10);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('miembros');
    }
};
