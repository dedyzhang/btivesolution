@extends('layouts.app')
@section('title', 'Tambah Aturan')

@section('content')
<div class="max-w-2xl mx-auto space-y-4">
    <a href="{{ route('poin.index') }}" class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-primary"><i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali</a>
    <div>
        <h1 class="page-title">Tambah Aturan</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Aturan penambahan/pengurangan poin siswa</p>
    </div>

    <form method="POST" action="{{ route('poin.store') }}" class="card p-6 space-y-4">
        @csrf
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="form-label">Kode Aturan</label>
                <input type="text" name="kode" value="{{ old('kode') }}" class="form-input" placeholder="Mis. A-01" required>
            </div>
            <div>
                <label class="form-label">Jenis</label>
                <select name="jenis" class="form-select" required>
                    <option value="">— Pilih —</option>
                    @foreach(\App\Models\Aturan::JENIS as $val => $lbl)
                    <option value="{{ $val }}" @selected(old('jenis')===$val)>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div>
            <label class="form-label">Aturan</label>
            <textarea name="aturan" rows="3" class="form-input" placeholder="Deskripsi aturan/pelanggaran" required>{{ old('aturan') }}</textarea>
        </div>
        <div>
            <label class="form-label">Poin</label>
            <input type="number" name="poin" value="{{ old('poin', 0) }}" min="0" class="form-input" required>
        </div>
        <button type="submit" class="btn-primary px-6 py-2.5 rounded-xl text-sm font-bold flex items-center gap-2"><i data-lucide="save" class="w-4 h-4"></i> Simpan</button>
    </form>
</div>
@endsection
