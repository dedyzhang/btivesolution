<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MissionConceptMastery extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'mission_concept_mastery';
    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'concept_key',
        'concept_label',
        'subject',
        'score',
        'level',
        'missions_count',
        'reflections_count',
        'recommendation',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'integer',
            'missions_count' => 'integer',
            'reflections_count' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'uuid');
    }
}
