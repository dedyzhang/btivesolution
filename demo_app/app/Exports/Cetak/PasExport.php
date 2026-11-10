<?php

namespace App\Exports\Cetak;

use App\Models\Kelas;
use App\Models\Ngajar;
use App\Models\NilaiPas;
use App\Models\Semester;
use App\Models\Siswa;
use App\Support\CetakExcelStyle;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

/** Matriks nilai PAS: baris siswa × kolom mapel, satu kelas per sheet. */
class PasExport implements FromArray, WithTitle, WithEvents
{
    private $ngajars;
    private $siswas;
    private array $nilai = [];

    public function __construct(private string $idKelas)
    {
        $sem = Semester::aktif() ?? Semester::first();

        $this->siswas = Siswa::where('id_kelas', $idKelas)->orderBy('nama')->get();
        $this->ngajars = Ngajar::with(['pelajaran'])->where('id_kelas', $idKelas)->whereNotNull('id_pelajaran')->get()
            ->sortBy(fn ($n) => [$n->pelajaran?->urutan, $n->pelajaran?->nama])->values();

        foreach ($this->ngajars as $ng) {
            $map = NilaiPas::where('id_ngajar', $ng->uuid)->where('id_semester', $sem?->id)->pluck('nilai', 'id_siswa')->toArray();
            foreach ($this->siswas as $s) {
                if (isset($map[$s->uuid]) && $map[$s->uuid] !== null && $map[$s->uuid] !== '') {
                    $this->nilai[$s->uuid][$ng->uuid] = (int) $map[$s->uuid];
                }
            }
        }
    }

    public function array(): array
    {
        $header = ['No', 'Nama Siswa'];
        foreach ($this->ngajars as $ng) { $header[] = $ng->pelajaran?->nama; }
        $header[] = 'Rata-rata';

        $rows = [$header];
        foreach ($this->siswas as $i => $s) {
            $row = [$i + 1, $s->nama];
            $sum = 0; $n = 0;
            foreach ($this->ngajars as $ng) {
                $v = $this->nilai[$s->uuid][$ng->uuid] ?? null;
                $row[] = $v;
                if ($v !== null) { $sum += $v; $n++; }
            }
            $row[] = $n > 0 ? (int) round($sum / $n) : null;
            $rows[] = $row;
        }
        return $rows;
    }

    public function title(): string
    {
        $k = Kelas::find($this->idKelas);
        return 'Nilai PAS ' . ($k ? "{$k->tingkat}{$k->kelas}" : '');
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $k = Kelas::find($this->idKelas);
                $judul = 'NILAI PAS — KELAS ' . ($k ? "{$k->tingkat}{$k->kelas}" : '-');
                CetakExcelStyle::kopDanTabel($event->sheet->getDelegate(), $judul, 2 + $this->ngajars->count() + 1, $this->siswas->count());
            },
        ];
    }
}
