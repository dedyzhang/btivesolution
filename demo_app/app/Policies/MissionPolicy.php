<?php

namespace App\Policies;

use App\Models\Classroom;
use App\Models\ClassroomMember;
use App\Models\Mission;
use App\Models\Ngajar;
use App\Models\User;
use App\Models\Walikelas;
use App\Support\UserRole;

class MissionPolicy
{
    /**
     * Role yang boleh membuka / memainkan misi (katalog & API).
     * Termasuk sapras & guru — di sekolah sering merangkap.
     */
    private const PLAY_ROLES = [
        'siswa', 'guru', 'admin', 'superadmin',
        'kepala', 'kurikulum', 'kesiswaan', 'walikelas', 'sapras',
    ];

    /**
     * Role yang boleh membuat / mengedit misi di Builder.
     * sapras & guru ikut — banyak staf merangkap mengajar.
     */
    private const BUILDER_ROLES = [
        'guru', 'admin', 'superadmin', 'kurikulum', 'sapras',
    ];

    public function view(User $user, Mission $mission): bool
    {
        return $this->canPlay($user);
    }

    public function play(User $user, Mission $mission): bool
    {
        return $this->canPlay($user);
    }

    public function evaluate(User $user, Mission $mission): bool
    {
        return $this->canPlay($user);
    }

    public function create(User $user): bool
    {
        return $this->canBuild($user);
    }

    public function manage(User $user, Mission $mission): bool
    {
        if (UserRole::matches($user->access, 'admin', 'superadmin')) {
            return true;
        }

        if (! $this->canBuild($user)) {
            return false;
        }

        if ($mission->created_by === $user->uuid) {
            return true;
        }

        // Katalog bersama Arena (seeder): created_by kosong + visible ke guru.
        return (bool) $mission->visible_to_teachers && $mission->created_by === null;
    }

    public function viewInClassroom(User $user, Mission $mission, Classroom $classroom): bool
    {
        if (! app(ClassroomPolicy::class)->view($user, $classroom)) {
            return false;
        }

        if (! $mission->isPublished()) {
            return $this->manageInClassroom($user, $classroom);
        }

        return $mission->assignmentFor($classroom) !== null
            || $this->manageInClassroom($user, $classroom);
    }

    public function manageInClassroom(User $user, Classroom $classroom): bool
    {
        return app(ClassroomPolicy::class)->manage($user, $classroom);
    }

    public function playInClassroom(User $user, Mission $mission, Classroom $classroom): bool
    {
        if ($user->access === 'siswa') {
            if (! $mission->isPublished()) {
                return false;
            }

            $assignment = $mission->assignmentFor($classroom);
            if (! $assignment || ! $assignment->isOpen()) {
                return false;
            }

            return ClassroomMember::where('classroom_id', $classroom->uuid)
                ->where('user_id', $user->uuid)
                ->where('role_in_class', 'siswa')
                ->exists();
        }

        // Guru pengampu / admin / staf yang manage kelas: penuh.
        if ($this->manageInClassroom($user, $classroom)) {
            return true;
        }

        // Staf lain (kepala/kurikulum/sapras/dll) yang bisa lihat kelas: pratinjau misi terbuka.
        if (! $this->canPlay($user)) {
            return false;
        }

        if (! app(ClassroomPolicy::class)->view($user, $classroom)) {
            return false;
        }

        if (! $mission->isPublished()) {
            return false;
        }

        $assignment = $mission->assignmentFor($classroom);

        return $assignment !== null && $assignment->isOpen();
    }

    public function viewAnalytics(User $user): bool
    {
        if (UserRole::matches(
            $user->access,
            'guru', 'admin', 'superadmin', 'walikelas',
            'kepala', 'kurikulum', 'kesiswaan', 'sapras',
        )) {
            return true;
        }

        // Merangkap: access utama lain tapi punya profil guru
        return $this->hasGuruProfile($user);
    }

    public function viewStudentAnalytics(User $user, User $student): bool
    {
        if (! $this->viewAnalytics($user)) {
            return false;
        }

        if ($student->access !== 'siswa') {
            return false;
        }

        if (UserRole::matches($user->access, 'admin', 'superadmin', 'kepala', 'kurikulum', 'kesiswaan')) {
            return true;
        }

        return $this->teacherCanViewStudent($user, $student);
    }

    public function viewDebriefPanel(User $user): bool
    {
        return $this->viewAnalytics($user);
    }

    public function reviewReflection(User $user): bool
    {
        return $this->viewDebriefPanel($user);
    }

    private function canPlay(User $user): bool
    {
        // UserRole::matches, bukan in_array: users.access disimpan kanonik ('sarpras')
        // sedangkan daftar di atas memakai alias lama ('sapras') — perbandingan mentah
        // membuat staf sarpras tertolak.
        if (UserRole::matches($user->access, ...self::PLAY_ROLES)) {
            return true;
        }

        // Merangkap: mis. access sapras/kepala tapi terdaftar sebagai guru
        return $this->hasGuruProfile($user);
    }

    private function canBuild(User $user): bool
    {
        if (UserRole::matches($user->access, ...self::BUILDER_ROLES)) {
            return true;
        }

        return $this->hasGuruProfile($user);
    }

    private function hasGuruProfile(User $user): bool
    {
        return $user->relationLoaded('guru')
            ? $user->guru !== null
            : $user->guru()->exists();
    }

    private function teacherCanViewStudent(User $user, User $student): bool
    {
        $guru = $user->guru;
        $siswa = $student->siswa;

        if (! $guru || ! $siswa?->id_kelas) {
            return false;
        }

        $kelasId = $siswa->id_kelas;

        if (Ngajar::where('id_guru', $guru->uuid)->where('id_kelas', $kelasId)->exists()) {
            return true;
        }

        if (Walikelas::where('id_guru', $guru->uuid)->where('id_kelas', $kelasId)->exists()) {
            return true;
        }

        $classroomIds = ClassroomMember::query()
            ->where('user_id', $student->uuid)
            ->pluck('classroom_id');

        foreach (Classroom::whereIn('uuid', $classroomIds)->get() as $classroom) {
            if ($this->manageInClassroom($user, $classroom)) {
                return true;
            }
        }

        return false;
    }
}
