<?php

namespace App\Exports\Cetak;

use App\Models\Guru;
use App\Models\PresensiGuru;
use App\Models\Setting;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class AbsensiGuruExport implements FromView, WithEvents
{
    private $period;
    private $gurus;
    private $presensiData;
    private $waktuTerlambat;

    public function __construct(private string $dari, private string $sampai)
    {
        $this->period = CarbonPeriod::create($this->dari, $this->sampai);
        $this->gurus = Guru::orderBy('nama')->get();
        $this->presensiData = PresensiGuru::whereBetween('tanggal', [$this->dari, $this->sampai])
            ->get()
            ->groupBy(function ($p) {
                return $p->id_guru . '_' . $p->tanggal->format('Y-m-d');
            });
        $this->waktuTerlambat = Setting::get('waktu_terlambat', '07:30');
    }

    public function view(): View
    {
        return view('cetak.absensi-guru.excel', [
            'period' => $this->period,
            'gurus' => $this->gurus,
            'presensiData' => $this->presensiData,
            'waktuTerlambat' => $this->waktuTerlambat,
        ]);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // Calculate dimensions
                $days = count($this->period);
                $totalCols = 2 + ($days * 2); // No, Nama + (Datang, Pulang) * days
                $lastCol = Coordinate::stringFromColumnIndex($totalCols);
                $headerRowStart = 4;
                $headerRowEnd = 5;
                $lastRow = $headerRowEnd + $this->gurus->count();

                // Title and Subtitle
                $namaSekolah = Setting::get('nama_sekolah', 'Sekolah');
                $subjudul = 'Periode ' . Carbon::parse($this->dari)->format('d-m-Y') . ' s/d ' . Carbon::parse($this->sampai)->format('d-m-Y') . '  •  ' . $namaSekolah . '  •  Diekspor: ' . now()->isoFormat('D MMMM Y, HH:mm') . ' WIB';

                $sheet->setCellValue('A1', 'REKAP ABSENSI GURU');
                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->setCellValue('A2', $subjudul);
                $sheet->mergeCells("A2:{$lastCol}2");

                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '1E293B']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => ['italic' => true, 'size' => 10, 'color' => ['rgb' => '64748B']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(24);
                $sheet->getRowDimension(2)->setRowHeight(18);

                // Merge No and Nama headers
                $sheet->mergeCells('A4:A5');
                $sheet->mergeCells('B4:B5');

                // Set column widths
                $sheet->getColumnDimension('A')->setWidth(5);
                $sheet->getColumnDimension('B')->setWidth(26);
                for ($i = 3; $i <= $totalCols; $i++) {
                    $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($i))->setWidth(10);
                }

                // Style Headers
                $sheet->getStyle("A{$headerRowStart}:{$lastCol}{$headerRowEnd}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F46E5']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '4338CA']]],
                ]);
                $sheet->getRowDimension(4)->setRowHeight(22);
                $sheet->getRowDimension(5)->setRowHeight(22);

                $sheet->freezePane('C6');

                // Style Data Rows
                if ($this->gurus->count() > 0) {
                    for ($row = 6; $row <= $lastRow; $row++) {
                        $isEven = ($row - 5) % 2 === 0;
                        if ($isEven) {
                            $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
                                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F8FAFC']],
                            ]);
                        }
                    }
                    $sheet->getStyle("A6:{$lastCol}{$lastRow}")->applyFromArray([
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E2E8F0']]],
                        'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                    ]);
                    $sheet->getStyle("A6:A{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("C6:{$lastCol}{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }
            },
        ];
    }
}
