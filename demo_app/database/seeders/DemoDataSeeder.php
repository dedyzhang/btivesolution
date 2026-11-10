<?php

namespace Database\Seeders;

use App\Models\Classroom;
use App\Models\Kelas;
use App\Models\Pelajaran;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('access', 'admin')->first() ?? User::first();
        if (!$admin) {
            $this->command->error('No user found');
            return;
        }

        $pelajarans = [
            'Pendidikan Agama Islam', 'Pendidikan Pancasila dan Kewarganegaraan', 
            'Bahasa Indonesia', 'Matematika', 'Ilmu Pengetahuan Alam', 
            'Ilmu Pengetahuan Sosial', 'Bahasa Inggris', 'Pendidikan Jasmani', 
            'Seni Budaya', 'Prakarya', 'Informatika'
        ];

        foreach ($pelajarans as $i => $nama) {
            Pelajaran::firstOrCreate(
                ['nama' => $nama],
                [
                    'uuid' => Str::uuid()->toString(),
                    'kode' => strtoupper(substr(str_replace(' ', '', $nama), 0, 3)),
                    'urutan' => $i + 1,
                    'jp' => 2,
                    'kkm' => 75
                ]
            );
        }

        $tingkats = [7, 8, 9];
        $hurufs = ['A', 'B', 'C', 'D'];

        foreach ($tingkats as $t) {
            foreach ($hurufs as $h) {
                Kelas::firstOrCreate(
                    ['tingkat' => $t, 'kelas' => $h],
                    ['uuid' => Str::uuid()->toString()]
                );
            }
        }

        $allPelajaran = Pelajaran::all();
        $allKelas = Kelas::all();

        $colors = ['#00a99d', '#e85d75', '#12345b', '#f5a524', '#5ba85b', '#6b21a8'];
        $c = 0;

        foreach ($allPelajaran as $pel) {
            foreach ($allKelas as $kel) {
                $title = $pel->nama . ' — Kelas ' . $kel->tingkat . $kel->kelas;
                
                $exists = Classroom::where('id_pelajaran', $pel->uuid)
                                   ->where('id_kelas', $kel->uuid)
                                   ->exists();

                if (!$exists) {
                    Classroom::create([
                        'uuid' => Str::uuid()->toString(),
                        'id_pelajaran' => $pel->uuid,
                        'id_kelas' => $kel->uuid,
                        'created_by' => $admin->uuid,
                        'title' => $title,
                        'description' => 'Ruang kelas untuk ' . $title,
                        'cover_color' => $colors[$c % count($colors)],
                        'status' => 'published',
                        'published_at' => now(),
                        'class_code' => strtoupper(Str::random(6)),
                    ]);
                    $c++;
                }
            }
        }

        $this->command->info("Seeded $c new classrooms!");
    }
}
