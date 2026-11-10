@extends('layouts.app')
@section('title', 'Cetak Data Agenda')

@section('content')
<div class="space-y-5">
    <div>
        <h1 class="page-title">Cetak Data Agenda</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Unduh data agenda mengajar guru pada rentang tanggal tertentu dalam format Excel.</p>
    </div>

    <form method="GET" action="{{ route('cetak.agenda.excel') }}" class="card p-6 flex flex-wrap gap-4 items-end"
          onsubmit="setTimeout(() => window.hideGlobalSpinner && hideGlobalSpinner(), 1200)">
        <div class="space-y-1.5">
            <label class="form-label">Dari Tanggal</label>
            <input type="date" name="dari" required class="form-input" value="{{ now()->startOfMonth()->format('Y-m-d') }}">
        </div>
        <div class="space-y-1.5">
            <label class="form-label">Sampai Tanggal</label>
            <input type="date" name="sampai" required class="form-input" value="{{ now()->format('Y-m-d') }}">
        </div>
        <div class="space-y-1.5 flex-1 min-w-48">
            <label class="form-label">Guru (opsional)</label>
            <select name="id_guru" class="form-select">
                <option value="">Semua Guru</option>
                @foreach($guruList as $g)
                <option value="{{ $g->uuid }}">{{ $g->nama }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-bold bg-emerald-600 text-white hover:bg-emerald-700 transition">
            <i data-lucide="download" class="w-4 h-4"></i> Unduh Excel
        </button>
    </form>
</div>
@endsection
