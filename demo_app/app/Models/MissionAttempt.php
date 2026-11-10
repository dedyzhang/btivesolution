<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MissionAttempt extends Model
{
    use HasFactory, HasUuids;

    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'mission_id',
        'assignment_id',
        'user_id',
        'status',
        'started_at',
        'completed_at',
        'score',
        'duration_seconds',
        'result_meta',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'score' => 'integer',
            'duration_seconds' => 'integer',
            'result_meta' => 'array',
        ];
    }

    public function mission(): BelongsTo
    {
        return $this->belongsTo(Mission::class, 'mission_id', 'uuid');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'uuid');
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(MissionAssignment::class, 'assignment_id', 'uuid');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(MissionAttemptResponse::class, 'mission_attempt_id', 'uuid');
    }

    public function reflection(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(MissionReflection::class, 'mission_attempt_id', 'uuid');
    }
}
