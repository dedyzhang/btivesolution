<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
| Riwayat hasil generate Asisten Guru. Berbeda dari ai_usage_logs yang hanya
| audit token/status, tabel ini dipakai guru sebagai pengingat hasil terakhir.
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_teacher_histories', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('user_uuid');
            $table->string('type', 40);
            $table->string('type_label', 80);
            $table->string('title', 180);
            $table->string('excerpt', 500)->nullable();
            $table->json('metadata')->nullable();
            $table->mediumText('answer');
            $table->timestamps();

            $table->foreign('user_uuid')
                ->references('uuid')->on('users')
                ->cascadeOnDelete();

            $table->index(['user_uuid', 'created_at']);
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_teacher_histories');
    }
};
