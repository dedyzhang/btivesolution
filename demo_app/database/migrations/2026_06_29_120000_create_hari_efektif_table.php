<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Kalender hari efektif: per tanggal, apakah siswa boleh absen & guru wajib isi agenda.
        Schema::create('hari_efektif', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->date('tanggal')->unique();
            $table->boolean('absen_siswa')->default(false);   // siswa boleh absen
            $table->boolean('agenda_guru')->default(false);   // guru wajib isi agenda
            $table->string('keterangan')->nullable();
            $table->unsignedTinyInteger('semester')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hari_efektif');
    }
};
