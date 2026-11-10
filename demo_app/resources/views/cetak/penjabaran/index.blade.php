@extends('layouts.app')
@section('title', 'Cetak Nilai Penjabaran')

@section('content')
<div class="space-y-5">
    <div>
        <h1 class="page-title">Cetak Nilai Penjabaran</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Unduh nilai penjabaran satu kelas — satu sheet Excel per mapel yang punya komponen penjabaran.</p>
    </div>

    @include('cetak._kelasList', ['kelas' => $kelas, 'routeExcel' => 'cetak.penjabaran.excel'])
</div>
@endsection
