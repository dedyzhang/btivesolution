<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PoinTemp extends Model
{
    use HasFactory, HasUuids;

    protected $primaryKey = 'uuid';
    protected $table = 'poin_temp';
    protected $fillable = ['tanggal', 'id_aturan', 'id_siswa', 'penginput', 'id_input', 'status'];

    protected $casts = ['tanggal' => 'date:Y-m-d'];

    public const STATUS = ['belum' => 'Menunggu', 'approve' => 'Disetujui', 'disapprove' => 'Ditolak'];

    public function aturan()
    {
        return $this->belongsTo(Aturan::class, 'id_aturan', 'uuid');
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'id_siswa', 'uuid');
    }

    /** Nama pengaju: guru atau siswa (sekretaris), sesuai kolom penginput. */
    public function getNamaPengajuAttribute(): string
    {
        if ($this->penginput === 'guru') {
            return Guru::where('uuid', $this->id_input)->value('nama') ?? '-';
        }
        return Siswa::where('uuid', $this->id_input)->value('nama') ?? '-';
    }
}
