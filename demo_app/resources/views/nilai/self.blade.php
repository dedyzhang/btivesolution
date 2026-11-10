@extends('layouts.app')
@section('title', 'Nilai Saya')

@section('content')
<div class="max-w-4xl mx-auto space-y-5">
    <div>
        <h1 class="page-title">Nilai {{ auth()->user()->access === 'orangtua' ? $siswa->nama : 'Saya' }}</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
            Kelas {{ $siswa->kelas ? $siswa->kelas->tingkat . $siswa->kelas->kelas : '-' }} &bull; NIS {{ $siswa->nis }}
            @if($semester) &bull; Semester {{ $semester->semester }} / {{ $semester->tahun }} @endif
        </p>
    </div>

    @if($mapel->isEmpty())
    <div class="card p-12 text-center text-slate-400">
        <i data-lucide="book-x" class="w-12 h-12 mx-auto mb-3 opacity-30"></i>
        <p class="font-medium">Belum ada mata pelajaran untuk kelas ini.</p>
    </div>
    @else
    <div class="space-y-3">
        @foreach($mapel as $m)
        @php $ngajar = $m['ngajar']; @endphp
        <div class="card overflow-hidden" x-data="{ open: false }">
            <button type="button" @click="open = !open" class="w-full flex items-center justify-between gap-4 p-4 text-left hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                <div class="flex items-center gap-3 min-w-0">
                    <span class="w-10 h-10 rounded-xl bg-primary/10 text-primary grid place-items-center flex-shrink-0"><i data-lucide="book-open" class="w-5 h-5"></i></span>
                    <div class="min-w-0">
                        <p class="font-bold text-slate-700 dark:text-slate-200 truncate">{{ $ngajar->pelajaran?->nama }}</p>
                        <p class="text-xs text-slate-400 truncate">{{ $ngajar->guru?->nama ?? '-' }}</p>
                    </div>
                </div>
                <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 transition-transform flex-shrink-0" :class="{ 'rotate-180': open }"></i>
            </button>

            <div x-show="open" x-collapse x-cloak class="border-t border-slate-100 dark:border-slate-700 p-4 space-y-3">
                @forelse($m['materi'] as $materi)
                <div class="rounded-xl border border-slate-200 dark:border-slate-700 p-3 bg-slate-50/50 dark:bg-slate-800/20">
                    <p class="text-xs font-bold text-slate-600 dark:text-slate-300 mb-2 truncate flex items-center gap-1.5">
                        <i data-lucide="bookmark" class="w-3.5 h-3.5 text-primary flex-shrink-0"></i>
                        {{ $materi->nama }}
                    </p>
                    <div class="space-y-1">
                        <div class="flex items-center justify-between gap-3 text-xs py-1.5 px-3 rounded-lg bg-primary/5 border border-primary/10">
                            <span class="font-semibold text-primary">Formatif</span>
                        </div>
                        @forelse($materi->tujuan as $tp)
                        <div class="flex items-center justify-between gap-3 text-xs py-1.5 px-3 rounded-lg bg-white dark:bg-slate-800/60">
                            <span class="text-slate-600 dark:text-slate-300">{{ $tp->tupe }}</span>
                            <span class="font-bold text-slate-700 dark:text-slate-200 flex-shrink-0">{{ $m['fmtRows']->get($tp->uuid)?->nilai ?? '-' }}</span>
                        </div>
                        @empty
                        <p class="text-xs text-slate-300 italic">Belum ada tujuan pembelajaran.</p>
                        @endforelse
                        <div class="flex items-center justify-between gap-3 text-xs py-1.5 px-3 rounded-lg bg-primary/5 border border-primary/10">
                            <span class="font-semibold text-primary">Sumatif</span>
                            <span class="font-bold text-primary flex-shrink-0">{{ $m['sumRows']->get($materi->uuid)?->nilai ?? '-' }}</span>
                        </div>
                    </div>
                </div>
                @empty
                <p class="text-xs text-slate-400 text-center py-4">Belum ada materi untuk mapel ini.</p>
                @endforelse
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>
@endsection
