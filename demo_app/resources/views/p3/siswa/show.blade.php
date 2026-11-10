@extends('layouts.app')
@section('title', 'P3 — '.$siswa->nama)

@php
$p3Warna = ['prestasi'=>'emerald','partisipasi'=>'blue','pelanggaran'=>'rose'];
$p3Icon  = ['prestasi'=>'award','partisipasi'=>'handshake','pelanggaran'=>'triangle-alert'];
@endphp

@section('content')
<div class="max-w-4xl mx-auto space-y-5">
    <a href="{{ route('p3.siswa.index') }}" class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-primary"><i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali</a>

    {{-- Info siswa --}}
    <div class="card p-5 flex items-center justify-between flex-wrap gap-3">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-xl grid place-items-center text-white text-lg font-bold flex-shrink-0" style="background:var(--cp)">{{ strtoupper(substr($siswa->nama,0,1)) }}</div>
            <div>
                <p class="font-bold text-lg text-slate-800 dark:text-slate-100">{{ $siswa->nama }}</p>
                <p class="text-xs text-slate-400">NIS {{ $siswa->nis }} &bull; Kelas {{ $siswa->kelas ? $siswa->kelas->tingkat.$siswa->kelas->kelas : '-' }}</p>
            </div>
        </div>
        @if(in_array(auth()->user()->access, ['superadmin', 'admin', 'kesiswaan']))
        <div class="flex items-center gap-2">
            <a href="{{ route('p3.siswa.print', $siswa) }}" target="_blank" class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition"><i data-lucide="printer" class="w-4 h-4"></i> Cetak</a>
            <a href="{{ route('p3.siswa.create', $siswa) }}" class="btn-primary flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold shadow-sm"><i data-lucide="plus" class="w-4 h-4"></i> Tambah P3</a>
        </div>
        @endif
    </div>

    {{-- 3 kartu total --}}
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

    {{-- Log --}}
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
                        @if(in_array(auth()->user()->access, ['superadmin', 'admin', 'kesiswaan']))
                        <th class="text-right">Aksi</th>
                        @endif
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
                        @if(in_array(auth()->user()->access, ['superadmin', 'admin', 'kesiswaan']))
                        <td class="text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                <a href="{{ route('p3.entri.edit', $r) }}" class="p-1.5 rounded-lg border border-slate-200 dark:border-slate-600 text-slate-400 hover:text-primary"><i data-lucide="pencil" class="w-3.5 h-3.5"></i></a>
                                <form method="POST" action="{{ route('p3.entri.delete', $r) }}" onsubmit="return confirmDelete(this)">
                                    @csrf @method('DELETE')
                                    <button class="p-1.5 rounded-lg border border-rose-200 dark:border-rose-800 text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/30"><i data-lucide="trash-2" class="w-3.5 h-3.5"></i></button>
                                </form>
                            </div>
                        </td>
                        @endif
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-slate-400 py-8">Belum ada catatan P3.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-100 dark:border-slate-700">{{ $rows->links() }}</div>
    </div>
</div>
@endsection
