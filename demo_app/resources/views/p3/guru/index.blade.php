@extends('layouts.app')
@section('title', 'Ajukan P3 Siswa')

@section('content')
<div class="space-y-5">
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h1 class="page-title">Ajukan P3 Siswa</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Pilih siswa untuk mengajukan P3 — menunggu persetujuan kesiswaan</p>
        </div>
        <a href="{{ route('p3.guru.riwayat') }}" class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition"><i data-lucide="history" class="w-4 h-4"></i> Riwayat Saya</a>
    </div>

    <form method="GET" class="card p-4 flex flex-wrap gap-2 items-center">
        <div class="relative flex-1 min-w-48">
            <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama siswa..." class="form-input pl-9 py-2 text-sm">
        </div>
        <select name="sort" class="form-select w-auto text-sm" onchange="this.form.submit()">
            <option value="nama" @selected(request('sort','nama')==='nama')>Urutkan: Nama</option>
            <option value="nis" @selected(request('sort')==='nis')>Urutkan: NIS</option>
        </select>
        <button type="submit" class="px-4 py-2 rounded-xl text-sm font-medium border border-slate-200 dark:border-slate-600 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 transition">Cari</button>
        @if(request('search'))
        <a href="{{ route('p3.guru.index') }}" class="px-4 py-2 rounded-xl text-sm text-slate-500 hover:text-slate-700 transition">Reset</a>
        @endif
    </form>

    @if($siswas->isEmpty())
    <div class="card p-12 text-center text-slate-400">
        <i data-lucide="users" class="w-12 h-12 mx-auto mb-3 opacity-30"></i>
        <p class="font-medium">Tidak ada siswa.</p>
    </div>
    @else
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
        @foreach($siswas as $s)
        <a href="{{ route('p3.guru.create', $s) }}" class="card p-3.5 flex items-center gap-3 hover:border-primary/40 transition">
            <div class="w-9 h-9 rounded-xl grid place-items-center text-white text-sm font-bold flex-shrink-0" style="background:{{ $s->jk==='L' ? 'var(--cp)' : '#ec4899' }}">{{ strtoupper(substr($s->nama,0,1)) }}</div>
            <div class="min-w-0 flex-1">
                <p class="font-semibold text-sm text-slate-800 dark:text-slate-200 truncate">{{ $s->nama }}</p>
                <p class="text-xs text-slate-400">NIS {{ $s->nis }} &bull; {{ $s->kelas ? $s->kelas->tingkat.$s->kelas->kelas : '-' }}</p>
            </div>
        </a>
        @endforeach
    </div>
    <div class="card p-4">{{ $siswas->links() }}</div>
    @endif
</div>
@endsection
