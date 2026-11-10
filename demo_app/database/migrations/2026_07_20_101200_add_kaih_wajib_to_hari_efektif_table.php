<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hari_efektif', function (Blueprint $table) {
            $table->boolean('kaih_wajib')->default(false)->after('agenda_guru');
        });
    }

    public function down(): void
    {
        Schema::table('hari_efektif', function (Blueprint $table) {
            $table->dropColumn('kaih_wajib');
        });
    }
};
