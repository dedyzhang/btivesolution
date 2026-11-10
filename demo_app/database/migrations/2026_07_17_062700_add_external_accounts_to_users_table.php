<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('gemini_account', 191)->nullable()->after('username_customized');
            $table->string('canva_belajar_id', 191)->nullable()->after('gemini_account');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['gemini_account', 'canva_belajar_id']);
        });
    }
};
