<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Seed lengkap Arena Belajar: kuis DEMO + semua permainan JagatMISI (misi).
 * Jalankan: php artisan db:seed --class=ArenaBelajarSeeder
 */
class ArenaBelajarSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(ArenaBelajarDemoSeeder::class);
        $this->call(SyncJagatMisiToArenaSeeder::class);
    }
}
