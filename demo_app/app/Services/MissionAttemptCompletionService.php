<?php

namespace App\Services;

use App\Models\Mission;
use App\Models\MissionAssignment;
use App\Models\MissionAttempt;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class MissionAttemptCompletionService
{
    public function resolveAssignmentId(Mission $mission, User $user, ?string $assignmentId): ?string
    {
        if ($user->access === 'siswa') {
            abort_unless($assignmentId, 422, 'Misi harus dikerjakan lewat tugas kelas.');

            return $this->validatedAssignmentId($mission, $assignmentId);
        }

        if (! $assignmentId) {
            return null;
        }

        return $this->validatedAssignmentId($mission, $assignmentId);
    }

    private function validatedAssignmentId(Mission $mission, string $assignmentId): string
    {
        $assignment = MissionAssignment::query()
            ->where('uuid', $assignmentId)
            ->where('mission_id', $mission->uuid)
            ->with('classroom')
            ->firstOrFail();

        Gate::authorize('playInClassroom', [$mission, $assignment->classroom]);

        return $assignment->uuid;
    }

    public function resolveStatusAfterScore(Mission $mission): string
    {
        if ($mission->requires_reflection && $mission->reflectionPrompts()->exists()) {
            return 'awaiting_reflection';
        }

        return 'completed';
    }

    public function completeAfterReflection(MissionAttempt $attempt): MissionAttempt
    {
        $attempt->forceFill([
            'status' => 'completed',
            'completed_at' => $attempt->completed_at ?? now(),
        ])->save();

        return $attempt->refresh();
    }
}
