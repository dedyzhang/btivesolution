<div class="card p-5 h-full flex flex-col">
    <div class="flex items-center justify-between mb-4 flex-shrink-0">
        <h3 class="font-bold text-slate-700 dark:text-slate-100 flex items-center gap-2"><i data-lucide="calendar-clock" class="w-4 h-4 text-primary"></i> Jadwal Hari Ini</h3>
        <span class="text-xs text-slate-400">{{ \App\Models\Jadwal::HARI[$hariIni] ?? 'Minggu' }}</span>
    </div>
    @if($jadwals->isEmpty())
    <div class="flex-1 flex flex-col items-center justify-center text-center text-slate-400">
        <i data-lucide="coffee" class="w-8 h-8 mx-auto mb-2 opacity-40"></i>
        <p class="text-sm font-medium">Tidak ada jadwal pelajaran hari ini.</p>
    </div>
    @else
    <div class="space-y-2 overflow-y-auto pr-1 flex-1">
        @foreach($jadwals as $j)
        <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50">
            <div class="w-14 text-center flex-shrink-0">
                <p class="text-xs font-bold text-primary">{{ substr($j->jam_mulai, 0, 5) }}</p>
                <p class="text-[10px] text-slate-400">{{ substr($j->jam_selesai, 0, 5) }}</p>
            </div>
            <div class="flex-1 min-w-0">
                <p class="font-semibold text-sm text-slate-800 dark:text-slate-100 truncate">{{ $j->pelajaran?->nama }}</p>
                <p class="text-xs text-slate-400 truncate">{{ $j->guru?->nama }}</p>
            </div>
            <span class="badge bg-primary/10 text-primary flex-shrink-0">JP {{ $j->jam_ke }}</span>
        </div>
        @endforeach
    </div>
    @endif
</div>
