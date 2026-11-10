<?php

namespace App\Exports\Cetak;

use App\Models\Guru;
use App\Support\CetakExcelStyle;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

class GuruExport implements FromCollection, WithHeadings, WithMapping, WithColumnWidths, WithTitle, WithEvents
{
    public const HEADINGS = [
        'No', 'Nama', 'NIK', 'NIP', 'JK', 'Tempat Lahir', 'Tanggal Lahir', 'Agama', 'Alamat', 'No Telp',
        'Tingkat Studi', 'Program Studi', 'Universitas', 'Tahun Tamat', 'TMT Mengajar', 'TMT di Sekolah Ini',
    ];

    public function collection()
    {
        return Guru::orderBy('nama')->get();
    }

    public function headings(): array
    {
        return self::HEADINGS;
    }

    public function title(): string
    {
        return 'Data Guru';
    }

    public function map($g): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $g->nama,
            $g->nik,
            $g->nip,
            $g->jk,
            $g->tempat_lahir,
            $g->tanggal_lahir,
            $g->agama,
            $g->alamat,
            $g->no_telp,
            $g->tingkat_studi,
            $g->program_studi,
            $g->universitas,
            $g->tahun_tamat,
            $g->tmt_ngajar,
            $g->tmt_smp,
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5, 'B' => 26, 'C' => 18, 'D' => 18, 'E' => 6, 'F' => 16, 'G' => 14, 'H' => 10, 'I' => 28, 'J' => 15,
            'K' => 14, 'L' => 22, 'M' => 22, 'N' => 12, 'O' => 14, 'P' => 14,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                CetakExcelStyle::kopDanTabel($event->sheet->getDelegate(), 'DATA GURU', count(self::HEADINGS), $this->collection()->count());
            },
        ];
    }
}
