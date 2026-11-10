<?php

namespace App\Models;

use Database\Factories\MissionStepFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MissionStep extends Model
{
    /** @use HasFactory<MissionStepFactory> */
    use HasFactory, HasUuids;

    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'mission_id',
        'module_key',
        'position',
        'title',
        'prompt',
        'body',
        'payload',
        'max_points',
    ];

    protected function casts(): array
    {
        return [
            'position' => 'integer',
            'payload' => 'array',
            'max_points' => 'integer',
        ];
    }

    public function mission(): BelongsTo
    {
        return $this->belongsTo(Mission::class, 'mission_id', 'uuid');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(MissionAttemptResponse::class, 'mission_step_id', 'uuid');
    }
}
