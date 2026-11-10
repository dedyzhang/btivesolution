@extends('layouts.app')
@section('title', 'Kalender Absensi')

@section('content')
<div class="space-y-5" x-data="kalenderAbsensi(@js($state), @js(route('kalender.toggle')))">

    {{-- Header --}}
    <div>
        <h1 class="page-title">Kalender Absensi &amp; Agenda</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Atur tanggal mana siswa boleh absen, guru wajib mengisi agenda, dan siswa wajib isi 7 KAIH sebelum absen.</p>
    </div>

    {{-- Master toggle penegakan --}}
    <form method="POST" action="{{ route('kalender.mode') }}" class="card p-5 grid sm:grid-cols-3 gap-4"
          x-data="{ a: {{ $absenAktif ? 'true':'false' }}, b: {{ $agendaAktif ? 'true':'false' }}, c: {{ $kaihAktif ? 'true':'false' }} }">
        @csrf
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
                <p class="font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2"><i data-lucide="user-check" class="w-4 h-4 text-emerald-500"></i> Batasi Absen Siswa</p>
                <p class="text-xs text-slate-400 mt-1 leading-relaxed">Jika aktif, siswa hanya bisa absen pada tanggal yang ditandai di kalender. Jika nonaktif, siswa bisa absen kapan saja.</p>
                <p class="text-xs mt-1.5 font-semibold" :class="a ? 'text-emerald-600 dark:text-emerald-400':'text-slate-400'" x-text="a ? '● Dibatasi kalender':'○ Bebas'"></p>
            </div>
            <label class="relative inline-flex items-center cursor-pointer flex-shrink-0 mt-1">
                <input type="checkbox" name="kalender_absen_aktif" value="1" class="sr-only peer" x-model="a">
                <div class="relative w-11 h-6 bg-slate-200 dark:bg-slate-600 rounded-full peer-checked:bg-[color:var(--cp)] transition after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-5 after:w-5 after:transition peer-checked:after:translate-x-5"></div>
            </label>
        </div>
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
                <p class="font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2"><i data-lucide="clipboard-pen-line" class="w-4 h-4 text-amber-500"></i> Batasi Wajib Agenda</p>
                <p class="text-xs text-slate-400 mt-1 leading-relaxed">Jika aktif, guru hanya wajib mengisi agenda pada tanggal yang ditandai. Jika nonaktif, agenda wajib di semua hari mengajar.</p>
                <p class="text-xs mt-1.5 font-semibold" :class="b ? 'text-amber-600 dark:text-amber-400':'text-slate-400'" x-text="b ? '● Dibatasi kalender':'○ Semua hari mengajar'"></p>
            </div>
            <label class="relative inline-flex items-center cursor-pointer flex-shrink-0 mt-1">
                <input type="checkbox" name="kalender_agenda_aktif" value="1" class="sr-only peer" x-model="b">
                <div class="relative w-11 h-6 bg-slate-200 dark:bg-slate-600 rounded-full peer-checked:bg-[color:var(--cp)] transition after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-5 after:w-5 after:transition peer-checked:after:translate-x-5"></div>
            </label>
        </div>
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
                <p class="font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2"><i data-lucide="list-checks" class="w-4 h-4 text-sky-500"></i> Batasi Wajib 7 KAIH</p>
                <p class="text-xs text-slate-400 mt-1 leading-relaxed">Jika aktif, siswa hanya wajib isi 7 KAIH sebelum absen pada tanggal yang ditandai. Jika nonaktif, ikut pengaturan global (halaman Soal 7 KAIH).</p>
                <p class="text-xs mt-1.5 font-semibold" :class="c ? 'text-sky-600 dark:text-sky-400':'text-slate-400'" x-text="c ? '● Dibatasi kalender':'○ Ikuti pengaturan global'"></p>
            </div>
            <label class="relative inline-flex items-center cursor-pointer flex-shrink-0 mt-1">
                <input type="checkbox" name="kalender_kaih_aktif" value="1" class="sr-only peer" x-model="c">
                <div class="relative w-11 h-6 bg-slate-200 dark:bg-slate-600 rounded-full peer-checked:bg-[color:var(--cp)] transition after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-5 after:w-5 after:transition peer-checked:after:translate-x-5"></div>
            </label>
        </div>
        <div class="sm:col-span-3">
            <button type="submit" class="btn-primary px-5 py-2.5 rounded-xl text-sm font-bold flex items-center gap-2"><i data-lucide="save" class="w-4 h-4"></i> Simpan Penegakan</button>
        </div>
    </form>

    {{-- Navigasi bulan + aksi massal --}}
    <div class="card p-4 flex items-center justify-between flex-wrap gap-3">
        <div class="flex items-center gap-2">
            <a href="{{ route('kalender.index', ['bulan' => $prev]) }}" class="w-9 h-9 grid place-items-center rounded-lg border border-slate-200 dark:border-slate-600 hover:bg-slate-50 dark:hover:bg-slate-700"><i data-lucide="chevron-left" class="w-4 h-4"></i></a>
            <span class="font-bold text-slate-800 dark:text-slate-100 min-w-36 text-center">{{ $label }}</span>
            <a href="{{ route('kalender.index', ['bulan' => $next]) }}" class="w-9 h-9 grid place-items-center rounded-lg border border-slate-200 dark:border-slate-600 hover:bg-slate-50 dark:hover:bg-slate-700"><i data-lucide="chevron-right" class="w-4 h-4"></i></a>
            <a href="{{ route('kalender.index') }}" class="ml-1 text-xs text-primary hover:underline">Bulan ini</a>
        </div>
        <div class="flex items-center gap-1.5 flex-wrap">
            <span class="text-xs text-slate-400 mr-1">Hari kerja:</span>
            @foreach([
                ['absen_siswa',1,'Absen ON'],
                ['absen_siswa',0,'Absen OFF'],
                ['agenda_guru',1,'Agenda ON'],
                ['agenda_guru',0,'Agenda OFF'],
                ['kaih_wajib',1,'7 KAIH ON'],
                ['kaih_wajib',0,'7 KAIH OFF'],
            ] as [$f,$v,$lbl])
            <form method="POST" action="{{ route('kalender.bulk') }}" class="inline">
                @csrf
                <input type="hidden" name="bulan" value="{{ $bulan }}">
                <input type="hidden" name="field" value="{{ $f }}">
                <input type="hidden" name="value" value="{{ $v }}">
                <button class="px-2.5 py-1.5 rounded-lg text-xs font-semibold border border-slate-200 dark:border-slate-600 hover:bg-slate-50 dark:hover:bg-slate-700">{{ $lbl }}</button>
            </form>
            @endforeach
        </div>
    </div>

    {{-- Legend --}}
    <div class="flex items-center gap-4 text-xs text-slate-400 flex-wrap px-1">
        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-emerald-500"></span> Siswa boleh absen</span>
        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-amber-500"></span> Guru wajib agenda</span>
        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-sky-500"></span> Wajib 7 KAIH sebelum absen</span>
        <span>Klik chip pada tanggal untuk mengaktifkan/menonaktifkan.</span>
    </div>

    {{-- Kalender --}}
    <div class="card p-3 sm:p-4">
        <div class="grid grid-cols-7 gap-1.5 mb-1.5 text-center text-[11px] font-bold text-slate-400">
            @foreach(['Sen','Sel','Rab','Kam','Jum','Sab','Min'] as $h)<div>{{ $h }}</div>@endforeach
        </div>
        <div class="space-y-1.5">
            @foreach($minggu as $week)
            <div class="grid grid-cols-7 gap-1.5">
                @foreach($week as $d)
                @if(!$d['inMonth'])
                <div class="rounded-xl bg-slate-50/50 dark:bg-slate-800/30 min-h-[104px]"></div>
                @else
                <div class="rounded-xl border p-1.5 min-h-[104px] flex flex-col gap-1 {{ $d['hari_ini'] ? 'border-primary' : 'border-slate-200 dark:border-slate-700' }} {{ $d['weekend'] ? 'bg-slate-50/60 dark:bg-slate-800/40' : '' }}">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold {{ $d['weekend'] ? 'text-rose-400' : 'text-slate-600 dark:text-slate-300' }}">{{ $d['day'] }}</span>
                        @if($d['hari_ini'])<span class="text-[9px] font-bold text-primary">Hari ini</span>@endif
                    </div>
                    <button type="button" @click="toggle('{{ $d['ymd'] }}','absen_siswa')"
                            :class="val('{{ $d['ymd'] }}','absen_siswa') ? 'bg-emerald-500 text-white border-emerald-500' : 'text-slate-400 border-slate-200 dark:border-slate-600'"
                            class="text-[10px] font-bold rounded-md border px-1 py-0.5 flex items-center justify-center gap-1 transition">
                        <i data-lucide="user-check" class="w-3 h-3"></i> Absen
                    </button>
                    <button type="button" @click="toggle('{{ $d['ymd'] }}','agenda_guru')"
                            :class="val('{{ $d['ymd'] }}','agenda_guru') ? 'bg-amber-500 text-white border-amber-500' : 'text-slate-400 border-slate-200 dark:border-slate-600'"
                            class="text-[10px] font-bold rounded-md border px-1 py-0.5 flex items-center justify-center gap-1 transition">
                        <i data-lucide="clipboard-pen-line" class="w-3 h-3"></i> Agenda
                    </button>
                    <button type="button" @click="toggle('{{ $d['ymd'] }}','kaih_wajib')"
                            :class="val('{{ $d['ymd'] }}','kaih_wajib') ? 'bg-sky-500 text-white border-sky-500' : 'text-slate-400 border-slate-200 dark:border-slate-600'"
                            class="text-[10px] font-bold rounded-md border px-1 py-0.5 flex items-center justify-center gap-1 transition">
                        <i data-lucide="list-checks" class="w-3 h-3"></i> 7 KAIH
                    </button>
                </div>
                @endif
                @endforeach
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function kalenderAbsensi(initial, toggleUrl){
    return {
        state: initial || {},
        busy: {},
        val(ymd, field){ return this.state[ymd] ? !!this.state[ymd][field] : false; },
        async toggle(ymd, field){
            const key = ymd + field;
            if(this.busy[key]) return;
            this.busy[key] = true;
            if(!this.state[ymd]) this.state[ymd] = { absen_siswa:false, agenda_guru:false, kaih_wajib:false };
            const newVal = !this.state[ymd][field];
            this.state[ymd][field] = newVal;   // optimistik
            try {
                const r = await fetch(toggleUrl, {
                    method:'POST',
                    headers:{ 'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').getAttribute('content') },
                    body: JSON.stringify({ tanggal: ymd, field: field, value: newVal })
                });
                if(!r.ok){ this.state[ymd][field] = !newVal; showToast('Gagal menyimpan','error'); }
            } catch(e){ this.state[ymd][field] = !newVal; showToast('Gagal terhubung ke server','error'); }
            this.busy[key] = false;
            this.$nextTick(()=> window.lucide && lucide.createIcons());
        }
    };
}
</script>
@endpush
