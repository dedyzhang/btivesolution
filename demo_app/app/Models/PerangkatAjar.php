<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/** Master jenis dokumen perangkat ajar (RPP, Modul Ajar, Prota, dst). */
class PerangkatAjar extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'perangkat_list';
    protected $primaryKey = 'uuid';
    protected $fillable = ['perangkat'];

    public function uploads()
    {
        return $this->hasMany(PerangkatAjarGuru::class, 'id_list', 'uuid');
    }
}
