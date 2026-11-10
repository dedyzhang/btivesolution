<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MissionStudentBadge extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'mission_student_badges';
    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'badge_id',
        'earned_at',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'earned_at' => 'datetime',
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
