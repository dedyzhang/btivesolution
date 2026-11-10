<?php

namespace App\Exports\Cetak\Sheets;

use App\Models\Materi;
use App\Models\Ngajar;
use App\Models\NilaiFormatif;
use App\Models\Siswa;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

/** Satu sheet = satu mapel (ngajar): grid siswa × TP, dikelompokkan per materi. */
class FormatifSheetExport implements FromArray, WithTitle, WithEvents
{
    private $materi;
    private $siswas;
    private array $skor = [];

    public function __construct(private Ngajar $ngajar, private ?int $idSemester)
    {
        $this->materi = Materi::with('tujuan')->where('id_ngajar', $ngajar->uuid)
            ->where('id_semester', $idSemester)->orderBy('urutan')->get();
        $this->siswas = Siswa::where('id_kelas', $ngajar->id_kelas)->orderBy('nama')->get();

        $tupeIds = $this->materi->flatMap(fn ($m) => $m->tujuan->pluck('uuid'))->all();
        foreach (NilaiFormatif::whereIn('id_tupe', $tupeIds)->get() as $r) {
            $this->skor[$r->id_tupe][$r->id_siswa] = $r->nilai;
        }
    }

    public function array(): array
    {
        $judul = ['NILAI FORMATIF — ' . ($this->ngajar->pelajaran?->nama ?? '-') . ' — Kelas ' . $this->ngajar->kelas?->tingkat . $this->ngajar->kelas?->kelas];
        $baris1 = ['', ''];
        $baris2 = ['No', 'Nama Siswa'];
        foreach ($this->materi as $m) {
            foreach ($m->tujuan as $i => $t) {
                $baris1[] = $i === 0 ? $m->nama : '';
                $baris2[] = 'TP' . ($i + 1);
            }
            $baris1[] = '';
            $baris2[] = 'RT';
        }

        $rows = [$judul, [], $baris1, $baris2];
        foreach ($this->siswas as $i => $s) {
            $row = [$i + 1, $s->nama];
            foreach ($this->materi as $m) {
                $sum = 0;
                $n = 0;
                foreach ($m->tujuan as $t) {
                    $v = $this->skor[$t->uuid][$s->uuid] ?? null;
                    $row[] = $v;
                    if ($v !== null) { $sum += $v; $n++; }
                }
                $row[] = $n > 0 ? (int) round($sum / $n) : null;
            }
            $rows[] = $row;
        }

        if ($this->siswas->isEmpty()) {
            $rows[] = ['Belum ada siswa di kelas ini.'];
        }

        return $rows;
    }

    public function title(): string
    {
        return Str::limit($this->ngajar->pelajaran?->nama ?? 'Mapel', 28, '');
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $col = 3; // kolom C = TP pertama
                foreach ($this->materi as $m) {
                    $span = $m->tujuan->count() + 1;
                    if ($span > 1) {
                        $startCol = Coordinate::stringFromColumnIndex($col);
                        $endCol = Coordinate::stringFromColumnIndex($col + $span - 1);
                        $sheet->mergeCells("{$startCol}3:{$endCol}3");
                    }
                    $col += $span;
                }
                $lastCol = Coordinate::stringFromColumnIndex(max($col - 1, 2));

                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => '1E293B']],
                ]);

                $sheet->getStyle("A3:{$lastCol}4")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F46E5']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '4338CA']]],
                ]);
                $sheet->freezePane('C5');

                foreach ($sheet->getColumnIterator('C', $lastCol) as $colIter) {
                    $sheet->getColumnDimension($colIter->getColumnIndex())->setWidth(9);
                }
                $sheet->getColumnDimension('A')->setWidth(5);
                $sheet->getColumnDimension('B')->setWidth(24);
            },
        ];
    }
}
