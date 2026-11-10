@extends('layouts.app')
@section('title', 'Tambah Kategori P3')

@section('content')
<div class="max-w-2xl mx-auto space-y-4">
    <a href="{{ route('p3.index') }}" class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-primary"><i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali</a>
    <div>
        <h1 class="page-title">Tambah Kategori P3</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Kategori Pelanggaran/Prestasi/Partisipasi</p>
    </div>

    <form method="POST" action="{{ route('p3.store') }}" class="card p-6 space-y-4">
        @csrf
        <div>
            <label class="form-label">Jenis</label>
            <select name="jenis" class="form-select" required>
                <option value="">— Pilih —</option>
                @foreach(\App\Models\P3Kategori::JENIS as $val => $lbl)
                <option value="{{ $val }}" @selected(old('jenis')===$val)>{{ $lbl }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Deskripsi</label>
            <input type="text" name="deskripsi" value="{{ old('deskripsi') }}" class="form-input" placeholder="Mis. Juara 1 Olimpiade Sains" required>
        </div>
        <div>
            <label class="form-label">Poin</label>
            <input type="number" name="poin" value="{{ old('poin', 0) }}" min="0" class="form-input" required>
        </div>
        <button type="submit" class="btn-primary px-6 py-2.5 rounded-xl text-sm font-bold flex items-center gap-2"><i data-lucide="save" class="w-4 h-4"></i> Simpan</button>
    </form>
</div>
@endsection
