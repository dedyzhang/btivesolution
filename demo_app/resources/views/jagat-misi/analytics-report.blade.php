@extends('layouts.app')
@section('title', $report['title'])

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@500;600;700&display=swap" rel="stylesheet">
@include('arena-belajar.partials.game-styles')
<style>
.mx-report { font-family: 'Fredoka', 'Plus Jakarta Sans', system-ui, sans-serif; }
.mx-report-card {
    border: 3px solid rgba(18, 52, 91, 0.1);
    border-radius: 1.35rem;
    background: #fff;
    box-shadow: 0 8px 0 rgba(18, 52, 91, 0.1);
    padding: 1.5rem;
}
.dark .mx-report-card {
    background: #0f172a;
    border-color: #334155;
    box-shadow: 0 8px 0 rgba(0,0,0,.35);
}
@media print {
    .arena-lobby-world, .print\:hidden { display: none !important; }
    .mx-report-card { box-shadow: none; border: 1px solid #cbd5e1; }
}
</style>
@endpush

@section('content')
<div class="max-w-2xl mx-auto space-y-4 arena-stage arena-lobby mx-report print:space-y-2">
    <div class="arena-lobby-world print:hidden" aria-hidden="true">
        <div class="arena-lobby-sky"></div>
    </div>

    <div class="relative z-[1] flex flex-wrap items-center justify-between gap-3 print:hidden">
        <a href="{{ route('jagat-misi.analytics') }}" class="arena-hud-back">
            <i data-lucide="chevron-left" class="w-4 h-4"></i>
            <span>Matriks</span>
        </a>
        <button type="button" onclick="window.print()" class="arena-play-btn arena-play-btn-amber">
            <i data-lucide="printer" class="w-4 h-4"></i> Cetak PDF
        </button>
    </div>

    <div class="mx-report-card relative z-[1]">
        <p class="arena-lobby-kicker" style="color:var(--arena-teal)">Player report</p>
        <h1 class="text-xl sm:text-2xl font-bold text-slate-800 dark:text-slate-100">{{ $report['title'] }}</h1>
        <p class="text-sm font-semibold text-slate-500 mt-1">{{ $report['student']['name'] }} · {{ $report['student']['class_name'] }}</p>
        <p class="mt-4 text-sm text-slate-600 dark:text-slate-300 leading-relaxed">{{ $report['summary'] }}</p>
        <div class="arena-chip3d mt-4 !inline-flex !min-w-0 gap-2 items-baseline px-4">
            <strong>{{ $report['average_score'] }}</strong>
            <span>Rata-rata skor</span>
        </div>
        <ul class="mt-5 space-y-2.5 text-sm">
            @foreach($report['masteries'] as $m)
            <li class="flex flex-wrap items-center justify-between gap-2 rounded-xl border-2 border-slate-100 dark:border-slate-700 px-3 py-2.5">
                <strong class="text-slate-800 dark:text-slate-100">{{ $m['concept_label'] }}</strong>
                <span class="font-bold" style="color:var(--arena-teal)">{{ $m['score'] }} · {{ $m['level'] }}</span>
            </li>
            @endforeach
        </ul>
    </div>
</div>
@endsection
