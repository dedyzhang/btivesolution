@extends('layouts.app')
@section('title', 'Riwayat Pengajuan P3 Saya')

@php $p3Warna = ['prestasi'=>'emerald','partisipasi'=>'blue','pelanggaran'=>'rose']; @endphp

@section('content')
<div class="space-y-5">
    <a href="{{ route('p3.guru.index') }}" class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-primary"><i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali</a>
    <div>
        <h1 class="page-title">Riwayat Pengajuan P3 Saya</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Status pengajuan P3 yang pernah Anda ajukan</p>
    </div>

    @if($items->isEmpty())
    <div class="card p-12 text-center text-slate-400">
        <i data-lucide="history" class="w-12 h-12 mx-auto mb-3 opacity-30"></i>
        <p class="font-medium">Belum ada pengajuan.</p>
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
                        <x-sortable-th field="jenis" label="Jenis" />
                        <th>Deskripsi</th>
                        <x-sortable-th field="poin" label="Poin" align="right" />
                        <x-sortable-th field="status" label="Status" />
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $i => $t)
                    @php $w = $p3Warna[$t->jenis] ?? 'slate'; @endphp
                    <tr>
                        <td class="text-slate-400 text-xs">{{ $items->firstItem() + $i }}</td>
                        <td class="text-sm text-slate-500">{{ $t->tanggal->isoFormat('D MMM Y') }}</td>
                        <td class="font-semibold text-slate-800 dark:text-slate-200">{{ $t->siswa?->nama }}</td>
                        <td><span class="badge bg-{{ $w }}-100 dark:bg-{{ $w }}-900 text-{{ $w }}-700 dark:text-{{ $w }}-300">{{ \App\Models\P3Kategori::JENIS[$t->jenis] ?? $t->jenis }}</span></td>
                        <td class="text-sm text-slate-600 dark:text-slate-300">{{ $t->deskripsi }}</td>
                        <td class="text-right font-bold text-slate-700 dark:text-slate-200">{{ $t->poin }}</td>
                        <td>
                            @if($t->status === 'approve')
                            <span class="badge bg-emerald-100 dark:bg-emerald-900 text-emerald-700 dark:text-emerald-300">Disetujui</span>
                            @elseif($t->status === 'disapprove')
                            <span class="badge bg-rose-100 dark:bg-rose-900 text-rose-600 dark:text-rose-300">Ditolak</span>
                            @else
                            <span class="badge bg-amber-100 dark:bg-amber-900 text-amber-700 dark:text-amber-300">Menunggu</span>
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
