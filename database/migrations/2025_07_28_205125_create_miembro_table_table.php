<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
        {
            Schema::create('miembro_table', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('miembro_id');
                $table->unsignedBigInteger('table_id');
                $table->timestamps();

                $table->foreign('miembro_id')->references('id')->on('miembros')->onDelete('cascade');
                $table->foreign('table_id')->references('id')->on('tables')->onDelete('cascade');
            });
        }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('miembro_table');
    }
};
