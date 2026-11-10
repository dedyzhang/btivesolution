@extends('layouts.app')
@section('title', 'Master Kategori P3')

@php
$p3Warna = ['prestasi'=>'emerald','partisipasi'=>'blue','pelanggaran'=>'rose'];
@endphp

@section('content')
<div class="space-y-5">
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h1 class="page-title">Master Kategori P3</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Pelanggaran, Prestasi &amp; Partisipasi — preset poin per kategori</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('p3.siswa.index') }}" class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition"><i data-lucide="users" class="w-4 h-4"></i> P3 Siswa</a>
            <a href="{{ route('p3.create') }}" class="btn-primary flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold shadow-sm transition"><i data-lucide="plus" class="w-4 h-4"></i> Tambah Kategori</a>
        </div>
    </div>

    @if($kategoris->isEmpty())
    <div class="card p-12 text-center text-slate-400">
        <i data-lucide="list-checks" class="w-12 h-12 mx-auto mb-3 opacity-30"></i>
        <p class="font-medium">Belum ada kategori.</p>
        <a href="{{ route('p3.create') }}" class="text-primary hover:underline text-sm mt-1 inline-block">Tambah sekarang</a>
    </div>
    @else
    <div class="card overflow-hidden">
        <div class="table-responsive">
            <table class="data-table w-full">
                <thead>
                    <tr>
                        <th class="w-10">#</th>
                        <x-sortable-th field="jenis" label="Jenis" />
                        <x-sortable-th field="deskripsi" label="Deskripsi" />
                        <x-sortable-th field="poin" label="Poin" align="right" />
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($kategoris as $i => $k)
                    @php $w = $p3Warna[$k->jenis] ?? 'slate'; @endphp
                    <tr>
                        <td class="text-slate-400 text-xs">{{ $kategoris->firstItem() + $i }}</td>
                        <td><span class="badge bg-{{ $w }}-100 dark:bg-{{ $w }}-900 text-{{ $w }}-700 dark:text-{{ $w }}-300">{{ \App\Models\P3Kategori::JENIS[$k->jenis] ?? $k->jenis }}</span></td>
                        <td class="text-slate-600 dark:text-slate-300 text-sm">{{ $k->deskripsi }}</td>
                        <td class="text-right font-bold text-slate-700 dark:text-slate-200">{{ $k->poin }}</td>
                        <td class="text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                <a href="{{ route('p3.edit', $k) }}" class="p-1.5 rounded-lg border border-slate-200 dark:border-slate-600 text-slate-400 hover:text-primary"><i data-lucide="pencil" class="w-3.5 h-3.5"></i></a>
                                <form method="POST" action="{{ route('p3.destroy', $k) }}" onsubmit="return confirmDelete(this)">
                                    @csrf @method('DELETE')
                                    <button class="p-1.5 rounded-lg border border-rose-200 dark:border-rose-800 text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/30"><i data-lucide="trash-2" class="w-3.5 h-3.5"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-100 dark:border-slate-700">{{ $kategoris->links() }}</div>
    </div>
    @endif
</div>
@endsection
