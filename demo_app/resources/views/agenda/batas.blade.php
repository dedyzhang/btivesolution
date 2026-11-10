@extends('layouts.app')
@section('title', 'Buku Batas')

@section('content')
<div class="space-y-5">
    <div>
        <h1 class="page-title">Buku Batas</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Lihat materi/batas pelajaran yang sudah diisi guru per kelas, per hari — sesuai jadwal mengajar.</p>
    </div>

    <form method="GET" action="{{ route('agenda.batas') }}" class="card p-4 flex flex-wrap gap-3 items-end">
        <div class="flex-1 min-w-40">
            <label class="form-label">Kelas</label>
            <select name="kelas" class="form-select" onchange="this.form.submit()">
                @foreach($kelasList as $k)
                <option value="{{ $k->uuid }}" @selected($idKelas===$k->uuid)>Kelas {{ $k->tingkat }}{{ $k->kelas }}</option>
                @endforeach
            </select>
        </div>
        <div class="min-w-40">
            <label class="form-label">Dari Tanggal</label>
            <input type="date" name="dari" value="{{ $dari }}" class="form-input" onchange="this.form.submit()">
        </div>
        <div class="min-w-40">
            <label class="form-label">Sampai Tanggal</label>
            <input type="date" name="sampai" value="{{ $sampai }}" class="form-input" onchange="this.form.submit()">
        </div>
        <button type="submit" class="px-4 py-2.5 rounded-xl text-sm font-medium border border-slate-200 dark:border-slate-600 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 transition">Tampilkan</button>
        @if($kelas && !empty($hari))
        <a href="{{ route('agenda.batas.excel', ['kelas' => $idKelas, 'dari' => $dari, 'sampai' => $sampai]) }}"
           class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-bold bg-emerald-600 text-white hover:bg-emerald-700 transition">
            <i data-lucide="file-spreadsheet" class="w-4 h-4"></i> Unduh Excel
        </a>
        @endif
    </form>

    @if(empty($hari))
    <div class="card p-12 text-center text-slate-400">
        <i data-lucide="notebook-pen" class="w-12 h-12 mx-auto mb-3 opacity-30"></i>
        <p class="font-medium">Tidak ada jadwal mengajar pada rentang tanggal ini{{ $kelas ? ' untuk kelas ' . $kelas->tingkat . $kelas->kelas : '' }}.</p>
    </div>
    @else
    <div class="space-y-6">
        @foreach($hari as $h)
        <div class="card overflow-hidden">
            <div class="px-4 py-3 border-b border-slate-100 dark:border-slate-700 text-sm font-bold text-slate-700 dark:text-slate-200">
                {{ ucfirst($h['label']) }} &bull; Kelas {{ $kelas->tingkat }}{{ $kelas->kelas }}
            </div>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th class="text-center w-10">No</th>
                            <th class="text-center w-24">Waktu</th>
                            <th class="text-left">Pelajaran</th>
                            <th class="text-left">Guru</th>
                            <th class="text-left">Pokok Pembahasan</th>
                            <th class="text-left w-32">Metode</th>
                            <th class="text-center w-16">S/B</th>
                            <th class="text-center w-16">Absen</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($h['slots'] as $i => $s)
                        @php $a = $s['agenda']; @endphp
                        <tr>
                            <td class="text-center text-slate-400">{{ $i + 1 }}</td>
                            <td class="text-center text-slate-500 whitespace-nowrap">{{ $s['jam_mulai'] }}–{{ $s['jam_selesai'] }}</td>
                            <td class="font-semibold text-slate-700 dark:text-slate-200">{{ $s['pelajaran'] }}</td>
                            <td class="text-slate-600 dark:text-slate-300">{{ $s['guru'] }}</td>
                            <td class="text-slate-600 dark:text-slate-300">{{ $a?->pembahasan ?: '-' }}</td>
                            <td class="text-slate-500">{{ $a?->metode ?: '-' }}</td>
                            <td class="text-center">
                                @if($a)
                                <span class="badge {{ $a->proses==='selesai' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-300' : 'bg-amber-50 text-amber-700 dark:bg-amber-950/30 dark:text-amber-300' }}">{{ $a->proses==='selesai' ? 'S' : 'B' }}</span>
                                @else
                                <span class="text-slate-300">-</span>
                                @endif
                            </td>
                            <td class="text-center text-slate-500">{{ $a ? $a->absensi->count() : '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>
@endsection
