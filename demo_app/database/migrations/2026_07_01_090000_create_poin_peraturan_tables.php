<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Sistem lama: Poin/Aturan (ledger deduksi basis 100) ──

        // Master aturan (jenis pelanggaran/penghargaan + nilai poinnya)
        Schema::create('aturan', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->string('kode')->unique();
            $table->string('jenis');       // tambah | kurang
            $table->text('aturan');        // deskripsi aturan
            $table->integer('poin')->default(0);
            $table->timestamps();
        });

        // Ledger resmi poin siswa (sudah disetujui / diinput langsung admin-kesiswaan)
        Schema::create('poin', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->date('tanggal');
            $table->string('id_siswa', 36);
            $table->string('id_aturan', 36);
            $table->timestamps();
            $table->index('id_siswa');
        });

        // Pengajuan poin (guru/walikelas/sekretaris) menunggu approval admin-kesiswaan
        Schema::create('poin_temp', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->date('tanggal');
            $table->string('id_aturan', 36);
            $table->string('id_siswa', 36);
            $table->string('penginput');           // guru | sekretaris
            $table->string('id_input', 36);        // uuid guru/siswa pengaju
            $table->string('status')->default('belum'); // belum | approve | disapprove
            $table->timestamps();
            $table->index(['status', 'id_siswa']);
        });

        // ── Sistem baru: P3 (Pelanggaran, Prestasi, Partisipasi) — akumulatif per semester ──

        // Master kategori P3 (preset poin per kategori)
        Schema::create('p3_kategori', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->string('jenis');       // pelanggaran | prestasi | partisipasi
            $table->string('deskripsi');
            $table->integer('poin')->default(0);
            $table->timestamps();
        });

        // Log resmi P3 siswa
        Schema::create('p3_poin', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->date('tanggal');
            $table->string('id_siswa', 36);
            $table->string('jenis');       // pelanggaran | prestasi | partisipasi
            $table->text('deskripsi');
            $table->integer('poin')->default(0);
            $table->unsignedBigInteger('id_semester')->nullable();
            $table->timestamps();
            $table->index(['id_siswa', 'id_semester']);
        });

        // Pengajuan P3 menunggu approval admin-kesiswaan
        Schema::create('p3_temp', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->string('id_siswa', 36);
            $table->string('yang_mengajukan');     // guru | sekretaris
            $table->string('id_pengajuan', 36);    // uuid guru/siswa pengaju
            $table->date('tanggal');
            $table->string('jenis');
            $table->text('deskripsi');
            $table->string('status')->default('belum');
            $table->unsignedBigInteger('id_semester')->nullable();
            $table->integer('poin')->default(0);
            $table->timestamps();
            $table->index(['status', 'id_siswa']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('p3_temp');
        Schema::dropIfExists('p3_poin');
        Schema::dropIfExists('p3_kategori');
        Schema::dropIfExists('poin_temp');
        Schema::dropIfExists('poin');
        Schema::dropIfExists('aturan');
    }
};
