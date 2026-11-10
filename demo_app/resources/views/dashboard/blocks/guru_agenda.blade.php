@php
    $guruAgdGuru = auth()->user()->guru;
    $guruAgdSlot = 0;
    $guruAgdTerisi = 0;
    if ($guruAgdGuru) {
        $guruAgdHari = (int) now()->isoWeekday();
        if ($guruAgdHari <= 6) {
            $guruAgdSlotKeys = \App\Models\Jadwal::where('id_guru', $guruAgdGuru->uuid)->where('hari', $guruAgdHari)
                ->whereNotNull('id_pelajaran')->get()
                ->map(fn ($j) => $j->id_kelas . '|' . $j->id_pelajaran)->unique();
            $guruAgdSlot = $guruAgdSlotKeys->count();
            $guruAgdTerisiKeys = \App\Models\Agenda::where('id_guru', $guruAgdGuru->uuid)
                ->whereDate('tanggal', now()->toDateString())->get()
                ->map(fn ($a) => $a->id_kelas . '|' . $a->id_pelajaran)->unique();
            $guruAgdTerisi = $guruAgdSlotKeys->intersect($guruAgdTerisiKeys)->count();
        }
    }
    $guruAgdBelum = max(0, $guruAgdSlot - $guruAgdTerisi);
@endphp
<div class="card p-5 h-full flex flex-col justify-center">
    <p class="text-xs text-slate-400 font-semibold mb-2 flex items-center gap-1.5"><i data-lucide="clipboard-pen-line" class="w-3.5 h-3.5 text-primary"></i> Agenda Hari Ini</p>
    <p class="text-2xl font-extrabold {{ $guruAgdBelum > 0 ? 'text-amber-600' : 'text-emerald-600' }}">{{ $guruAgdTerisi }} <span class="text-sm font-semibold text-slate-400">/ {{ $guruAgdSlot }}</span></p>
    <p class="text-xs text-slate-400 mt-1">slot terisi</p>
    @if($guruAgdBelum > 0)
    <a href="{{ route('agenda.index') }}" class="text-xs font-semibold text-amber-600 hover:underline mt-2 flex items-center gap-1">Isi {{ $guruAgdBelum }} agenda lagi <i data-lucide="arrow-right" class="w-3 h-3"></i></a>
    @elseif($guruAgdSlot > 0)
    <a href="{{ route('agenda.index') }}" class="text-xs font-semibold text-primary hover:underline mt-2 flex items-center gap-1">Lihat agenda <i data-lucide="arrow-right" class="w-3 h-3"></i></a>
    @endif
</div>
