<?php

namespace App\Support;

use App\Models\Setting;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Kop judul + subjudul (nama sekolah + waktu ekspor) + header tabel bergaya +
 * zebra-striping baris data, dipakai semua export "Cetak Data" (menu admin)
 * supaya tampilan xlsx-nya seragam tanpa duplikasi kode styling di tiap class.
 */
class CetakExcelStyle
{
    public static function kopDanTabel(Worksheet $sheet, string $judul, int $jumlahKolom, int $jumlahBarisData, ?string $subjudulExtra = null): void
    {
        $lastCol = Coordinate::stringFromColumnIndex($jumlahKolom);

        $sheet->insertNewRowBefore(1, 3);
        $headerRow = 4;
        $lastRow = $headerRow + $jumlahBarisData;

        $namaSekolah = Setting::get('nama_sekolah', 'Sekolah');
        $subjudul = $namaSekolah . '  •  Diekspor: ' . now()->isoFormat('D MMMM Y, HH:mm') . ' WIB';
        if ($subjudulExtra) $subjudul = $subjudulExtra . '  •  ' . $subjudul;

        $sheet->setCellValue('A1', $judul);
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

        $sheet->getStyle("A{$headerRow}:{$lastCol}{$headerRow}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F46E5']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '4338CA']]],
        ]);
        $sheet->getRowDimension($headerRow)->setRowHeight(26);
        $sheet->freezePane('A' . ($headerRow + 1));
        if ($jumlahBarisData > 0) {
            $sheet->setAutoFilter("A{$headerRow}:{$lastCol}{$headerRow}");
        }

        for ($row = $headerRow + 1; $row <= $lastRow; $row++) {
            $isEven = ($row - $headerRow) % 2 === 0;
            $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E2E8F0']]],
                'fill' => $isEven
                    ? ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F8FAFC']]
                    : ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFFFF']],
            ]);
        }
    }
}
