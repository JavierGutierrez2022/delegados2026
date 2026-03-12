<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            // Evita que un miembro se asigne más de una vez en el mismo scope (RECINTO o MESA)
            $table->unique(['scope','miembro_id'], 'uniq_scope_member');
        });
    }
    public function down(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            $table->dropUnique('uniq_scope_member');
        });
    }
};