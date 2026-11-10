<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MissionItemBank extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'mission_item_bank';
    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'created_by',
        'type',
        'title',
        'content',
        'meta',
        'is_shared',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
            'is_shared' => 'boolean',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'uuid');
    }
}
