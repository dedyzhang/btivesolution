<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

/**
 * Gabung toggle lama fitur_jagat_misi_aktif ke fitur_arena_belajar_aktif (OR).
 * Arena ON jika salah satu row yang ada bernilai ON.
 */
return new class extends Migration
{
    public function up(): void
    {
        $arenaKey = 'fitur_arena_belajar_aktif';
        $legacyKey = 'fitur_jagat_misi_aktif';

        $arena = Setting::where('key', $arenaKey)->first();
        $legacy = Setting::where('key', $legacyKey)->first();

        if ($arena === null && $legacy === null) {
            return;
        }

        $arenaOn = $arena ? $arena->value === '1' : false;
        $legacyOn = $legacy ? $legacy->value === '1' : false;

        Setting::set($arenaKey, ($arenaOn || $legacyOn) ? '1' : '0');
    }

    public function down(): void
    {
        // Merge satu arah — tidak mengembalikan toggle terpisah.
    }
};
