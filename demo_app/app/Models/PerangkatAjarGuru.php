<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/** Satu file perangkat ajar yang diupload guru untuk satu jenis dokumen. */
class PerangkatAjarGuru extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'perangkat_guru';
    protected $primaryKey = 'uuid';
    protected $fillable = ['id_guru', 'id_list', 'nama_asli', 'file'];

    public function guru()
    {
        return $this->belongsTo(Guru::class, 'id_guru', 'uuid');
    }

    public function list()
    {
        return $this->belongsTo(PerangkatAjar::class, 'id_list', 'uuid');
    }
}
