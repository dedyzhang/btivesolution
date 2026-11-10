@extends('layouts.app')
@section('title', 'Cetak Buku Batas')

@section('content')
<div class="space-y-5">
    <div>
        <h1 class="page-title">Cetak Buku Batas</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Unduh log materi/batas pelajaran satu kelas pada rentang tanggal tertentu dalam format Excel.</p>
    </div>

    <form method="GET" action="{{ route('cetak.bukuBatas.excel') }}" class="card p-6 flex flex-wrap gap-4 items-end"
          onsubmit="setTimeout(() => window.hideGlobalSpinner && hideGlobalSpinner(), 1200)">
        <div class="flex-1 min-w-40">
            <label class="form-label">Kelas</label>
            <select name="kelas" required class="form-select">
                @foreach($kelas as $k)
                <option value="{{ $k->uuid }}">Kelas {{ $k->tingkat }}{{ $k->kelas }}</option>
                @endforeach
            </select>
        </div>
        <div class="space-y-1.5">
            <label class="form-label">Dari Tanggal</label>
            <input type="date" name="dari" required class="form-input" value="{{ $dari }}">
        </div>
        <div class="space-y-1.5">
            <label class="form-label">Sampai Tanggal</label>
            <input type="date" name="sampai" required class="form-input" value="{{ $sampai }}">
        </div>
        <button type="submit" class="flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-bold bg-emerald-600 text-white hover:bg-emerald-700 transition">
            <i data-lucide="download" class="w-4 h-4"></i> Unduh Excel
        </button>
    </form>
</div>
@endsection
