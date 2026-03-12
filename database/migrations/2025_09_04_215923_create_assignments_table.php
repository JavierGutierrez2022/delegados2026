<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('miembro_id');
            $table->enum('scope', ['RECINTO','MESA']); // tipo de asignación
            $table->unsignedBigInteger('electoral_precinct_id')->nullable(); // cuando scope = RECINTO o MESA
            $table->unsignedBigInteger('table_id')->nullable();              // cuando scope = MESA
            $table->string('role', 40); // JEFE_DE_RECINTO | MONITOR_RADAR | DELEGADO_PROPIETARIO | DELEGADO_SUPLENTE

            $table->timestamps();

            $table->foreign('miembro_id')->references('id')->on('miembros')->cascadeOnDelete();
            $table->foreign('electoral_precinct_id')->references('id')->on('electoral_precincts')->nullOnDelete();
            $table->foreign('table_id')->references('id')->on('tables')->nullOnDelete();

            // Evita duplicar un rol en el mismo recinto/mesa (N.B.: NULLs no chocan en MySQL)
            $table->unique(['scope','electoral_precinct_id','role'], 'uniq_scope_recinto_role');
            $table->unique(['scope','table_id','role'], 'uniq_scope_mesa_role');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assignments');
    }
};