<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
| Workspace Presentasi Canva di SIMS. Guru menyusun outline/catatan di sini,
| lalu mengerjakan desain di Canva (belajar.id) dan menempelkan link desain.
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('canva_presentations', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('user_uuid');
            $table->string('title', 200);
            $table->string('subject', 120)->nullable();
            $table->string('status', 20)->default('draft'); // draft|in_progress|done
            $table->mediumText('outline')->nullable();
            $table->text('notes')->nullable();
            $table->string('canva_url', 500)->nullable();
            $table->timestamp('last_opened_at')->nullable();
            $table->timestamps();

            $table->foreign('user_uuid')
                ->references('uuid')->on('users')
                ->cascadeOnDelete();

            $table->index(['user_uuid', 'updated_at']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('canva_presentations');
    }
};
