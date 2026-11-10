@extends('layouts.app')
@section('title', 'Cetak Data Guru')

@section('content')
<div class="space-y-5">
    <div>
        <h1 class="page-title">Cetak Data Guru</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Unduh biodata seluruh guru dalam format Excel.</p>
    </div>

    <div class="card p-8 flex flex-col items-center justify-center text-center gap-4">
        <div class="w-16 h-16 rounded-2xl bg-emerald-50 dark:bg-emerald-900/20 flex items-center justify-center">
            <i data-lucide="file-spreadsheet" class="w-8 h-8 text-emerald-600"></i>
        </div>
        <p class="text-sm text-slate-500 dark:text-slate-400">File akan berisi biodata seluruh guru yang terdaftar, urut nama.</p>
        <a href="{{ route('cetak.guru.excel') }}" class="flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-bold bg-emerald-600 text-white hover:bg-emerald-700 transition">
            <i data-lucide="download" class="w-4 h-4"></i> Unduh Data Guru
        </a>
    </div>
</div>
@endsection
