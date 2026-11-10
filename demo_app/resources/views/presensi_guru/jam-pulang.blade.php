@extends('layouts.app')
@section('title', 'Jadwal Pulang Guru')

@section('content')
<div class="space-y-5" x-data="{ selected: [] }">

    <div class="flex items-center gap-3">
        <a href="{{ route('presensi-guru.index') }}" class="grid place-items-center w-10 h-10 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-500 hover:text-primary hover:border-primary transition">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </a>
        <div>
            <h1 class="page-title">Jadwal Pulang Guru</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Guru dengan jam pulang wajib hanya bisa scan wajah absen pulang pada jam itu atau setelahnya. Kosongkan untuk bebas (tanpa batasan).</p>
        </div>
    </div>

    @if(session('success'))
    <div class="rounded-xl bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-700 p-3 text-sm text-emerald-700 dark:text-emerald-300 font-semibold">{{ session('success') }}</div>
    @endif

    <form method="GET" action="{{ route('presensi-guru.jamPulang.index') }}" class="card p-4 flex flex-wrap gap-3 items-end">
        <div class="flex-1 min-w-48">
            <label class="form-label">Cari nama guru</label>
            <input type="text" name="search" value="{{ $search }}" placeholder="Ketik nama guru..." class="form-input">
        </div>
        <button type="submit" class="px-4 py-2.5 rounded-xl text-sm font-medium border border-slate-200 dark:border-slate-600 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 transition">Cari</button>
    </form>

    <form method="POST" action="{{ route('presensi-guru.jamPulang.update') }}" @submit="if(selected.length===0){ alert('Pilih minimal 1 guru dulu.'); $event.preventDefault(); }">
        @csrf
        <template x-for="uuid in selected" :key="uuid">
            <input type="hidden" name="guru_ids[]" :value="uuid">
        </template>

        <div class="card p-4 flex flex-wrap items-end gap-3 mb-4 sticky top-2 z-10">
            <div class="text-sm font-semibold text-slate-600 dark:text-slate-300">
                <span x-text="selected.length"></span> guru dipilih
            </div>
            <div class="min-w-40">
                <label class="form-label">Jam pulang wajib</label>
                <input type="time" name="jam_pulang_wajib" class="form-input">
            </div>
            <button type="submit" :disabled="selected.length===0" class="btn-primary px-4 py-2.5 rounded-xl text-sm font-bold disabled:opacity-40 flex items-center gap-2">
                <i data-lucide="clock-4" class="w-4 h-4"></i> Terapkan Jam Pulang
            </button>
            <button type="submit" onclick="this.form.jam_pulang_wajib.value=''" :disabled="selected.length===0" class="px-4 py-2.5 rounded-xl text-sm font-bold border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 disabled:opacity-40 flex items-center gap-2">
                <i data-lucide="unlock" class="w-4 h-4"></i> Bebaskan (Hapus Jadwal)
            </button>
        </div>

        @if($gurus->isEmpty())
        <div class="card p-12 text-center text-slate-400">
            <i data-lucide="user-x" class="w-12 h-12 mx-auto mb-3 opacity-30"></i>
            <p class="font-medium">Tidak ada guru ditemukan.</p>
        </div>
        @else
        <div class="card overflow-hidden">
            <div class="p-3 border-b border-slate-100 dark:border-slate-700 flex items-center gap-2">
                <label class="flex items-center gap-2 text-xs font-semibold text-slate-500 cursor-pointer">
                    <input type="checkbox" @change="selected = $event.target.checked ? [{{ $gurus->pluck('uuid')->map(fn($u) => "'{$u}'")->implode(',') }}] : []" class="accent-[color:var(--cp)] w-4 h-4">
                    Pilih semua ({{ $gurus->count() }} guru)
                </label>
            </div>
            <div class="divide-y divide-slate-100 dark:divide-slate-700">
                @foreach($gurus as $g)
                <label class="p-3.5 flex items-center gap-3 hover:bg-slate-50 dark:hover:bg-slate-900/40 transition cursor-pointer">
                    <input type="checkbox" value="{{ $g->uuid }}" x-model="selected" class="accent-[color:var(--cp)] w-4 h-4 flex-shrink-0">
                    <div class="w-9 h-9 rounded-full grid place-items-center text-white text-xs font-bold flex-shrink-0" style="background:{{ $g->jk==='P' ? '#ec4899' : 'var(--cp)' }}">{{ strtoupper(substr($g->nama,0,1)) }}</div>
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-sm text-slate-700 dark:text-slate-200 truncate">{{ $g->nama }}</p>
                        <p class="text-xs text-slate-400">{{ $g->nip ?: ($g->nik ?: 'Guru') }}</p>
                    </div>
                    @if($g->jam_pulang_wajib)
                    <span class="badge bg-amber-100 dark:bg-amber-900 text-amber-700 dark:text-amber-300 font-bold flex-shrink-0">Pulang {{ substr($g->jam_pulang_wajib,0,5) }}</span>
                    @else
                    <span class="badge bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 flex-shrink-0">Bebas</span>
                    @endif
                </label>
                @endforeach
            </div>
        </div>
        @endif
    </form>
</div>
@endsection
