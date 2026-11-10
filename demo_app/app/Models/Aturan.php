<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Aturan extends Model
{
    use HasFactory, HasUuids;

    protected $primaryKey = 'uuid';
    protected $table = 'aturan';
    protected $fillable = ['kode', 'jenis', 'aturan', 'poin'];

    public const JENIS = ['tambah' => 'Tambah', 'kurang' => 'Kurang'];

    public function poins()
    {
        return $this->hasMany(Poin::class, 'id_aturan', 'uuid');
    }
}
