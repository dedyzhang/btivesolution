<?php

namespace App\Exports;

use App\Models\Aturan;
use App\Models\Setting;
use App\Support\ExcelWatermark;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Protection;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;

class AturanExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle, WithEvents
{
    /** Tag watermark — tanda tangan HMAC ini yang diverifikasi ulang saat import. */
    public const WATERMARK_TAG = 'aturan-poin-export-v1';

    public const HEADINGS = ['No', 'Kode', 'Jenis', 'Aturan', 'Poin'];

    public function collection()
    {
        return Aturan::orderBy('kode')->get();
    }

    public function headings(): array
    {
        return self::HEADINGS;
    }

    public function title(): string
    {
        return 'Aturan Poin';
    }

    public function map($aturan): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $aturan->kode,
            Aturan::JENIS[$aturan->jenis] ?? $aturan->jenis,
            $aturan->aturan,
            $aturan->poin,
        ];
    }

    public function columnWidths(): array
    {
        return ['A' => 6, 'B' => 14, 'C' => 14, 'D' => 50, 'E' => 10];
    }

    public function styles(Worksheet $sheet)
    {
        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastCol = 'E';
                $lastRow = $sheet->getHighestRow(); // baris terakhir data (heading masih di baris 1 di sini)

                // ===== Sisipkan 3 baris kop di atas (judul, subjudul, spasi) =====
                $sheet->insertNewRowBefore(1, 3);
                $headerRow = 4; // heading (No/Kode/Jenis/Aturan/Poin) kini di baris 4
                $lastRow += 3;

                $namaSekolah = Setting::get('nama_sekolah', 'Sekolah');
                $sheet->setCellValue('A1', 'MASTER ATURAN POIN KEDISIPLINAN');
                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->setCellValue('A2', $namaSekolah . '  •  Diekspor: ' . now()->isoFormat('D MMMM Y, HH:mm') . ' WIB');
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

                // ===== Header tabel (baris 4) =====
                $sheet->getStyle("A{$headerRow}:{$lastCol}{$headerRow}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F46E5']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '4338CA']]],
                ]);
                $sheet->getRowDimension($headerRow)->setRowHeight(26);
                $sheet->freezePane('A' . ($headerRow + 1));
                $sheet->setAutoFilter("A{$headerRow}:{$lastCol}{$headerRow}");

                // ===== Border + zebra-striping untuk baris data =====
                for ($row = $headerRow + 1; $row <= $lastRow; $row++) {
                    $isEven = ($row - $headerRow) % 2 === 0;
                    $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E2E8F0']]],
                        'fill' => $isEven
                            ? ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F8FAFC']]
                            : ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFFFF']],
                    ]);
                    $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("E{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("D{$row}")->getAlignment()->setWrapText(true);
                }

                // ===== Dropdown validasi kolom Jenis (Tambah/Kurang) untuk baris tambahan =====
                for ($row = $headerRow + 1; $row <= $headerRow + 300; $row++) {
                    $validation = $sheet->getCell("C{$row}")->getDataValidation();
                    $validation->setType(DataValidation::TYPE_LIST);
                    $validation->setErrorStyle(DataValidation::STYLE_INFORMATION);
                    $validation->setAllowBlank(true);
                    $validation->setShowInputMessage(true);
                    $validation->setShowErrorMessage(true);
                    $validation->setShowDropDown(true);
                    $validation->setErrorTitle('Input salah');
                    $validation->setError('Pilih Tambah atau Kurang.');
                    $validation->setFormula1('"Tambah,Kurang"');
                }

                // ===== Proteksi: kunci judul & header tabel, kolom "No" tidak bisa diubah; sel data bebas diedit =====
                foreach ([1, 2, $headerRow] as $lockedRow) {
                    $sheet->getStyle("A{$lockedRow}:{$lastCol}{$lockedRow}")->getProtection()->setLocked(Protection::PROTECTION_PROTECTED);
                }
                $sheet->getStyle("A" . ($headerRow + 1) . ":A{$lastRow}")->getProtection()->setLocked(Protection::PROTECTION_PROTECTED);
                $sheet->getStyle("B" . ($headerRow + 1) . ":{$lastCol}" . ($headerRow + 300))->getProtection()->setLocked(Protection::PROTECTION_UNPROTECTED);
                $sheet->getProtection()->setSheet(true);

                // ===== Watermark tersembunyi (custom document property) — diverifikasi saat import =====
                $properties = $sheet->getParent()->getProperties();
                $properties->setCustomProperty('smpv6_wm_tag', AturanExport::WATERMARK_TAG, 's');
                $properties->setCustomProperty('smpv6_wm_sig', ExcelWatermark::sign(AturanExport::WATERMARK_TAG), 's');
            },
        ];
    }
}
