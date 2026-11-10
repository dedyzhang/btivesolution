<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_preferences', function (Blueprint $table) {
            // Urutan blok dashboard hasil drag & drop, mis. ["stats","ringkasan","sarpras","recent"]
            $table->json('dashboard_layout')->nullable()->after('dashboard_widgets');
        });
    }

    public function down(): void
    {
        Schema::table('user_preferences', function (Blueprint $table) {
            $table->dropColumn('dashboard_layout');
        });
    }
};
