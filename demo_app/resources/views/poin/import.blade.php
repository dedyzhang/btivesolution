@extends('layouts.app')
@section('title', 'Import Aturan Poin')

@section('content')
<div class="max-w-xl mx-auto space-y-5">
    <div class="flex items-center gap-3">
        <a href="{{ route('poin.index') }}" class="grid place-items-center w-10 h-10 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-500 hover:text-primary hover:border-primary transition">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </a>
        <div>
            <h1 class="page-title">Import Aturan Poin</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Ubah banyak aturan sekaligus lewat Excel</p>
        </div>
    </div>

    <div class="card p-6 space-y-5">
        <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-xl p-4 text-sm text-amber-800 dark:text-amber-300">
            <p class="font-semibold mb-2 flex items-center gap-1.5"><i data-lucide="info" class="w-4 h-4"></i> Cara Import</p>
            <ol class="space-y-1 list-decimal list-inside text-xs">
                <li>Klik <strong>Export Excel</strong> untuk mengunduh data aturan saat ini</li>
                <li>Ubah kolom <strong>Kode / Jenis / Aturan / Poin</strong> sesuai kebutuhan (Kode baru = data baru, Kode yang sudah ada = diperbarui)</li>
                <li>Jangan mengubah nama, urutan, atau menghapus kolom — file akan ditolak jika strukturnya berubah</li>
                <li>Upload kembali file .xlsx tersebut di bawah ini</li>
            </ol>
            <p class="text-xs mt-2 flex items-center gap-1.5"><i data-lucide="shield-check" class="w-3.5 h-3.5 flex-shrink-0"></i> Sistem hanya menerima file hasil export dari halaman ini — file Excel buatan sendiri akan otomatis ditolak.</p>
        </div>

        <a href="{{ route('poin.export') }}"
           class="flex items-center gap-3 p-4 rounded-xl border-2 border-dashed border-emerald-300 dark:border-emerald-700 hover:border-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 transition text-emerald-700 dark:text-emerald-400">
            <div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-900 grid place-items-center flex-shrink-0">
                <i data-lucide="download" class="w-5 h-5"></i>
            </div>
            <div>
                <p class="font-semibold text-sm">Export Excel</p>
                <p class="text-xs opacity-70">Unduh data aturan saat ini sebagai file resmi</p>
            </div>
        </a>

        <form method="POST" action="{{ route('poin.import') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div>
                <label class="form-label">Upload File Excel Hasil Export</label>
                <label class="flex flex-col items-center gap-2 p-8 border-2 border-dashed border-slate-200 dark:border-slate-600 rounded-xl cursor-pointer hover:border-primary hover:bg-primary-50 dark:hover:bg-primary-900/10 transition">
                    <i data-lucide="file-spreadsheet" class="w-10 h-10 text-slate-400"></i>
                    <span class="text-sm text-slate-500">Klik untuk memilih file .xlsx / .xls</span>
                    <span id="selectedFile" class="text-sm font-semibold text-primary hidden"></span>
                    <input type="file" name="file" accept=".xlsx,.xls" required class="hidden"
                           onchange="document.getElementById('selectedFile').textContent = this.files[0]?.name; document.getElementById('selectedFile').classList.remove('hidden')">
                </label>
            </div>
            <button type="submit" class="btn-primary w-full py-3 rounded-xl text-sm font-bold flex items-center justify-center gap-2">
                <i data-lucide="upload" class="w-4 h-4"></i> Upload &amp; Import Aturan
            </button>
        </form>
    </div>
</div>
@endsection
