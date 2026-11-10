<?php

namespace App\Exports\Cetak;

use App\Models\Kelas;
use App\Support\CetakExcelStyle;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

class KelasExport implements FromCollection, WithHeadings, WithMapping, WithColumnWidths, WithTitle, WithEvents
{
    public const HEADINGS = ['No', 'Kelas', 'Wali Kelas', 'Jumlah Siswa', 'Laki-laki', 'Perempuan'];

    public function collection()
    {
        return Kelas::with(['guru', 'siswa'])->orderBy('tingkat')->orderBy('kelas')->get();
    }

    public function headings(): array
    {
        return self::HEADINGS;
    }

    public function title(): string
    {
        return 'Data Kelas';
    }

    public function map($k): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            "{$k->tingkat}{$k->kelas}",
            $k->guru?->nama ?? '-',
            $k->siswa->count(),
            $k->siswa->where('jk', 'L')->count(),
            $k->siswa->where('jk', 'P')->count(),
        ];
    }

    public function columnWidths(): array
    {
        return ['A' => 5, 'B' => 10, 'C' => 26, 'D' => 14, 'E' => 12, 'F' => 12];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                CetakExcelStyle::kopDanTabel($event->sheet->getDelegate(), 'DATA KELAS', count(self::HEADINGS), $this->collection()->count());
            },
        ];
    }
}
