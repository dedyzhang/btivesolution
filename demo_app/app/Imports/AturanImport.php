<?php

namespace App\Imports;

use App\Models\Aturan;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;

/** Baca baris data (mulai baris 5 — setelah kop 3 baris + 1 baris header) hasil AturanExport. */
class AturanImport implements ToCollection, WithStartRow
{
    public int $created = 0;
    public int $updated = 0;
    public int $skipped = 0;
    public array $errors = [];

    public function startRow(): int
    {
        return 5;
    }

    public function collection(Collection $rows)
    {
        $baris = $this->startRow() - 1;
        foreach ($rows as $row) {
            $baris++;

            $kode = trim((string) ($row[1] ?? ''));
            if ($kode === '' && trim((string) ($row[3] ?? '')) === '') {
                $this->skipped++; // baris kosong
                continue;
            }
            if ($kode === '') {
                $this->errors[] = "Baris {$baris}: kolom Kode wajib diisi.";
                continue;
            }

            $jenisLabel = strtolower(trim((string) ($row[2] ?? '')));
            $jenis = match (true) {
                str_starts_with($jenisLabel, 'tambah') => 'tambah',
                str_starts_with($jenisLabel, 'kurang') => 'kurang',
                default => null,
            };
            if ($jenis === null) {
                $this->errors[] = "Baris {$baris}: kolom Jenis harus \"Tambah\" atau \"Kurang\".";
                continue;
            }

            $aturanTeks = trim((string) ($row[3] ?? ''));
            if ($aturanTeks === '') {
                $this->errors[] = "Baris {$baris}: kolom Aturan wajib diisi.";
                continue;
            }

            $poinVal = $row[4] ?? null;
            if (!is_numeric($poinVal) || (int) $poinVal < 0) {
                $this->errors[] = "Baris {$baris}: kolom Poin harus berupa angka 0 atau lebih.";
                continue;
            }

            $data = ['kode' => $kode, 'jenis' => $jenis, 'aturan' => $aturanTeks, 'poin' => (int) $poinVal];
            $existing = Aturan::where('kode', $kode)->first();
            if ($existing) {
                $existing->update($data);
                $this->updated++;
            } else {
                Aturan::create($data);
                $this->created++;
            }
        }
    }
}
