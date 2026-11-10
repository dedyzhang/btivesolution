@extends('layouts.app')
@section('title', 'Edit Aturan')

@section('content')
<div class="max-w-2xl mx-auto space-y-4">
    <a href="{{ route('poin.index') }}" class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-primary"><i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali</a>
    <div>
        <h1 class="page-title">Edit Aturan</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">{{ $aturan->kode }}</p>
    </div>

    <form method="POST" action="{{ route('poin.update', $aturan) }}" class="card p-6 space-y-4">
        @csrf @method('PUT')
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="form-label">Kode Aturan</label>
                <input type="text" name="kode" value="{{ old('kode', $aturan->kode) }}" class="form-input" required>
            </div>
            <div>
                <label class="form-label">Jenis</label>
                <select name="jenis" class="form-select" required>
                    @foreach(\App\Models\Aturan::JENIS as $val => $lbl)
                    <option value="{{ $val }}" @selected(old('jenis', $aturan->jenis)===$val)>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div>
            <label class="form-label">Aturan</label>
            <textarea name="aturan" rows="3" class="form-input" required>{{ old('aturan', $aturan->aturan) }}</textarea>
        </div>
        <div>
            <label class="form-label">Poin</label>
            <input type="number" name="poin" value="{{ old('poin', $aturan->poin) }}" min="0" class="form-input" required>
        </div>
        <button type="submit" class="btn-primary px-6 py-2.5 rounded-xl text-sm font-bold flex items-center gap-2"><i data-lucide="save" class="w-4 h-4"></i> Simpan Perubahan</button>
    </form>
</div>
@endsection
