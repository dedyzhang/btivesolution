@extends('layouts.app')
@section('title', 'Atur VA & Nominal — ' . $kelas->nama_lengkap)

@section('content')
<div x-data="vaStudio()" class="space-y-5 max-w-4xl mx-auto">

    {{-- Header --}}
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <nav class="text-xs text-slate-400 mb-1">
                <a href="{{ route('keuangan.index', ['ta'=>$ta]) }}" class="hover:underline">Keuangan</a> /
                <a href="{{ route('keuangan.kelas', ['kelas'=>$kelas->uuid,'ta'=>$ta]) }}" class="hover:underline">{{ $kelas->nama_lengkap }}</a> / Atur VA &amp; Nominal
            </nav>
            <h1 class="page-title">Atur VA &amp; Nominal SPP</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">{{ $kelas->nama_lengkap }} · {{ $siswaList->count() }} siswa · T.A. {{ $ta }}</p>
        </div>
        <a href="{{ route('keuangan.kelas', ['kelas'=>$kelas->uuid,'ta'=>$ta]) }}" class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700">
            <i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali ke grid
        </a>
    </div>

    <div class="card p-4 text-xs text-slate-500 dark:text-slate-400 flex gap-2">
        <i data-lucide="info" class="w-4 h-4 flex-shrink-0 mt-0.5"></i>
        <p><b>VA</b> = nomor Virtual Account siswa (muncul di halaman bayar orang tua). <b>Nominal SPP/bulan</b> jadi tagihan default tiap bulan. Centang "terapkan" untuk memperbarui nominal bulan-bulan yang <b>belum dibayar</b> dengan nilai baru.</p>
    </div>

    <form method="POST" action="{{ route('keuangan.kelas.pengaturan.simpan', ['kelas'=>$kelas->uuid]) }}" class="space-y-4">
        @csrf
        <input type="hidden" name="ta" value="{{ $ta }}">

        {{-- Isi semua nominal sekaligus --}}
        <div class="card p-4 flex flex-wrap items-end gap-3">
            <div>
                <label class="form-label">Isi semua nominal sekaligus</label>
                <div class="relative">
                    <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-xs text-slate-400">Rp</span>
                    <input type="number" min="0" x-model.number="isiSemua" placeholder="mis. 150000" class="form-input text-sm !pl-8 w-44">
                </div>
            </div>
            <button type="button" @click="terapkanSemua()" class="flex items-center gap-1.5 px-4 py-2.5 rounded-xl text-sm font-semibold border border-primary/40 text-primary hover:bg-primary/5">
                <i data-lucide="copy-check" class="w-4 h-4"></i> Terapkan ke semua siswa
            </button>
        </div>

        <div class="card p-0 overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-700">
                        <th class="px-4 py-3 font-semibold">Siswa</th>
                        <th class="px-4 py-3 font-semibold w-56">Virtual Account (VA)</th>
                        <th class="px-4 py-3 font-semibold w-48">Nominal SPP / bulan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($siswaList as $s)
                    <tr class="border-b border-slate-100 dark:border-slate-700/60">
                        <td class="px-4 py-2.5">
                            <p class="font-medium text-slate-700 dark:text-slate-200">{{ $s->nama }}</p>
                            <p class="text-[11px] text-slate-400">NIS {{ $s->nis }}</p>
                        </td>
                        <td class="px-4 py-2.5">
                            <input type="text" name="va[{{ $s->uuid }}]" value="{{ $s->va }}" maxlength="60" placeholder="mis. 8810{{ $s->nis }}" class="form-input text-sm font-mono">
                        </td>
                        <td class="px-4 py-2.5">
                            <div class="relative">
                                <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-xs text-slate-400">Rp</span>
                                <input type="number" min="0" name="spp[{{ $s->uuid }}]" value="{{ (int) preg_replace('/\D/', '', (string) $s->spp) ?: '' }}" data-spp class="form-input text-sm !pl-8">
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="3" class="px-4 py-8 text-center text-slate-400">Belum ada siswa di kelas ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3">
            <label class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300 cursor-pointer">
                <input type="checkbox" name="terapkan" value="1" checked class="rounded text-primary">
                Perbarui nominal bulan yang <b>belum dibayar</b> dengan nominal baru
            </label>
            <button type="submit" class="btn-primary flex items-center gap-2 px-6 py-2.5 rounded-xl text-sm font-bold shadow-sm">
                <i data-lucide="save" class="w-4 h-4"></i> Simpan
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function vaStudio() {
    return {
        isiSemua: '',
        terapkanSemua() {
            const val = this.isiSemua;
            if (val === '' || val === null) return;
            document.querySelectorAll('input[data-spp]').forEach(el => { el.value = val; });
        },
    }
}
</script>
@endpush
