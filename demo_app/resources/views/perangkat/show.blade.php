@extends('layouts.app')
@section('title', 'Perangkat Ajar — ' . $guru->nama)

@section('content')
<div class="space-y-5">
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div class="flex items-start gap-3 min-w-0">
            @if($bisaPantau && !$isSelf)
            <a href="{{ route('perangkat.index') }}" class="grid place-items-center w-10 h-10 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-500 hover:text-primary hover:border-primary transition flex-shrink-0 mt-0.5">
                <i data-lucide="arrow-left" class="w-5 h-5"></i>
            </a>
            @endif
            <div class="min-w-0">
                <h1 class="page-title truncate">Perangkat Ajar{{ $isSelf ? '' : ' — ' . $guru->nama }}</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Unggah dokumen RPP, Modul Ajar, dan perangkat mengajar lainnya (PDF, maks 20MB).</p>
            </div>
        </div>
        @if($uploads->isNotEmpty())
        <a href="{{ route('perangkat.zip', $guru) }}" class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold border border-emerald-200 dark:border-emerald-700 text-emerald-700 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 transition flex-shrink-0">
            <i data-lucide="folder-down" class="w-4 h-4"></i> Unduh Semua (Zip)
        </a>
        @endif
    </div>

    @if($list->isEmpty())
    <div class="card p-12 text-center text-slate-400">
        <i data-lucide="folder-x" class="w-12 h-12 mx-auto mb-3 opacity-30"></i>
        <p class="font-medium">Belum ada jenis dokumen perangkat ajar.</p>
        @if($bisaPantau)<p class="text-sm mt-1">Tambahkan jenis dokumen dulu di <a href="{{ route('perangkat.index') }}" class="text-primary hover:underline">halaman Perangkat Ajar</a>.</p>@endif
    </div>
    @else
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        @foreach($list as $l)
        @php $files = $uploads->get($l->uuid, collect()); @endphp
        <div class="card p-4 space-y-3">
            <div class="flex items-center justify-between gap-2">
                <p class="font-bold text-slate-800 dark:text-slate-100">{{ $l->perangkat }}</p>
                <span class="badge {{ $files->isNotEmpty() ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-slate-100 text-slate-500 dark:bg-slate-700' }}">{{ $files->count() }} file</span>
            </div>

            <form method="POST" action="{{ route('perangkat.upload', [$guru, $l]) }}" enctype="multipart/form-data" x-data="{ fileName: '' }" class="space-y-2">
                @csrf
                <label class="flex items-center gap-3 px-3 py-2.5 rounded-xl border-2 border-dashed border-slate-200 dark:border-slate-600 cursor-pointer hover:border-primary hover:bg-primary-50 dark:hover:bg-primary-900/10 transition group">
                    <div class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-700 group-hover:bg-primary/10 grid place-items-center flex-shrink-0 transition">
                        <i data-lucide="file-up" class="w-4 h-4 text-slate-400 group-hover:text-primary transition"></i>
                    </div>
                    <span class="text-xs truncate flex-1" :class="fileName ? 'text-slate-700 dark:text-slate-200 font-semibold' : 'text-slate-400'" x-text="fileName || 'Klik untuk pilih file PDF (maks 20MB)'"></span>
                    <input type="file" name="file" accept="application/pdf" required class="hidden" @change="fileName = $event.target.files[0]?.name ?? ''">
                </label>
                <button type="submit" x-show="fileName" x-cloak class="w-full py-2 rounded-lg text-xs font-bold text-white flex items-center justify-center gap-1.5" style="background:var(--cp)">
                    <i data-lucide="upload" class="w-3.5 h-3.5"></i> Upload
                </button>
            </form>

            @if($files->isNotEmpty())
            <div class="space-y-1.5 pt-1 border-t border-slate-100 dark:border-slate-700">
                @foreach($files as $f)
                <div class="flex items-center gap-2 px-2.5 py-2 rounded-lg bg-slate-50 dark:bg-slate-800/60">
                    <i data-lucide="file-text" class="w-4 h-4 text-rose-500 flex-shrink-0"></i>
                    <a href="{{ route('perangkat.preview', $f) }}" target="_blank" class="text-xs text-slate-600 dark:text-slate-300 truncate flex-1 hover:text-primary hover:underline" title="{{ $f->nama_asli }} — klik untuk lihat">{{ $f->nama_asli }}</a>
                    <span class="text-[10px] text-slate-400 flex-shrink-0">{{ $f->created_at->isoFormat('D MMM Y') }}</span>
                    <a href="{{ route('perangkat.preview', $f) }}" target="_blank" class="p-1 rounded text-slate-400 hover:text-primary flex-shrink-0" title="Lihat"><i data-lucide="eye" class="w-3.5 h-3.5"></i></a>
                    <a href="{{ route('perangkat.download', $f) }}" class="p-1 rounded text-slate-400 hover:text-primary flex-shrink-0" title="Unduh"><i data-lucide="download" class="w-3.5 h-3.5"></i></a>
                    <form method="POST" action="{{ route('perangkat.file.destroy', $f) }}" onsubmit="return confirmDelete(this)" class="flex-shrink-0">
                        @csrf @method('DELETE')
                        <button class="p-1 rounded text-slate-400 hover:text-rose-600" title="Hapus"><i data-lucide="trash-2" class="w-3.5 h-3.5"></i></button>
                    </form>
                </div>
                @endforeach
            </div>
            @endif
        </div>
        @endforeach
    </div>
    @endif
</div>
@endsection
