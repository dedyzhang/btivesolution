<?php

namespace App\Models;

use Database\Factories\MissionBadgeFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MissionBadge extends Model
{
    /** @use HasFactory<MissionBadgeFactory> */
    use HasFactory, HasUuids;

    protected $table = 'mission_badges';
    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'code',
        'name',
        'description',
        'icon',
        'threshold_xp',
        'threshold_streak',
        'threshold_missions',
        'is_active',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'threshold_xp' => 'integer',
            'threshold_streak' => 'integer',
            'threshold_missions' => 'integer',
            'is_active' => 'boolean',
            'meta' => 'array',
        ];
    }

    public function studentBadges(): HasMany
    {
        return $this->hasMany(MissionStudentBadge::class, 'badge_id', 'uuid');
    }
}
