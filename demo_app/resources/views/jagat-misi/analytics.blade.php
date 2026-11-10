@extends('layouts.app')
@section('title', 'Matriks Penguasaan — Arena Belajar')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@500;600;700&display=swap" rel="stylesheet">
@include('arena-belajar.partials.game-styles')
<style>
.mx-board {
    --lobby-font: 'Fredoka', 'Plus Jakarta Sans', system-ui, sans-serif;
    font-family: var(--lobby-font);
}
.mx-board, .mx-board button, .mx-board a, .mx-board input, .mx-board select {
    font-family: var(--lobby-font);
}
.mx-table-wrap {
    position: relative;
    z-index: 1;
    border: 3px solid rgba(18, 52, 91, 0.1);
    border-radius: 1.25rem;
    background: rgba(255,255,255,.95);
    box-shadow: 0 8px 0 rgba(18, 52, 91, 0.1);
    overflow: hidden;
}
.dark .mx-table-wrap {
    background: rgba(15, 23, 42, .95);
    border-color: #334155;
    box-shadow: 0 8px 0 rgba(0,0,0,.35);
}
.mx-table {
    width: 100%;
    min-width: 640px;
    border-collapse: separate;
    border-spacing: 0;
    font-size: .85rem;
}
.mx-table thead th {
    position: sticky;
    top: 0;
    z-index: 2;
    padding: .85rem 1rem;
    text-align: left;
    font-size: .68rem;
    font-weight: 800;
    letter-spacing: .06em;
    text-transform: uppercase;
    color: #0b5e8a;
    background: linear-gradient(180deg, #dff3ff, #c9ebff);
    border-bottom: 2px solid rgba(18, 52, 91, 0.1);
    white-space: nowrap;
}
.dark .mx-table thead th {
    color: #7dd3fc;
    background: linear-gradient(180deg, #15253a, #0f172a);
    border-bottom-color: #334155;
}
.mx-table tbody td {
    padding: .8rem 1rem;
    border-top: 1px solid rgba(18, 52, 91, 0.06);
    vertical-align: middle;
}
.dark .mx-table tbody td { border-top-color: #1e293b; }
.mx-table tbody tr:hover td {
    background: rgba(0, 194, 178, 0.06);
}
.mx-score {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 2.6rem;
    padding: .28rem .55rem;
    border-radius: .65rem;
    font-weight: 800;
    font-size: .8rem;
    border: 2px solid transparent;
    box-shadow: 0 3px 0 rgba(0,0,0,.08);
}
.mx-score-strong {
    background: linear-gradient(180deg, #86efac, #22c55e);
    color: #14532d;
    border-color: #16a34a;
}
.mx-score-watch {
    background: linear-gradient(180deg, #fde68a, #f59e0b);
    color: #78350f;
    border-color: #d97706;
}
.mx-score-support {
    background: linear-gradient(180deg, #fda4af, #f43f5e);
    color: #881337;
    border-color: #e11d48;
}
.mx-score-empty {
    background: #f1f5f9;
    color: #94a3b8;
    border-color: #e2e8f0;
    box-shadow: none;
}
.dark .mx-score-empty {
    background: #1e293b;
    color: #64748b;
    border-color: #334155;
}
.mx-level {
    display: inline-flex;
    align-items: center;
    gap: .3rem;
    padding: .3rem .65rem;
    border-radius: .65rem;
    font-size: .68rem;
    font-weight: 800;
    letter-spacing: .04em;
    text-transform: uppercase;
    box-shadow: 0 3px 0 rgba(0,0,0,.1);
}
.mx-level-strong { background: #dcfce7; color: #166534; }
.mx-level-watch { background: #fef3c7; color: #92400e; }
.mx-level-support { background: #ffe4e6; color: #9f1239; }
.dark .mx-level-strong { background: rgba(34,197,94,.2); color: #86efac; }
.dark .mx-level-watch { background: rgba(245,158,11,.2); color: #fbbf24; }
.dark .mx-level-support { background: rgba(244,63,94,.2); color: #fda4af; }
.mx-player {
    display: flex;
    align-items: center;
    gap: .65rem;
}
.mx-avatar {
    width: 2.2rem;
    height: 2.2rem;
    border-radius: .75rem;
    display: grid;
    place-items: center;
    font-size: .75rem;
    font-weight: 800;
    color: #fff;
    background: linear-gradient(145deg, #00c2b2, #0b3d6e);
    box-shadow: inset 0 2px 0 rgba(255,255,255,.3), 0 3px 0 rgba(11,61,110,.3);
    flex-shrink: 0;
}
</style>
@endpush

@php
    $students = $matrix['students'] ?? [];
    $concepts = $matrix['concepts'] ?? [];
    $studentCount = count($students);
    $conceptCount = count($concepts);
    $levelLabels = [
        'strong' => 'Kuat',
        'watch' => 'Pantau',
        'support' => 'Dukung',
    ];
    $scoreTone = function ($score) {
        if ($score === null || $score === '' || $score === '—') {
            return 'empty';
        }
        $n = (int) $score;
        if ($n >= 80) {
            return 'strong';
        }
        if ($n >= 68) {
            return 'watch';
        }

        return 'support';
    };
@endphp

@section('content')
<div class="space-y-5 arena-stage arena-lobby mx-board">
    <div class="arena-lobby-world" aria-hidden="true">
        <div class="arena-lobby-sky"></div>
        <div class="arena-lobby-grid"></div>
        <span class="arena-float-block arena-fb-a"></span>
        <span class="arena-float-block arena-fb-b"></span>
        <span class="arena-float-coin arena-fc-a"></span>
    </div>

    <header class="arena-lobby-hud arena-anim-in">
        <a href="{{ route('jagat-misi.index') }}" class="arena-hud-back">
            <i data-lucide="chevron-left" class="w-4 h-4"></i>
            <span>Katalog Misi</span>
        </a>
        <div class="arena-hud-player">
            <span class="arena-hud-avatar" aria-hidden="true"><i data-lucide="bar-chart-3" class="w-4 h-4"></i></span>
            <div class="min-w-0">
                <p class="arena-hud-name">Host analytics</p>
                <p class="arena-hud-role">Matriks penguasaan</p>
            </div>
        </div>
    </header>

    <section class="relative z-[1] text-center pt-1 pb-2 arena-anim-in">
        <p class="arena-lobby-kicker">Arena Belajar · Stats board</p>
        <h1 class="arena-lobby-brand" style="font-size:clamp(1.8rem,5vw,2.75rem)">Matriks Penguasaan</h1>
        <p class="arena-lobby-tagline">Pantau skor konsep per siswa — kuat, pantau, atau butuh dukungan.</p>

        <div class="arena-lobby-stats mt-4">
            <div class="arena-chip3d">
                <strong>{{ $studentCount }}</strong>
                <span>Player</span>
            </div>
            <div class="arena-chip3d arena-chip3d-amber">
                <strong>{{ $conceptCount }}</strong>
                <span>Konsep</span>
            </div>
            <div class="arena-chip3d arena-chip3d-sky">
                <strong>LIVE</strong>
                <span>Sync mastery</span>
            </div>
        </div>

        <div class="flex flex-wrap justify-center gap-2 mt-4">
            <span class="mx-level mx-level-strong">★ Kuat ≥80</span>
            <span class="mx-level mx-level-watch">● Pantau 68–79</span>
            <span class="mx-level mx-level-support">! Dukung &lt;68</span>
        </div>
    </section>

    <div class="mx-table-wrap arena-anim-in overflow-x-auto">
        <table class="mx-table">
            <thead>
                <tr>
                    <th>Player</th>
                    <th>Kelas</th>
                    @foreach($concepts as $concept)
                    <th title="{{ $concept['subject'] ?? '' }}">{{ $concept['label'] }}</th>
                    @endforeach
                    <th>Level</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($students as $row)
                @php
                    $level = $row['level'] ?? 'support';
                    $levelLabel = $levelLabels[$level] ?? ucfirst($level);
                    $initial = mb_strtoupper(mb_substr($row['name'] ?? '?', 0, 1));
                @endphp
                <tr>
                    <td>
                        <div class="mx-player">
                            <span class="mx-avatar" aria-hidden="true">{{ $initial }}</span>
                            <span class="font-bold text-slate-800 dark:text-slate-100">{{ $row['name'] }}</span>
                        </div>
                    </td>
                    <td class="font-semibold text-slate-500">{{ $row['class_name'] }}</td>
                    @foreach($concepts as $concept)
                    @php
                        $score = $row['scores'][$concept['key']] ?? null;
                        $tone = $scoreTone($score);
                    @endphp
                    <td>
                        <span class="mx-score mx-score-{{ $tone }}">{{ $score !== null ? $score : '—' }}</span>
                    </td>
                    @endforeach
                    <td>
                        <span class="mx-level mx-level-{{ $level }}">{{ $levelLabel }}</span>
                    </td>
                    <td>
                        <a href="{{ route('jagat-misi.analytics.report', $row['user_id']) }}"
                           class="text-xs font-bold whitespace-nowrap" style="color:var(--arena-teal)">
                            Laporan →
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="{{ max(5, 4 + $conceptCount) }}" class="p-12 text-center">
                        <div class="inline-grid place-items-center w-16 h-16 rounded-2xl mb-3"
                             style="background:#eaf8f6;color:var(--arena-teal);box-shadow:0 5px 0 rgba(0,169,157,.2)">
                            <i data-lucide="gamepad-2" class="w-8 h-8"></i>
                        </div>
                        <p class="font-black text-lg text-slate-700 dark:text-slate-200">Board masih kosong</p>
                        <p class="text-sm text-slate-500 mt-1 max-w-sm mx-auto">Belum ada data penguasaan. Setelah siswa menyelesaikan misi, skor konsep muncul di sini.</p>
                        <a href="{{ route('jagat-misi.index') }}" class="arena-play-btn mt-4 inline-flex">Ke katalog misi</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
