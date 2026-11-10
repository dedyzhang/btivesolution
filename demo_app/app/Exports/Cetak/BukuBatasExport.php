<?php

namespace App\Exports\Cetak;

use App\Models\Kelas;
use App\Models\Setting;
use App\Support\BukuBatas;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

/** Buku Batas satu kelas dalam satu rentang tanggal — satu sheet, dikelompokkan per hari. */
class BukuBatasExport implements FromArray, WithTitle, WithEvents
{
    private const HEADER = ['No', 'Waktu', 'Pelajaran', 'Guru', 'Pokok Pembahasan', 'Metode', 'S/B', 'Absen'];
    private const LAST_COL = 'H';

    private ?Kelas $kelas;
    private array $hari;
    private array $dayHeaderRows = [];
    private array $tableHeaderRows = [];

    public function __construct(private string $idKelas, private string $dari, private string $sampai)
    {
        $this->kelas = Kelas::find($idKelas);
        $this->hari = BukuBatas::build($idKelas, $dari, $sampai);
    }

    public function array(): array
    {
        $namaSekolah = Setting::get('nama_sekolah', 'Sekolah');
        $judul = 'BUKU BATAS — KELAS ' . ($this->kelas ? "{$this->kelas->tingkat}{$this->kelas->kelas}" : '-');
        $sub = 'Periode ' . Carbon::parse($this->dari)->format('d-m-Y') . ' s/d ' . Carbon::parse($this->sampai)->format('d-m-Y')
            . '  •  ' . $namaSekolah . '  •  Diekspor: ' . now()->isoFormat('D MMMM Y, HH:mm') . ' WIB';

        $kosong = array_fill(0, count(self::HEADER), null);
        $rows = [[$judul], [$sub], $kosong];

        foreach ($this->hari as $h) {
            $rows[] = [ucfirst($h['label'])];
            $this->dayHeaderRows[] = count($rows);

            $rows[] = self::HEADER;
            $this->tableHeaderRows[] = count($rows);

            foreach ($h['slots'] as $i => $s) {
                $a = $s['agenda'];
                $rows[] = [
                    $i + 1,
                    $s['jam_mulai'] . '–' . $s['jam_selesai'],
                    $s['pelajaran'],
                    $s['guru'],
                    $a?->pembahasan ?: '-',
                    $a?->metode ?: '-',
                    $a ? ($a->proses === 'selesai' ? 'S' : 'B') : '-',
                    $a ? $a->absensi->count() : '-',
                ];
            }
            $rows[] = $kosong;
        }

        if (empty($this->hari)) {
            $rows[] = ['Tidak ada jadwal mengajar pada rentang tanggal ini.'];
        }

        return $rows;
    }

    public function title(): string
    {
        return 'Buku Batas';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastCol = self::LAST_COL;

                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->mergeCells("A2:{$lastCol}2");
                $sheet->getStyle('A1')->applyFromArray(['font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '1E293B']]]);
                $sheet->getStyle('A2')->applyFromArray(['font' => ['italic' => true, 'size' => 10, 'color' => ['rgb' => '64748B']]]);

                foreach ($this->dayHeaderRows as $r) {
                    $sheet->mergeCells("A{$r}:{$lastCol}{$r}");
                    $sheet->getStyle("A{$r}")->applyFromArray([
                        'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => '1E293B']],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E2E8F0']],
                    ]);
                }

                foreach ($this->tableHeaderRows as $r) {
                    $sheet->getStyle("A{$r}:{$lastCol}{$r}")->applyFromArray([
                        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F46E5']],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '4338CA']]],
                    ]);
                }

                foreach (['A' => 5, 'B' => 14, 'C' => 22, 'D' => 24, 'E' => 36, 'F' => 18, 'G' => 7, 'H' => 9] as $col => $w) {
                    $sheet->getColumnDimension($col)->setWidth($w);
                }
            },
        ];
    }
}
