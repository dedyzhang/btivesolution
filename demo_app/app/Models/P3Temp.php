<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class P3Temp extends Model
{
    use HasFactory, HasUuids;

    protected $primaryKey = 'uuid';
    protected $table = 'p3_temp';
    protected $fillable = [
        'id_siswa', 'yang_mengajukan', 'id_pengajuan', 'tanggal',
        'jenis', 'deskripsi', 'status', 'id_semester', 'poin',
    ];

    protected $casts = ['tanggal' => 'date:Y-m-d'];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'id_siswa', 'uuid');
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class, 'id_semester');
    }

    public function getNamaPengajuAttribute(): string
    {
        if ($this->yang_mengajukan === 'guru') {
            return Guru::where('uuid', $this->id_pengajuan)->value('nama') ?? '-';
        }
        return Siswa::where('uuid', $this->id_pengajuan)->value('nama') ?? '-';
    }
}
