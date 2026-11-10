<?php

namespace App\Exports\Cetak;

use App\Exports\Cetak\Sheets\PenjabaranSheetExport;
use App\Models\Ngajar;
use App\Models\Semester;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class PenjabaranExport implements WithMultipleSheets
{
    public function __construct(private string $idKelas)
    {
    }

    public function sheets(): array
    {
        $sem = Semester::aktif() ?? Semester::first();
        $ngajars = Ngajar::with(['pelajaran', 'kelas'])->where('id_kelas', $this->idKelas)
            ->whereNotNull('id_pelajaran')->whereNotNull('id_guru')->get()
            ->filter(fn ($n) => $n->pelajaran && $n->pelajaran->penjabaranKomponen()->exists())
            ->sortBy(fn ($n) => [$n->pelajaran?->urutan, $n->pelajaran?->nama]);

        $sheets = [];
        foreach ($ngajars as $n) {
            $sheets[] = new PenjabaranSheetExport($n, $sem?->id);
        }
        return $sheets;
    }
}
