@extends('layouts.app')
@section('title', 'Cetak Nilai Sumatif')

@section('content')
<div class="space-y-5">
    <div>
        <h1 class="page-title">Cetak Nilai Sumatif</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Unduh nilai sumatif satu kelas — satu sheet Excel per mata pelajaran.</p>
    </div>

    @include('cetak._kelasList', ['kelas' => $kelas, 'routeExcel' => 'cetak.sumatif.excel'])
</div>
@endsection
