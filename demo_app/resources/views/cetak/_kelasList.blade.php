{{-- Partial daftar kelas + tombol unduh Excel. Variabel: $kelas, $routeExcel (nama route, terima 1 param uuid kelas). Slot opsional 'baris_atas' utk baris tambahan (mis. "Semua Siswa"). --}}
<div class="card overflow-hidden">
    <div class="px-4 py-3 border-b border-slate-100 dark:border-slate-700 text-sm font-semibold text-slate-700 dark:text-slate-200">
        Pilih Kelas
    </div>
    @if($kelas->isEmpty())
    <div class="p-12 text-center text-slate-400"><i data-lucide="school" class="w-12 h-12 mx-auto mb-3 opacity-30"></i><p class="font-medium">Belum ada data kelas.</p></div>
    @else
    <table class="data-table">
        <thead>
            <tr>
                <th class="text-center w-12">No</th>
                <th class="text-left">Kelas</th>
                <th class="text-center w-32">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($kelas as $i => $k)
            <tr>
                <td class="text-center text-slate-400">{{ $i + 1 }}</td>
                <td class="font-medium text-slate-700 dark:text-slate-200">Kelas {{ $k->tingkat }}{{ $k->kelas }}</td>
                <td class="text-center">
                    <a href="{{ route($routeExcel, $k->uuid) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition">
                        <i data-lucide="file-spreadsheet" class="w-3.5 h-3.5"></i> Unduh
                    </a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>
