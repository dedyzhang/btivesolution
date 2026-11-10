@extends('layouts.app')
@section('title', 'P3 Siswa')

@section('content')
<div class="space-y-5">
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h1 class="page-title">P3 Siswa</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Jumlah catatan Pelanggaran/Prestasi/Partisipasi tiap siswa</p>
        </div>
        @if(in_array(auth()->user()->access, ['superadmin', 'admin', 'kesiswaan']))
        <div class="flex items-center gap-2">
            <a href="{{ route('p3.temp.index') }}" class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition"><i data-lucide="inbox" class="w-4 h-4"></i> Pengajuan</a>
            <a href="{{ route('p3.index') }}" class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition"><i data-lucide="list-checks" class="w-4 h-4"></i> Master Kategori</a>
        </div>
        @endif
    </div>

    {{-- Search --}}
    <form method="GET" class="card p-4 flex flex-wrap gap-2">
        @if(request('sort'))<input type="hidden" name="sort" value="{{ request('sort') }}">@endif
        @if(request('dir'))<input type="hidden" name="dir" value="{{ request('dir') }}">@endif
        <div class="relative flex-1 min-w-48">
            <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau NIS..." class="form-input pl-9 py-2 text-sm">
        </div>
        <button type="submit" class="px-4 py-2 rounded-xl text-sm font-medium border border-slate-200 dark:border-slate-600 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 transition">Cari</button>
        @if(request('search'))
        <a href="{{ route('p3.siswa.index') }}" class="px-4 py-2 rounded-xl text-sm text-slate-500 hover:text-slate-700 transition">Reset</a>
        @endif
    </form>

    @if($siswas->isEmpty())
    <div class="card p-12 text-center text-slate-400">
        <i data-lucide="users" class="w-12 h-12 mx-auto mb-3 opacity-30"></i>
        <p class="font-medium">{{ request('search') ? 'Tidak ada siswa yang cocok dengan pencarian.' : 'Belum ada siswa.' }}</p>
    </div>
    @else
    <div class="card overflow-hidden">
        <div class="table-responsive">
            <table class="data-table w-full">
                <thead>
                    <tr>
                        <th class="w-10">#</th>
                        <x-sortable-th field="nis" label="NIS" class="w-32" />
                        <x-sortable-th field="nama" label="Nama" />
                        <x-sortable-th field="kelas" label="Kelas" class="hide-mobile w-20" />
                        <x-sortable-th field="prestasi" label="Prestasi" align="center" class="w-24" />
                        <x-sortable-th field="partisipasi" label="Partisipasi" align="center" class="w-28" />
                        <x-sortable-th field="pelanggaran" label="Pelanggaran" align="center" class="w-28" />
                        <th class="text-right w-20">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($siswas as $i => $s)
                    @php $c = $countMap[$s->uuid] ?? []; @endphp
                    <tr>
                        <td class="text-slate-400 text-xs">{{ $siswas->firstItem() + $i }}</td>
                        <td class="font-mono text-sm text-slate-500">{{ $s->nis }}</td>
                        <td class="font-semibold text-slate-800 dark:text-slate-200">{{ $s->nama }}</td>
                        <td class="hide-mobile text-slate-500">{{ $s->kelas ? $s->kelas->tingkat.$s->kelas->kelas : '-' }}</td>
                        <td class="text-center font-semibold text-emerald-600">{{ $c['prestasi'] ?? 0 }}</td>
                        <td class="text-center font-semibold text-blue-600">{{ $c['partisipasi'] ?? 0 }}</td>
                        <td class="text-center font-semibold text-rose-600">{{ $c['pelanggaran'] ?? 0 }}</td>
                        <td class="text-right">
                            <a href="{{ route('p3.siswa.show', $s) }}" class="p-1.5 rounded-lg border border-slate-200 dark:border-slate-600 text-slate-400 hover:text-primary inline-flex"><i data-lucide="eye" class="w-3.5 h-3.5"></i></a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-100 dark:border-slate-700">{{ $siswas->links() }}</div>
    </div>
    @endif
</div>
@endsection
