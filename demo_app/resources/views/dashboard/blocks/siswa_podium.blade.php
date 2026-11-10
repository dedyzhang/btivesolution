<div class="card p-6 md:p-8 h-full relative overflow-hidden">
    <div class="absolute inset-0 opacity-5 pointer-events-none" style="background:radial-gradient(circle at 50% 0%, #f59e0b, transparent 60%)"></div>
    <h3 class="relative font-bold text-slate-700 dark:text-slate-100 flex items-center gap-2 mb-5"><i data-lucide="trophy" class="w-4 h-4 text-amber-500"></i> Papan Peringkat Sekolah</h3>
    @if($podium && $podium->isNotEmpty())
    <x-podium :items="$podium" compact />
    @else
    <p class="text-sm text-slate-400 text-center py-6">Belum ada data peringkat.</p>
    @endif
</div>
