<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Masa manfaat (tahun) aset → dasar perhitungan penyusutan garis lurus
 * & Nilai Buku (nilai perolehan dikurangi akumulasi penyusutan).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sarpras_aset', function (Blueprint $table) {
            $table->unsignedSmallInteger('masa_manfaat_tahun')->default(4)->after('nilai_perolehan');
        });
    }

    public function down(): void
    {
        Schema::table('sarpras_aset', function (Blueprint $table) {
            $table->dropColumn('masa_manfaat_tahun');
        });
    }
};
