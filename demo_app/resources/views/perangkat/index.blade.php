@extends('layouts.app')
@section('title', 'Perangkat Ajar')

@section('content')
<div class="space-y-5" x-data="{ editOpen:false, ed:{ uuid:'', perangkat:'' } }">
    <div>
        <h1 class="page-title">Perangkat Ajar</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Pantau dokumen perangkat mengajar (RPP, Modul Ajar, dst) yang diupload tiap guru.</p>
    </div>

    {{-- Master jenis dokumen --}}
    <div class="card p-5 space-y-4">
        <h2 class="font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2"><i data-lucide="list-checks" class="w-[18px] h-[18px] text-primary"></i> Jenis Dokumen</h2>

        <form method="POST" action="{{ route('perangkat.jenis.store') }}" class="flex flex-wrap gap-2 items-end">
            @csrf
            <div class="flex-1 min-w-48">
                <label class="form-label">Nama Jenis Dokumen</label>
                <input type="text" name="perangkat" required placeholder="mis. RPP, Modul Ajar, Prota" class="form-input">
            </div>
            <button type="submit" class="btn-primary px-4 py-2.5 rounded-xl text-sm font-semibold flex items-center gap-2"><i data-lucide="plus" class="w-4 h-4"></i> Tambah</button>
        </form>

        @if($list->isEmpty())
        <p class="text-sm text-slate-400 text-center py-4">Belum ada jenis dokumen. Tambahkan dulu di atas (mis. "RPP").</p>
        @else
        <div class="flex flex-wrap gap-2">
            @foreach($list as $l)
            <div class="flex items-center gap-1.5 pl-3 pr-1.5 py-1.5 rounded-full border border-slate-200 dark:border-slate-600 text-sm">
                <span class="text-slate-700 dark:text-slate-200">{{ $l->perangkat }}</span>
                <button type="button" @click="editOpen=true; ed={ uuid:'{{ $l->uuid }}', perangkat:'{{ addslashes($l->perangkat) }}' }" class="p-1 rounded-full text-slate-400 hover:text-primary hover:bg-slate-100 dark:hover:bg-slate-700"><i data-lucide="pencil" class="w-3 h-3"></i></button>
                <form method="POST" action="{{ route('perangkat.jenis.destroy', $l) }}" onsubmit="return confirmDelete(this)">
                    @csrf @method('DELETE')
                    <button class="p-1 rounded-full text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-900/30"><i data-lucide="trash-2" class="w-3 h-3"></i></button>
                </form>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Modal edit jenis --}}
    <template x-teleport="body">
        <div x-show="editOpen" x-cloak class="modal-backdrop" x-transition @click.self="editOpen=false">
            <div class="modal-box max-w-sm w-full" @click.stop>
                <div class="p-5 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
                    <h3 class="font-bold text-slate-800 dark:text-slate-200">Ubah Jenis Dokumen</h3>
                    <button @click="editOpen=false" class="p-1 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-400"><i data-lucide="x" class="w-4 h-4"></i></button>
                </div>
                <form method="POST" :action="'{{ url('perangkat-ajar/jenis') }}/' + ed.uuid" class="p-5 space-y-4">
                    @csrf @method('PUT')
                    <div>
                        <label class="form-label">Nama Jenis Dokumen</label>
                        <input type="text" name="perangkat" x-model="ed.perangkat" required class="form-input">
                    </div>
                    <button type="submit" class="btn-primary w-full py-2.5 rounded-xl text-sm font-semibold">Simpan</button>
                </form>
            </div>
        </div>
    </template>

    {{-- Grid monitoring guru --}}
    <div>
        <h2 class="font-bold text-slate-800 dark:text-slate-100 mb-3 flex items-center gap-2"><i data-lucide="users" class="w-[18px] h-[18px] text-primary"></i> Perangkat Ajar per Guru</h2>
        @if($guruList->isEmpty())
        <div class="card p-12 text-center text-slate-400">
            <i data-lucide="user-x" class="w-12 h-12 mx-auto mb-3 opacity-30"></i>
            <p class="font-medium">Belum ada data guru.</p>
        </div>
        @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($guruList as $g)
            <a href="{{ route('perangkat.show', $g) }}" class="card p-4 flex items-center gap-3 hover:border-primary transition">
                <div class="w-11 h-11 rounded-xl bg-primary/10 text-primary flex items-center justify-center font-bold flex-shrink-0">{{ mb_substr($g->nama, 0, 1) }}</div>
                <div class="min-w-0 flex-1">
                    <p class="font-semibold text-slate-800 dark:text-slate-100 truncate">{{ $g->nama }}</p>
                    <p class="text-xs text-slate-400 mt-0.5">{{ $g->perangkat_uploads_count }} file terupload</p>
                </div>
                <i data-lucide="chevron-right" class="w-4 h-4 text-slate-300 flex-shrink-0"></i>
            </a>
            @endforeach
        </div>
        @endif
    </div>
</div>
@endsection
