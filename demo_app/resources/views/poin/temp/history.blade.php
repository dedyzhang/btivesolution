@extends('layouts.app')
@section('title', $status === 'approve' ? 'Riwayat Disetujui' : 'Riwayat Ditolak')

@section('content')
<div class="space-y-5">
    <a href="{{ route('poin.temp.index') }}" class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-primary"><i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali ke Pengajuan</a>
    <div>
        <h1 class="page-title">{{ $status === 'approve' ? 'Riwayat Disetujui' : 'Riwayat Ditolak' }}</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Pengajuan poin yang sudah diproses</p>
    </div>

    @if($items->isEmpty())
    <div class="card p-12 text-center text-slate-400">
        <i data-lucide="{{ $status === 'approve' ? 'check-check' : 'x' }}" class="w-12 h-12 mx-auto mb-3 opacity-30"></i>
        <p class="font-medium">Belum ada riwayat.</p>
    </div>
    @else
    <div class="card overflow-hidden">
        <div class="table-responsive">
            <table class="data-table w-full">
                <thead>
                    <tr>
                        <th class="w-10">#</th>
                        <x-sortable-th field="tanggal" label="Tanggal" />
                        <th>Siswa</th>
                        <th>Aturan</th>
                        <th class="text-right">Poin</th>
                        <th class="hide-mobile">Diajukan Oleh</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $i => $t)
                    <tr>
                        <td class="text-slate-400 text-xs">{{ $items->firstItem() + $i }}</td>
                        <td class="text-sm text-slate-500">{{ $t->tanggal->isoFormat('D MMM Y') }}</td>
                        <td class="font-semibold text-slate-800 dark:text-slate-200">{{ $t->siswa?->nama }}</td>
                        <td class="text-sm text-slate-600 dark:text-slate-300"><span class="font-mono text-xs text-slate-400">{{ $t->aturan?->kode }}</span> {{ $t->aturan?->aturan }}</td>
                        <td class="text-right font-semibold {{ $t->aturan?->jenis==='kurang' ? 'text-rose-600' : 'text-emerald-600' }}">{{ $t->aturan?->jenis==='kurang' ? '-' : '+' }}{{ $t->aturan?->poin }}</td>
                        <td class="hide-mobile text-xs text-slate-500">{{ ucfirst($t->penginput) }} &bull; {{ $t->nama_pengaju }}</td>
                        <td>
                            @if($status === 'approve')
                            <span class="badge bg-emerald-100 dark:bg-emerald-900 text-emerald-700 dark:text-emerald-300">Disetujui</span>
                            @else
                            <span class="badge bg-rose-100 dark:bg-rose-900 text-rose-600 dark:text-rose-300">Ditolak</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-100 dark:border-slate-700">{{ $items->links() }}</div>
    </div>
    @endif
</div>
@endsection
