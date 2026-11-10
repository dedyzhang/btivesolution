@extends('layouts.app')
@section('title', 'Data Siswa Kelas')

@section('content')
<div class="space-y-5">
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h1 class="page-title">Data Siswa Kelas</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Kelas {{ $kelas->tingkat }}{{ $kelas->kelas }} &bull; wali kelas Anda</p>
        </div>
        <a href="{{ route('walikelas.sekretaris.form') }}" class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition"><i data-lucide="user-cog" class="w-4 h-4"></i> Set Sekretaris</a>
    </div>

    <form method="GET" class="card p-4 flex flex-wrap gap-2">
        @if(request('sort'))<input type="hidden" name="sort" value="{{ request('sort') }}">@endif
        @if(request('dir'))<input type="hidden" name="dir" value="{{ request('dir') }}">@endif
        <div class="relative flex-1 min-w-48">
            <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau NIS..." class="form-input pl-9 py-2 text-sm">
        </div>
        <button type="submit" class="px-4 py-2 rounded-xl text-sm font-medium border border-slate-200 dark:border-slate-600 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 transition">Cari</button>
        @if(request('search'))
        <a href="{{ route('walikelas.siswa.index') }}" class="px-4 py-2 rounded-xl text-sm text-slate-500 hover:text-slate-700 transition">Reset</a>
        @endif
    </form>

    @if($siswas->isEmpty())
    <div class="card p-12 text-center text-slate-400">
        <i data-lucide="users" class="w-12 h-12 mx-auto mb-3 opacity-30"></i>
        <p class="font-medium">{{ request('search') ? 'Tidak ada siswa yang cocok dengan pencarian.' : 'Belum ada siswa di kelas ini.' }}</p>
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
                        <x-sortable-th field="jk" label="JK" align="center" class="w-16" />
                        <th class="text-right w-32">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($siswas as $i => $s)
                    <tr>
                        <td class="text-slate-400 text-xs">{{ $siswas->firstItem() + $i }}</td>
                        <td class="font-mono text-sm text-slate-500">{{ $s->nis }}</td>
                        <td class="font-semibold text-slate-800 dark:text-slate-200">
                            {{ $s->nama }}
                            @if(in_array($s->uuid, $sekretarisIds))
                            <span class="badge bg-primary/10 text-primary ml-1">Sekretaris</span>
                            @endif
                        </td>
                        <td class="text-center text-slate-500">{{ $s->jk }}</td>
                        <td class="text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                <a href="{{ route('walikelas.siswa.show', $s) }}" class="p-1.5 rounded-lg border border-slate-200 dark:border-slate-600 text-slate-400 hover:text-primary inline-flex" title="Lihat biodata"><i data-lucide="eye" class="w-3.5 h-3.5"></i></a>
                                <form method="POST" action="{{ route('walikelas.siswa.reset', $s) }}" onsubmit="return confirmAction(this, 'Reset password siswa ini?', 'orange')">
                                    @csrf
                                    <button class="p-1.5 rounded-lg border border-amber-200 dark:border-amber-800 text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-900/30" title="Reset password siswa"><i data-lucide="key-round" class="w-3.5 h-3.5"></i></button>
                                </form>
                                <form method="POST" action="{{ route('walikelas.siswa.resetOrtu', $s) }}" onsubmit="return confirmAction(this, 'Reset password orang tua siswa ini?', 'orange')">
                                    @csrf
                                    <button class="p-1.5 rounded-lg border border-amber-200 dark:border-amber-800 text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-900/30" title="Reset password orang tua"><i data-lucide="users" class="w-3.5 h-3.5"></i></button>
                                </form>
                            </div>
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
