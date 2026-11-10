@extends('layouts.app')
@section('title', 'Set Sekretaris Kelas')

@section('content')
<div class="max-w-xl mx-auto space-y-5">
    <a href="{{ route('walikelas.siswa.index') }}" class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-primary"><i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali</a>

    <div>
        <h1 class="page-title">Set Sekretaris Kelas</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Kelas {{ $kelas->tingkat }}{{ $kelas->kelas }} &bull; pilih maksimal 2 siswa untuk membantu mengajukan poin/P3</p>
    </div>

    <div class="rounded-xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 text-amber-700 dark:text-amber-300 px-4 py-3 text-sm flex items-start gap-2">
        <i data-lucide="info" class="w-4 h-4 flex-shrink-0 mt-0.5"></i>
        <span>Sekretaris kelas dapat mengajukan poin/P3 untuk teman sekelasnya, sama seperti guru. Pengajuan tetap menunggu persetujuan kesiswaan.</span>
    </div>

    <form method="POST" action="{{ route('walikelas.sekretaris.store') }}" class="card p-6 space-y-4">
        @csrf
        <div class="space-y-2 max-h-96 overflow-y-auto pr-1">
            @forelse($siswas as $s)
            <label class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800/50 cursor-pointer transition">
                <input type="checkbox" name="id_siswa[]" value="{{ $s->uuid }}" class="w-4 h-4 rounded" @checked(in_array($s->uuid, $sekretarisIds))>
                <div class="min-w-0 flex-1">
                    <p class="font-semibold text-sm text-slate-800 dark:text-slate-200 truncate">{{ $s->nama }}</p>
                    <p class="text-xs text-slate-400">NIS {{ $s->nis }}</p>
                </div>
            </label>
            @empty
            <p class="text-sm text-slate-400 text-center py-6">Belum ada siswa di kelas ini.</p>
            @endforelse
        </div>
        <button type="submit" class="btn-primary w-full py-3 rounded-xl text-sm font-bold flex items-center justify-center gap-2">
            <i data-lucide="save" class="w-4 h-4"></i> Simpan Sekretaris
        </button>
    </form>
</div>
@endsection
