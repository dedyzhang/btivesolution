@extends('layouts.app')
@section('title', 'Cetak Nilai PAS')

@section('content')
<div class="space-y-5">
    <div>
        <h1 class="page-title">Cetak Nilai PAS</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Unduh matriks nilai Penilaian Akhir Semester (siswa × mata pelajaran) satu kelas dalam format Excel.</p>
    </div>

    @include('cetak._kelasList', ['kelas' => $kelas, 'routeExcel' => 'cetak.pas.excel'])
</div>
@endsection
