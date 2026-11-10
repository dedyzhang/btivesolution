@extends('layouts.app')
@section('title', 'Nilai Kelas Saya')

@section('content')
<div class="space-y-5">
    <div>
        <h1 class="page-title">Nilai Kelas Saya</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
            Kelas {{ $kelas->tingkat }}{{ $kelas->kelas }} &bull; lihat saja (Formatif, Sumatif, PAS)
            @if($semester) &bull; <span class="font-semibold text-slate-600 dark:text-slate-300">{{ $semester->nama_lengkap }}</span> @endif
        </p>
    </div>

    @if($ngajars->isEmpty())
    <div class="card p-12 text-center text-slate-400">
        <i data-lucide="book-x" class="w-12 h-12 mx-auto mb-3 opacity-30"></i>
        <p class="font-medium">Belum ada penugasan mengajar di kelas ini.</p>
    </div>
    @else
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($ngajars as $n)
        <div class="card p-4 flex flex-col gap-3">
            <div class="flex items-start justify-between gap-2">
                <div class="min-w-0">
                    <p class="font-bold text-slate-800 dark:text-slate-100 truncate text-base">{{ $n->pelajaran?->nama }}</p>
                    <p class="text-xs text-slate-400 font-medium mt-0.5">{{ $n->pelajaran?->kode }} &bull; KKM {{ $n->kkm ?? $n->pelajaran?->kkm ?? 75 }}</p>
                </div>
                <span class="badge bg-primary/10 text-primary flex-shrink-0 font-bold px-3 py-1 rounded-full">{{ $n->kelas?->tingkat }}{{ $n->kelas?->kelas }}</span>
            </div>
            @if($n->mine)
            <p class="text-xs text-emerald-600 dark:text-emerald-400 flex items-center gap-1.5 font-medium">
                <i data-lucide="pencil" class="w-3.5 h-3.5"></i> {{ $n->guru?->nama }} &bull; Anda yang mengajar
            </p>
            @else
            <p class="text-xs text-sky-600 dark:text-sky-400 flex items-center gap-1.5 font-medium">
                <i data-lucide="eye" class="w-3.5 h-3.5"></i> {{ $n->guru?->nama }} &bull; Lihat saja
            </p>
            @endif
            <div class="grid grid-cols-3 gap-2 mt-1">
                <a href="{{ route('nilai.formatif', $n->uuid) }}" class="flex items-center justify-center gap-1.5 px-2.5 py-2 rounded-xl text-xs font-bold border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 hover:bg-white/40 dark:hover:bg-slate-700/50 transition">
                    <i data-lucide="pencil" class="w-3.5 h-3.5"></i> Formatif
                </a>
                <a href="{{ route('nilai.sumatif', $n->uuid) }}" class="flex items-center justify-center gap-1.5 px-2.5 py-2 rounded-xl text-xs font-bold border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 hover:bg-white/40 dark:hover:bg-slate-700/50 transition">
                    <i data-lucide="clipboard-check" class="w-3.5 h-3.5"></i> Sumatif
                </a>
                <a href="{{ route('nilai.pas', $n->uuid) }}" class="flex items-center justify-center gap-1.5 px-2.5 py-2 rounded-xl text-xs font-bold border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 hover:bg-white/40 dark:hover:bg-slate-700/50 transition">
                    <i data-lucide="file-check-2" class="w-3.5 h-3.5"></i> PAS
                </a>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>
@endsection
