<?php

namespace App\Exports\Cetak\Sheets;

use App\Models\Ngajar;
use App\Models\NilaiPenjabaran;
use App\Models\PenjabaranKomponen;
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

/** Satu sheet = satu mapel (ngajar): grid siswa × komponen penjabaran. */
class PenjabaranSheetExport implements FromArray, WithTitle, WithEvents
{
    private $komponen;
    private $siswas;
    private array $skor = [];

    public function __construct(private Ngajar $ngajar, private ?int $idSemester)
    {
        $this->komponen = PenjabaranKomponen::where('id_pelajaran', $ngajar->id_pelajaran)->orderBy('urutan')->get();
        $this->siswas = Siswa::where('id_kelas', $ngajar->id_kelas)->orderBy('nama')->get();

        foreach (NilaiPenjabaran::whereIn('id_komponen', $this->komponen->pluck('uuid'))->where('id_semester', $idSemester)->get() as $r) {
            $this->skor[$r->id_komponen][$r->id_siswa] = $r->nilai;
        }
    }

    public function array(): array
    {
        $judul = ['NILAI PENJABARAN — ' . ($this->ngajar->pelajaran?->nama ?? '-') . ' — Kelas ' . $this->ngajar->kelas?->tingkat . $this->ngajar->kelas?->kelas];
        $header = ['No', 'Nama Siswa'];
        foreach ($this->komponen as $k) { $header[] = $k->nama; }

        $rows = [$judul, [], $header];
        foreach ($this->siswas as $i => $s) {
            $row = [$i + 1, $s->nama];
            foreach ($this->komponen as $k) {
                $row[] = $this->skor[$k->uuid][$s->uuid] ?? null;
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
                $lastCol = Coordinate::stringFromColumnIndex(max(2 + $this->komponen->count(), 2));

                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->getStyle('A1')->applyFromArray(['font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => '1E293B']]]);

                $sheet->getStyle("A3:{$lastCol}3")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F46E5']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '4338CA']]],
                ]);
                $sheet->freezePane('C4');

                foreach ($sheet->getColumnIterator('C', $lastCol) as $colIter) {
                    $sheet->getColumnDimension($colIter->getColumnIndex())->setWidth(16);
                }
                $sheet->getColumnDimension('A')->setWidth(5);
                $sheet->getColumnDimension('B')->setWidth(24);
            },
        ];
    }
}
