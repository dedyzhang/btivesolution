<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('gemini_api_key')->nullable()->after('gemini_account');
            $table->string('gemini_api_key_hint', 8)->nullable()->after('gemini_api_key');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['gemini_api_key', 'gemini_api_key_hint']);
        });
    }
};
