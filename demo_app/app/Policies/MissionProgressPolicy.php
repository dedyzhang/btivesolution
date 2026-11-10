<?php

namespace App\Policies;

use App\Models\User;

class MissionProgressPolicy
{
    public function viewProgress(User $user, User $subject): bool
    {
        return $user->uuid === $subject->uuid
            || in_array($user->access, ['guru', 'admin', 'superadmin'], true);
    }

    public function viewLeaderboard(User $user): bool
    {
        return in_array($user->access, ['siswa', 'guru', 'admin', 'superadmin'], true);
    }

    public function toggleLeaderboardVisibility(User $user, User $subject): bool
    {
        return $user->uuid === $subject->uuid
            || in_array($user->access, ['guru', 'admin', 'superadmin'], true);
    }
}
