<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MissionCollectionItem extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'mission_collection_items';
    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'badge_id',
        'code',
        'name',
        'kind',
        'description',
        'unlocked_at',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'unlocked_at' => 'datetime',
            'meta' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'uuid');
    }

    public function badge(): BelongsTo
    {
        return $this->belongsTo(MissionBadge::class, 'badge_id', 'uuid');
    }
}
