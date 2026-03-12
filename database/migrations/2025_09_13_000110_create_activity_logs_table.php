<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('activity_logs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->string('action', 40); // CREATED, UPDATED, DELETED, LOGIN, LOGOUT, CUSTOM
            $t->string('model_type')->nullable(); // App\Models\Miembro
            $t->unsignedBigInteger('model_id')->nullable();
            $t->string('url', 1024)->nullable();
            $t->string('method', 10)->nullable();
            $t->string('ip', 64)->nullable();
            $t->text('user_agent')->nullable();
            $t->string('description')->nullable();

            $t->json('before')->nullable();
            $t->json('after')->nullable();

            $t->timestamps();

            $t->index(['action', 'model_type']);
            $t->index(['model_type', 'model_id']);
            $t->index('created_at');
        });
    }
    public function down(): void {
        Schema::dropIfExists('activity_logs');
    }
};
