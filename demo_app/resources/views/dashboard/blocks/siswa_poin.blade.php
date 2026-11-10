<div class="card p-5 h-full flex flex-col">
    <div class="flex items-center justify-between flex-wrap gap-4">
        <div>
            <h3 class="font-bold text-slate-700 dark:text-slate-100 flex items-center gap-2"><i data-lucide="shield-check" class="w-4 h-4 text-primary"></i> {{ $jenisAturan === 'poin' ? 'Poin Kedisiplinan' : 'P3 Kedisiplinan' }}</h3>
            <p class="text-xs text-slate-400 mt-0.5">{{ $jenisAturan === 'poin' ? 'Basis 100, dikurangi tiap pelanggaran' : 'Pelanggaran, Prestasi & Partisipasi' }}</p>
        </div>
        <a href="{{ route($jenisAturan === 'poin' ? 'poin.self' : 'p3.self') }}" class="text-xs font-semibold text-primary hover:underline flex items-center gap-1 flex-shrink-0">Lihat Detail <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i></a>
    </div>

    <div class="mt-4 flex-1 flex flex-col">
        @if($jenisAturan === 'poin')
        @php
            $sisa = $poin['sisa'];
            $warna = $sisa < 50 ? ['rose', '#f43f5e'] : ($sisa < 75 ? ['amber', '#f59e0b'] : ['emerald', '#10b981']);
            $ring = max(0, min(100, $sisa));
            $pesan = match (true) {
                $sisa >= 100 => 'Mantap, pertahankan sikap baikmu!',
                $sisa >= 75  => 'Kedisiplinanmu terjaga dengan baik.',
                $sisa >= 50  => 'Perhatikan sikapmu, jangan sampai poin terus berkurang.',
                default      => 'Segera perbaiki sikap — bicarakan dengan wali kelas jika perlu.',
            };
            $recent = array_reverse(array_slice($poin['ledger'], -3));
        @endphp
        <div class="flex items-center gap-5">
            <div class="relative w-24 h-24 flex-shrink-0">
                <svg viewBox="0 0 36 36" class="w-full h-full transform -rotate-90">
                    <circle cx="18" cy="18" r="15.9155" fill="none" stroke="currentColor" class="text-slate-100 dark:text-slate-800" stroke-width="3" />
                    <circle cx="18" cy="18" r="15.9155" fill="none" stroke="{{ $warna[1] }}" stroke-width="3.5" stroke-dasharray="{{ $ring }} 100" stroke-linecap="round" class="transition-all duration-500" />
                </svg>
                <div class="absolute inset-0 flex flex-col items-center justify-center text-center">
                    <span class="text-xl font-black text-{{ $warna[0] }}-600 dark:text-{{ $warna[0] }}-400 leading-none">{{ $sisa }}</span>
                    <span class="text-[9px] text-slate-400 mt-0.5">/ 100</span>
                </div>
            </div>
            <div class="min-w-0">
                <p class="text-xs text-slate-400">Sisa Poin</p>
                @if($poin['peringatan'] !== '-')
                <span class="badge bg-rose-100 dark:bg-rose-900 text-rose-600 dark:text-rose-300 font-semibold">{{ $poin['peringatan'] }}</span>
                @else
                <span class="badge bg-emerald-100 dark:bg-emerald-900 text-emerald-700 dark:text-emerald-300 font-semibold">Aman</span>
                @endif
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-2 leading-relaxed">{{ $pesan }}</p>
            </div>
        </div>

        <div class="mt-5 pt-4 border-t border-slate-100 dark:border-slate-700 flex-1">
            <p class="text-xs font-semibold text-slate-400 mb-2.5">Riwayat Terbaru</p>
            @if(count($recent))
            <div class="space-y-2">
                @foreach($recent as $l)
                @php $naik = $l['delta'] > 0; @endphp
                <div class="flex items-center gap-3 p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                    <span class="w-7 h-7 rounded-lg grid place-items-center flex-shrink-0 {{ $naik ? 'bg-emerald-100 dark:bg-emerald-900 text-emerald-600 dark:text-emerald-300' : 'bg-rose-100 dark:bg-rose-900 text-rose-600 dark:text-rose-300' }}">
                        <i data-lucide="{{ $naik ? 'plus' : 'minus' }}" class="w-3.5 h-3.5"></i>
                    </span>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-semibold text-slate-700 dark:text-slate-200 truncate">{{ $l['row']->aturan?->aturan ?? '-' }}</p>
                        <p class="text-[10px] text-slate-400">{{ $l['row']->tanggal->isoFormat('D MMM Y') }}</p>
                    </div>
                    <span class="text-xs font-bold flex-shrink-0 {{ $naik ? 'text-emerald-600' : 'text-rose-600' }}">{{ $naik ? '+' : '' }}{{ $l['delta'] }}</span>
                </div>
                @endforeach
            </div>
            @else
            <div class="flex flex-col items-center justify-center text-center text-slate-400 py-6">
                <i data-lucide="sparkles" class="w-6 h-6 mx-auto mb-2 opacity-40"></i>
                <p class="text-xs font-medium">Belum ada catatan poin. Pertahankan sikap baikmu!</p>
            </div>
            @endif
        </div>
        @else
        @php
            $p3Meta = ['prestasi' => ['Prestasi', 'emerald', 'award'], 'partisipasi' => ['Partisipasi', 'blue', 'handshake'], 'pelanggaran' => ['Pelanggaran', 'rose', 'triangle-alert']];
        @endphp
        <div class="grid grid-cols-3 gap-3">
            @foreach($p3Meta as $key => [$label, $w, $icon])
            <div class="text-center p-3 rounded-xl bg-{{ $w }}-50 dark:bg-{{ $w }}-900/20">
                <i data-lucide="{{ $icon }}" class="w-4 h-4 mx-auto text-{{ $w }}-600 dark:text-{{ $w }}-300 mb-1"></i>
                <p class="text-xl font-extrabold text-{{ $w }}-600 dark:text-{{ $w }}-300">{{ $poin[$key] }}</p>
                <p class="text-[11px] text-slate-400">{{ $label }}</p>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>
