<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Field tambahan ruangan untuk fitur "Ruangan & Booking":
 * - gedung/lantai : lokasi (mis. "Gedung A" / "Lantai 1")
 * - fasilitas      : daftar fasilitas (JSON, mis. ["Proyektor","AC"])
 * - status         : tersedia | digunakan | maintenance
 * (kapasitas & deskripsi sudah ada sebelumnya.)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sarpras_denah_ruangan', function (Blueprint $table) {
            $table->string('gedung', 80)->nullable()->after('nama');
            $table->string('lantai', 40)->nullable()->after('gedung');
            $table->json('fasilitas')->nullable()->after('kapasitas');
            $table->string('status', 20)->default('tersedia')->after('fasilitas');
        });
    }

    public function down(): void
    {
        Schema::table('sarpras_denah_ruangan', function (Blueprint $table) {
            $table->dropColumn(['gedung', 'lantai', 'fasilitas', 'status']);
        });
    }
};
