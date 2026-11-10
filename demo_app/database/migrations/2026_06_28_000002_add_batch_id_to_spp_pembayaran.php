<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Menandai pembayaran yang diunggah bersamaan (bayar beberapa bulan sekaligus)
 * dengan satu `batch_id`, agar bendahara dapat memverifikasi sekali untuk
 * seluruh bulan dalam satu kali bayar.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('spp_pembayaran', function (Blueprint $table) {
            $table->uuid('batch_id')->nullable()->after('bulan')->index();
        });
    }

    public function down(): void
    {
        Schema::table('spp_pembayaran', function (Blueprint $table) {
            $table->dropColumn('batch_id');
        });
    }
};
