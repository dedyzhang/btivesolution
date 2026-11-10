@extends('layouts.app')
@section('title', 'Ajukan P3 — '.$siswa->nama)

@section('content')
<div class="max-w-xl mx-auto space-y-4" x-data="p3Form(@js(route('p3.kategori.json')))" x-init="init()">
    <a href="{{ route('p3.guru.index') }}" class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-primary"><i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali</a>
    <div>
        <h1 class="page-title">Ajukan P3</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">{{ $siswa->nama }} &bull; Kelas {{ $siswa->kelas ? $siswa->kelas->tingkat.$siswa->kelas->kelas : '-' }}</p>
    </div>

    <div class="rounded-xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 text-amber-700 dark:text-amber-300 px-4 py-3 text-sm flex items-start gap-2">
        <i data-lucide="info" class="w-4 h-4 flex-shrink-0 mt-0.5"></i>
        <span>Pengajuan ini akan masuk daftar menunggu persetujuan kesiswaan sebelum tercatat resmi.</span>
    </div>

    <form method="POST" action="{{ route('p3.guru.store', $siswa) }}" class="card p-6 space-y-4">
        @csrf
        <div>
            <label class="form-label">Tanggal</label>
            <input type="date" name="tanggal" value="{{ old('tanggal', now()->toDateString()) }}" class="form-input" required>
        </div>
        <div>
            <label class="form-label">Jenis</label>
            <select name="jenis" class="form-select" x-model="jenis" @change="loadKategori()" required>
                <option value="">— Pilih —</option>
                @foreach(\App\Models\P3Kategori::JENIS as $val => $lbl)
                <option value="{{ $val }}">{{ $lbl }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Kategori <span class="text-slate-400 font-normal">(otomatis isi deskripsi &amp; poin, tetap bisa diedit)</span></label>
            <select class="form-select" @change="pilihKategori($event.target.value)">
                <option value="">— Pilih jenis dulu —</option>
                <template x-for="k in kategoris" :key="k.uuid">
                    <option :value="k.uuid" x-text="k.deskripsi + ' (' + k.poin + ' poin)'"></option>
                </template>
            </select>
        </div>
        <div>
            <label class="form-label">Deskripsi</label>
            <textarea name="deskripsi" x-model="deskripsi" rows="2" class="form-input" required></textarea>
        </div>
        <div>
            <label class="form-label">Poin</label>
            <input type="number" name="poin" x-model="poin" min="0" class="form-input" required>
        </div>
        <button type="submit" class="btn-primary px-6 py-2.5 rounded-xl text-sm font-bold flex items-center gap-2"><i data-lucide="send" class="w-4 h-4"></i> Ajukan</button>
    </form>
</div>
@endsection

@push('scripts')
<script>
function p3Form(url) {
    return {
        jenis: '', kategoris: [], deskripsi: '', poin: 0,
        init() {},
        async loadKategori() {
            this.kategoris = [];
            if (!this.jenis) return;
            try {
                const res = await fetch(url + '?jenis=' + this.jenis);
                const data = await res.json();
                this.kategoris = data.kategoris || [];
            } catch (e) {}
        },
        pilihKategori(uuid) {
            const k = this.kategoris.find(x => x.uuid === uuid);
            if (k) { this.deskripsi = k.deskripsi; this.poin = k.poin; }
        }
    };
}
</script>
@endpush
