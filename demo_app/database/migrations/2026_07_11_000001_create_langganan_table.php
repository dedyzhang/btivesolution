<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('langganan', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            // Tier paket (dasar/pro/enterprise) — opsional, informatif saja.
            $table->string('paket', 30)->nullable();
            // Hanya 3 / 6 / 12 — divalidasi di controller (in:3,6,12).
            $table->unsignedTinyInteger('durasi_bulan');
            $table->date('mulai_pada');
            // = mulai_pada + durasi_bulan (kalender nyata via addMonths, bukan 30×bulan).
            $table->date('berakhir_pada')->index();
            $table->string('status', 20)->default('aktif');
            $table->text('catatan')->nullable();
            $table->uuid('diatur_oleh')->nullable();
            $table->timestamps();

            $table->foreign('diatur_oleh')
                ->references('uuid')->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('langganan');
    }
};
