<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            try {
                $table->dropUnique('uniq_scope_member');
            } catch (\Throwable $e) {
                // El índice puede no existir en algunos entornos.
            }

            $table->index(['scope', 'miembro_id'], 'idx_scope_member');
        });
    }

    public function down(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            try {
                $table->dropIndex('idx_scope_member');
            } catch (\Throwable $e) {
                // El índice puede no existir en algunos entornos.
            }

            $table->unique(['scope', 'miembro_id'], 'uniq_scope_member');
        });
    }
};
