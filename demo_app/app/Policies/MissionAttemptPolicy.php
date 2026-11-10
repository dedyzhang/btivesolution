<?php

namespace App\Policies;

use App\Models\MissionAttempt;
use App\Models\User;

class MissionAttemptPolicy
{
    public function view(User $user, MissionAttempt $attempt): bool
    {
        return $user->uuid === $attempt->user_id
            || in_array($user->access, ['guru', 'admin', 'superadmin', 'walikelas'], true);
    }

    public function reflect(User $user, MissionAttempt $attempt): bool
    {
        return $user->uuid === $attempt->user_id
            && in_array($attempt->status, ['awaiting_reflection', 'completed'], true);
    }
}
