@if($terkunci ?? false)
<div class="card p-3.5 flex items-center gap-2.5 bg-amber-50 dark:bg-amber-900/15 border border-amber-200 dark:border-amber-700/40 text-amber-700 dark:text-amber-300 mb-4">
    <i data-lucide="lock" class="w-5 h-5 flex-shrink-0"></i>
    <p class="text-sm"><b>Nilai sudah dikonfirmasi & terkunci.</b> Untuk mengubah, batalkan konfirmasi di halaman Rapor (oleh admin).</p>
</div>
@elseif($readOnly ?? false)
<div class="card p-3.5 flex items-center gap-2.5 bg-sky-50 dark:bg-sky-900/15 border border-sky-200 dark:border-sky-700/40 text-sky-700 dark:text-sky-300 mb-4">
    <i data-lucide="eye" class="w-5 h-5 flex-shrink-0"></i>
    <p class="text-sm"><b>Mode lihat saja.</b> Anda melihat nilai ini sebagai wali kelas — hanya guru mapel & admin yang bisa mengubahnya.</p>
</div>
@endif
