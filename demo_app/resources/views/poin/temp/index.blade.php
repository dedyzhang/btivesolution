@extends('layouts.app')
@section('title', 'Pengajuan Poin')

@section('content')
<div class="space-y-5">
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h1 class="page-title">Pengajuan Poin</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Menunggu persetujuan — dari guru, wali kelas, atau sekretaris kelas</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('poin.temp.history', ['status'=>'approve']) }}" class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition"><i data-lucide="check-check" class="w-4 h-4"></i> Disetujui</a>
            <a href="{{ route('poin.temp.history', ['status'=>'disapprove']) }}" class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition"><i data-lucide="x" class="w-4 h-4"></i> Ditolak</a>
            @if($pendings->isNotEmpty())
            <form method="POST" action="{{ route('poin.temp.bulkUpdate') }}" onsubmit="return confirmAction(this, 'Setujui semua ({{ $pendings->total() }}) pengajuan poin yang menunggu?', 'green')">
                @csrf<input type="hidden" name="status" value="approve">
                <button class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold border border-emerald-200 dark:border-emerald-800 text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-900/30 transition"><i data-lucide="check-check" class="w-4 h-4"></i> Setujui Semua</button>
            </form>
            <form method="POST" action="{{ route('poin.temp.bulkUpdate') }}" onsubmit="return confirmAction(this, 'Tolak semua ({{ $pendings->total() }}) pengajuan poin yang menunggu?', 'red')">
                @csrf<input type="hidden" name="status" value="disapprove">
                <button class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold border border-rose-200 dark:border-rose-800 text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/30 transition"><i data-lucide="x" class="w-4 h-4"></i> Tolak Semua</button>
            </form>
            @endif
        </div>
    </div>

    @if($pendings->isEmpty())
    <div class="card p-12 text-center text-slate-400">
        <i data-lucide="inbox" class="w-12 h-12 mx-auto mb-3 opacity-30"></i>
        <p class="font-medium">Tidak ada pengajuan menunggu.</p>
    </div>
    @else
    <div class="card overflow-hidden">
        <div class="table-responsive">
            <table class="data-table w-full">
                <thead>
                    <tr>
                        <th class="w-10">#</th>
                        <x-sortable-th field="tanggal" label="Tanggal" />
                        <th>Siswa</th>
                        <th class="hide-mobile">Kelas</th>
                        <th>Aturan</th>
                        <th class="text-right">Poin</th>
                        <th class="hide-mobile">Diajukan Oleh</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pendings as $i => $t)
                    <tr>
                        <td class="text-slate-400 text-xs">{{ $pendings->firstItem() + $i }}</td>
                        <td class="text-sm text-slate-500">{{ $t->tanggal->isoFormat('D MMM Y') }}</td>
                        <td class="font-semibold text-slate-800 dark:text-slate-200">{{ $t->siswa?->nama }}</td>
                        <td class="hide-mobile text-slate-500">{{ $t->siswa?->kelas ? $t->siswa->kelas->tingkat.$t->siswa->kelas->kelas : '-' }}</td>
                        <td class="text-sm text-slate-600 dark:text-slate-300"><span class="font-mono text-xs text-slate-400">{{ $t->aturan?->kode }}</span> {{ $t->aturan?->aturan }}</td>
                        <td class="text-right font-semibold {{ $t->aturan?->jenis==='kurang' ? 'text-rose-600' : 'text-emerald-600' }}">{{ $t->aturan?->jenis==='kurang' ? '-' : '+' }}{{ $t->aturan?->poin }}</td>
                        <td class="hide-mobile text-xs text-slate-500">{{ ucfirst($t->penginput) }} &bull; {{ $t->nama_pengaju }}</td>
                        <td class="text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                <form method="POST" action="{{ route('poin.temp.update', $t) }}">
                                    @csrf<input type="hidden" name="status" value="approve">
                                    <button class="p-1.5 rounded-lg border border-emerald-200 dark:border-emerald-800 text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-900/30"><i data-lucide="check" class="w-3.5 h-3.5"></i></button>
                                </form>
                                <form method="POST" action="{{ route('poin.temp.update', $t) }}">
                                    @csrf<input type="hidden" name="status" value="disapprove">
                                    <button class="p-1.5 rounded-lg border border-rose-200 dark:border-rose-800 text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/30"><i data-lucide="x" class="w-3.5 h-3.5"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-100 dark:border-slate-700">{{ $pendings->links() }}</div>
    </div>
    @endif
</div>
@endsection
