<?php

namespace App\Exports;

use App\Support\CetakExcelStyle;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

/**
 * Kredensial login (username+password PLAINTEXT) guru yang baru
 * dibuat lewat satu batch Import Guru. Hanya bisa dibuat SEKALI tepat setelah
 * import — setelah itu password sudah ter-hash & tidak bisa diambil lagi.
 */
class GuruImportKredensialExport implements FromArray, WithColumnWidths, WithTitle, WithEvents
{
    private const HEADINGS = ['No', 'Nama', 'NIK', 'NIP', 'Username Guru', 'Password Guru'];

    public function __construct(private array $kredensial)
    {
    }

    public function array(): array
    {
        $rows = [self::HEADINGS];
        foreach ($this->kredensial as $i => $k) {
            $rows[] = [
                $i + 1,
                $k['nama'],
                $k['nik'],
                $k['nip'],
                $k['username_guru'],
                $k['password_guru'],
            ];
        }
        return $rows;
    }

    public function title(): string
    {
        return 'Kredensial Guru Baru';
    }

    public function columnWidths(): array
    {
        return ['A' => 5, 'B' => 26, 'C' => 20, 'D' => 20, 'E' => 24, 'F' => 16];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                CetakExcelStyle::kopDanTabel(
                    $event->sheet->getDelegate(),
                    'KREDENSIAL LOGIN GURU BARU (IMPORT)',
                    count(self::HEADINGS),
                    count($this->kredensial),
                    'Simpan file ini dengan aman — password tidak bisa ditampilkan ulang setelah ini'
                );
            },
        ];
    }
}
