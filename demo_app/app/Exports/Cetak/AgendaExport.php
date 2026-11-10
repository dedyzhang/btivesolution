<?php

namespace App\Exports\Cetak;

use App\Models\Agenda;
use App\Support\CetakExcelStyle;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

class AgendaExport implements FromCollection, WithHeadings, WithMapping, WithColumnWidths, WithTitle, WithEvents
{
    public const HEADINGS = ['No', 'Tanggal', 'Guru', 'Kelas', 'Mata Pelajaran', 'Pembahasan', 'Metode', 'Kegiatan', 'Kendala', 'Siswa Tidak Hadir', 'Validasi'];

    public function __construct(private string $dari, private string $sampai, private ?string $idGuru = null)
    {
    }

    public function collection()
    {
        return Agenda::with(['guru', 'kelas', 'pelajaran', 'absensi.siswa'])
            ->whereBetween('tanggal', [$this->dari, $this->sampai])
            ->when($this->idGuru, fn ($q) => $q->where('id_guru', $this->idGuru))
            ->orderBy('tanggal')
            ->get();
    }

    public function headings(): array
    {
        return self::HEADINGS;
    }

    public function title(): string
    {
        return 'Data Agenda';
    }

    public function map($a): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $a->tanggal?->format('d-m-Y'),
            $a->guru?->nama ?? '-',
            $a->kelas ? "{$a->kelas->tingkat}{$a->kelas->kelas}" : '-',
            $a->pelajaran?->nama ?? '-',
            $a->pembahasan,
            $a->metode,
            $a->kegiatan,
            $a->kendala,
            $this->siswaTidakHadir($a),
            $a->validasi === 'valid' ? 'Sudah Divalidasi' : 'Belum Divalidasi',
        ];
    }

    /** "Nama (Sakit), Nama (Izin), ..." dari relasi absensi agenda ini. */
    private function siswaTidakHadir(Agenda $a): string
    {
        if ($a->absensi->isEmpty()) return '-';

        return $a->absensi->map(function ($ab) {
            $nama = $ab->siswa?->nama ?? '-';
            $jenis = Agenda::ABSENSI[$ab->absensi] ?? $ab->absensi;
            return "{$nama} ({$jenis})";
        })->implode(', ');
    }

    public function columnWidths(): array
    {
        return ['A' => 5, 'B' => 14, 'C' => 24, 'D' => 8, 'E' => 20, 'F' => 30, 'G' => 16, 'H' => 30, 'I' => 24, 'J' => 34, 'K' => 16];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sub = 'Periode ' . \Carbon\Carbon::parse($this->dari)->format('d-m-Y') . ' s/d ' . \Carbon\Carbon::parse($this->sampai)->format('d-m-Y');
                CetakExcelStyle::kopDanTabel($event->sheet->getDelegate(), 'DATA AGENDA', count(self::HEADINGS), $this->collection()->count(), $sub);
            },
        ];
    }
}
