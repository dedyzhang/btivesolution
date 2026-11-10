<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HariEfektif extends Model
{
    use HasFactory, HasUuids;

    protected $primaryKey = 'uuid';
    protected $table = 'hari_efektif';
    protected $fillable = ['tanggal', 'absen_siswa', 'agenda_guru', 'kaih_wajib', 'keterangan', 'semester'];

    protected $casts = [
        'tanggal'     => 'date:Y-m-d',
        'absen_siswa' => 'boolean',
        'agenda_guru' => 'boolean',
        'kaih_wajib'  => 'boolean',
    ];
}
