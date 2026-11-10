<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel pembayaran SPP per siswa, per bulan, per tahun ajaran.
 *
 * - `bulan` = indeks 1..12 dengan 1 = Juli .. 12 = Juni (lihat App\Support\TahunAjaran).
 * - `status`: belum | menunggu (bukti diunggah, perlu verifikasi) | lunas | ditolak.
 * - Satu siswa hanya punya satu baris untuk kombinasi (tahun_ajaran, bulan).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spp_pembayaran', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('id_siswa');
            $table->string('tahun_ajaran', 9);          // mis. "2025/2026"
            $table->unsignedTinyInteger('bulan');        // 1..12 (1 = Juli)
            $table->unsignedBigInteger('nominal')->default(0);
            $table->string('status', 16)->default('belum');
            $table->string('bank')->nullable();          // bank/metode yang dipilih ortu
            $table->string('bukti_path')->nullable();    // bukti transfer (gambar)
            $table->date('tanggal_bayar')->nullable();   // tanggal ortu membayar/upload
            $table->date('jatuh_tempo')->nullable();     // tenggat (diatur bendahara)
            $table->text('catatan')->nullable();         // alasan tolak / catatan bendahara
            $table->uuid('diverifikasi_oleh')->nullable();
            $table->timestamp('diverifikasi_pada')->nullable();
            $table->timestamps();

            $table->unique(['id_siswa', 'tahun_ajaran', 'bulan'], 'spp_unik_siswa_bulan');
            $table->index(['tahun_ajaran', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spp_pembayaran');
    }
};
