<div class="card p-5 h-full">
    <div class="flex items-center justify-between mb-4">
        <h3 class="font-bold text-slate-700 dark:text-slate-100 flex items-center gap-2"><i data-lucide="calendar-check" class="w-4 h-4 text-primary"></i> Absensi {{ auth()->user()->access === 'orangtua' ? $siswa->nama : 'Saya' }}</h3>
        @if($streakHadir >= 2)
        <span class="badge bg-orange-100 dark:bg-orange-900 text-orange-600 dark:text-orange-300 font-semibold flex items-center gap-1"><i data-lucide="flame" class="w-3 h-3"></i> {{ $streakHadir }} hari</span>
        @endif
    </div>

    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 mb-3">
        <div>
            <p class="text-xs text-slate-400">Status Hari Ini</p>
            @php $statusWarna = ['hadir' => 'emerald', 'izin' => 'amber', 'sakit' => 'blue', 'alpa' => 'rose']; @endphp
            @if($absensiHariIni)
            @php $sw = $statusWarna[$absensiHariIni->status] ?? 'slate'; @endphp
            <span class="badge bg-{{ $sw }}-100 dark:bg-{{ $sw }}-900 text-{{ $sw }}-700 dark:text-{{ $sw }}-300 font-semibold mt-1">{{ \App\Models\Absensi::STATUS[$absensiHariIni->status] ?? $absensiHariIni->status }}</span>
            @if($absensiHariIni->status === 'hadir' && $absensiHariIni->jam_masuk)
            <p class="text-xs text-slate-400 mt-1">Masuk {{ substr($absensiHariIni->jam_masuk, 0, 5) }}</p>
            @endif
            @else
            <span class="badge bg-slate-100 dark:bg-slate-700 text-slate-500 font-semibold mt-1">Belum Absen</span>
            @endif
        </div>
        @if($persenHadir !== null)
        <div class="text-right">
            <p class="text-2xl font-extrabold {{ $persenHadir >= 90 ? 'text-emerald-600' : ($persenHadir >= 75 ? 'text-amber-600' : 'text-rose-600') }}">{{ $persenHadir }}%</p>
            <p class="text-[10px] text-slate-400">Kehadiran</p>
        </div>
        @else
        <i data-lucide="user-check" class="w-8 h-8 text-slate-300"></i>
        @endif
    </div>

    @if($persenHadir !== null)
    <div class="w-full h-1.5 rounded-full bg-slate-100 dark:bg-slate-700 overflow-hidden mb-4">
        <div class="h-full rounded-full {{ $persenHadir >= 90 ? 'bg-emerald-500' : ($persenHadir >= 75 ? 'bg-amber-500' : 'bg-rose-500') }} transition-all duration-500" style="width: {{ $persenHadir }}%"></div>
    </div>
    @endif

    @php
        $totalTercatat = array_sum($rekapAbsensi);
        $pHadir = $totalTercatat > 0 ? round($rekapAbsensi['hadir'] / $totalTercatat * 100) : 0;
        $pIzin  = $totalTercatat > 0 ? round($rekapAbsensi['izin']  / $totalTercatat * 100) : 0;
        $pSakit = $totalTercatat > 0 ? round($rekapAbsensi['sakit'] / $totalTercatat * 100) : 0;
        $pAlpa  = $totalTercatat > 0 ? max(0, 100 - $pHadir - $pIzin - $pSakit) : 0;
    @endphp
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        {{-- Kalender mini bulan berjalan --}}
        <div>
            <p class="text-xs text-slate-400 font-semibold mb-2 capitalize">{{ now()->locale('id')->isoFormat('MMMM Y') }}</p>
            <div class="max-w-[260px]">
                <div class="grid grid-cols-7 gap-1 mb-1">
                    @foreach(['S', 'S', 'R', 'K', 'J', 'S', 'M'] as $h)
                    <div class="text-center text-[9px] font-bold text-slate-300 dark:text-slate-500">{{ $h }}</div>
                    @endforeach
                </div>
                <div class="grid grid-cols-7 gap-1">
                    @for($i = 0; $i < $offsetAwal; $i++)
                    <div></div>
                    @endfor
                    @php $warnaKal = ['hadir' => 'bg-emerald-500', 'izin' => 'bg-amber-500', 'sakit' => 'bg-blue-500', 'alpa' => 'bg-rose-500']; @endphp
                    @foreach($kalenderBulan as $d)
                    <div class="aspect-square rounded-md grid place-items-center text-[10px] font-semibold
                        {{ $d['status'] ? $warnaKal[$d['status']] . ' text-white' : ($d['isFuture'] ? 'text-slate-300 dark:text-slate-600' : ($d['isWeekend'] ? 'bg-slate-50 dark:bg-slate-800/50 text-slate-300 dark:text-slate-600' : 'bg-slate-100 dark:bg-slate-700/60 text-slate-400')) }}
                        {{ $d['isToday'] ? 'ring-2 ring-primary ring-offset-1 dark:ring-offset-slate-900' : '' }}"
                        title="{{ $d['tanggal']->isoFormat('D MMMM') }}{{ $d['status'] ? ' - ' . (\App\Models\Absensi::STATUS[$d['status']] ?? '') : '' }}">
                        {{ $d['tanggal']->day }}
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Pie chart distribusi kehadiran bulan ini --}}
        <div class="flex flex-col items-center">
            <p class="text-xs text-slate-400 font-semibold mb-2 self-start">Distribusi Bulan Ini</p>
            @if($totalTercatat > 0)
            <div class="relative w-[130px] h-[130px]">
                <svg viewBox="0 0 36 36" class="w-full h-full transform -rotate-90">
                    <circle cx="18" cy="18" r="15.9155" fill="none" stroke="currentColor" class="text-slate-100 dark:text-slate-800" stroke-width="2.5" />
                    <circle cx="18" cy="18" r="15.9155" fill="none" stroke="#10b981" stroke-width="3.2" stroke-dasharray="{{ $pHadir }} 100" stroke-dashoffset="0" stroke-linecap="round" class="transition-all duration-500" />
                    <circle cx="18" cy="18" r="15.9155" fill="none" stroke="#f59e0b" stroke-width="3.2" stroke-dasharray="{{ $pIzin }} 100" stroke-dashoffset="-{{ $pHadir }}" stroke-linecap="round" class="transition-all duration-500" />
                    <circle cx="18" cy="18" r="15.9155" fill="none" stroke="#3b82f6" stroke-width="3.2" stroke-dasharray="{{ $pSakit }} 100" stroke-dashoffset="-{{ $pHadir + $pIzin }}" stroke-linecap="round" class="transition-all duration-500" />
                    <circle cx="18" cy="18" r="15.9155" fill="none" stroke="#f43f5e" stroke-width="3.2" stroke-dasharray="{{ $pAlpa }} 100" stroke-dashoffset="-{{ $pHadir + $pIzin + $pSakit }}" stroke-linecap="round" class="transition-all duration-500" />
                </svg>
                <div class="absolute inset-0 flex flex-col items-center justify-center text-center">
                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">Total</span>
                    <span class="text-lg font-black text-slate-700 dark:text-slate-200 leading-none mt-0.5">{{ $totalTercatat }}</span>
                </div>
            </div>
            <div class="flex flex-col gap-1.5 mt-3 w-full max-w-[170px]">
                @foreach([['Hadir', 'bg-emerald-500', $rekapAbsensi['hadir']], ['Izin', 'bg-amber-500', $rekapAbsensi['izin']], ['Sakit', 'bg-blue-500', $rekapAbsensi['sakit']], ['Alpa', 'bg-rose-500', $rekapAbsensi['alpa']]] as [$label, $warna, $jumlah])
                <div class="flex items-center justify-between text-xs">
                    <span class="flex items-center gap-1.5 text-slate-500 dark:text-slate-400"><span class="w-2 h-2 rounded-full {{ $warna }}"></span>{{ $label }}</span>
                    <span class="font-bold text-slate-700 dark:text-slate-200">{{ $jumlah }}</span>
                </div>
                @endforeach
            </div>
            @else
            <div class="flex-1 flex items-center justify-center text-center text-slate-400 text-xs py-10">Belum ada data absensi bulan ini.</div>
            @endif
        </div>
    </div>
</div>
