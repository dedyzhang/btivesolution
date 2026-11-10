<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MissionAssignment extends Model
{
    use HasFactory, HasUuids;

    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'mission_id',
        'classroom_id',
        'assigned_by',
        'opens_at',
        'due_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'opens_at' => 'datetime',
            'due_at' => 'datetime',
        ];
    }

    public function mission()
    {
        return $this->belongsTo(Mission::class, 'mission_id', 'uuid');
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class, 'classroom_id', 'uuid');
    }

    public function assigner()
    {
        return $this->belongsTo(User::class, 'assigned_by', 'uuid');
    }

    public function attempts()
    {
        return $this->hasMany(MissionAttempt::class, 'assignment_id', 'uuid');
    }

    public function isOpen(): bool
    {
        if ($this->status !== 'open') {
            return false;
        }
        $now = now();
        if ($this->opens_at && $now->lt($this->opens_at)) {
            return false;
        }
        if ($this->due_at && $now->gt($this->due_at)) {
            return false;
        }

        return true;
    }
}
