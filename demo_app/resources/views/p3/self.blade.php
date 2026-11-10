@extends('layouts.app')
@section('title', 'P3 Saya')

@php
$p3Warna = ['prestasi'=>'emerald','partisipasi'=>'blue','pelanggaran'=>'rose'];
$p3Icon  = ['prestasi'=>'award','partisipasi'=>'handshake','pelanggaran'=>'triangle-alert'];
@endphp

@section('content')
<div class="max-w-3xl mx-auto space-y-5">
    <div>
        <h1 class="page-title">P3 {{ auth()->user()->access === 'orangtua' ? $siswa->nama : 'Saya' }}</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Pelanggaran, Prestasi &amp; Partisipasi</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        @foreach(['prestasi'=>'Poin Prestasi','partisipasi'=>'Poin Partisipasi','pelanggaran'=>'Poin Pelanggaran'] as $jenis => $label)
        @php $w = $p3Warna[$jenis]; @endphp
        <div class="card p-5 flex items-center justify-between">
            <div>
                <p class="text-xs text-slate-400 font-semibold">{{ $label }}</p>
                <p class="text-2xl font-extrabold text-{{ $w }}-600 dark:text-{{ $w }}-400 mt-1">{{ $totals[$jenis] }}</p>
            </div>
            <span class="grid place-items-center w-10 h-10 rounded-xl bg-{{ $w }}-100 dark:bg-{{ $w }}-900 text-{{ $w }}-600 dark:text-{{ $w }}-300"><i data-lucide="{{ $p3Icon[$jenis] }}" class="w-5 h-5"></i></span>
        </div>
        @endforeach
    </div>

    <div class="card overflow-hidden">
        <div class="table-responsive">
            <table class="data-table w-full">
                <thead>
                    <tr>
                        <th class="w-10">#</th>
                        <x-sortable-th field="tanggal" label="Tanggal" />
                        <x-sortable-th field="jenis" label="Jenis" />
                        <x-sortable-th field="deskripsi" label="Keterangan" />
                        <x-sortable-th field="poin" label="Poin" align="right" />
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $i => $r)
                    @php $w = $p3Warna[$r->jenis] ?? 'slate'; @endphp
                    <tr>
                        <td class="text-slate-400 text-xs">{{ $rows->firstItem() + $i }}</td>
                        <td class="text-sm text-slate-500">{{ $r->tanggal->isoFormat('D MMM Y') }}</td>
                        <td><span class="badge bg-{{ $w }}-100 dark:bg-{{ $w }}-900 text-{{ $w }}-700 dark:text-{{ $w }}-300">{{ \App\Models\P3Kategori::JENIS[$r->jenis] ?? $r->jenis }}</span></td>
                        <td class="text-sm text-slate-600 dark:text-slate-300">{{ $r->deskripsi }}</td>
                        <td class="text-right font-bold text-slate-700 dark:text-slate-200">{{ $r->poin }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center text-slate-400 py-8">Belum ada catatan P3.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-100 dark:border-slate-700">{{ $rows->links() }}</div>
    </div>
</div>
@endsection
