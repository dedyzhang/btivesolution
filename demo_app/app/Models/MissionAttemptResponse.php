<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MissionAttemptResponse extends Model
{
    use HasFactory, HasUuids;

    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'mission_attempt_id',
        'mission_step_id',
        'module_key',
        'response_payload',
        'is_correct',
        'points_awarded',
        'evaluated_at',
    ];

    protected function casts(): array
    {
        return [
            'response_payload' => 'array',
            'is_correct' => 'boolean',
            'points_awarded' => 'integer',
            'evaluated_at' => 'datetime',
        ];
    }

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(MissionAttempt::class, 'mission_attempt_id', 'uuid');
    }

    public function missionStep(): BelongsTo
    {
        return $this->belongsTo(MissionStep::class, 'mission_step_id', 'uuid');
    }
}
