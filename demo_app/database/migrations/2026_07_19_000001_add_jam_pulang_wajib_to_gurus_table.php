<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Jam pulang wajib per guru (opsional). Kosong = bebas, boleh absen pulang kapan
 * saja (perilaku lama). Diisi = guru itu hanya boleh scan wajah absen pulang
 * pada jam tsb atau setelahnya — dipakai admin utk jadwal pulang bergilir/piket.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gurus', function (Blueprint $table) {
            $table->time('jam_pulang_wajib')->nullable()->after('tmt_smp');
        });
    }

    public function down(): void
    {
        Schema::table('gurus', function (Blueprint $table) {
            $table->dropColumn('jam_pulang_wajib');
        });
    }
};
