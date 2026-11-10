<?php

namespace App\Services;

use App\Models\Mission;
use App\Models\MissionAttempt;
use App\Models\MissionConceptMastery;
use App\Models\MissionReflection;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MissionAnalyticsService
{
    public function syncMasteryFor(User $user): void
    {
        DB::transaction(function () use ($user) {
            $attempts = MissionAttempt::query()
                ->with('mission')
                ->where('user_id', $user->uuid)
                ->where('status', 'completed')
                ->get();

            $reflectionsCount = MissionReflection::query()
                ->where('user_id', $user->uuid)
                ->where('confirmed', true)
                ->count();

            $grouped = $attempts->groupBy(fn (MissionAttempt $a) => $this->conceptKeyFor($a->mission));

            foreach ($grouped as $conceptKey => $rows) {
                if ($conceptKey === 'general') {
                    continue;
                }
                $mission = $rows->first()->mission;
                $avgScore = (int) round($rows->avg('score'));
                $level = $this->levelFromScore($avgScore);

                MissionConceptMastery::updateOrCreate(
                    ['user_id' => $user->uuid, 'concept_key' => $conceptKey],
                    [
                        'concept_label' => $mission?->meta['concept_label'] ?? $mission?->title ?? $conceptKey,
                        'subject' => $mission?->subject ?? 'Umum',
                        'score' => $avgScore,
                        'level' => $level,
                        'missions_count' => $rows->count(),
                        'reflections_count' => $reflectionsCount,
                        'recommendation' => $this->recommendationFor($level, $mission?->subject),
                    ]
                );
            }
        });
    }

    public function matrix(?string $classFilter = null, ?string $subjectFilter = null, ?string $statusFilter = null): array
    {
        $students = $this->studentUsers($classFilter);
        $rows = [];

        foreach ($students as $student) {
            $this->syncMasteryFor($student);
            $masteries = MissionConceptMastery::query()
                ->where('user_id', $student->uuid)
                ->when($subjectFilter && $subjectFilter !== 'all', fn ($q) => $q->where('subject', $subjectFilter))
                ->when($statusFilter && $statusFilter !== 'all', fn ($q) => $q->where('level', $statusFilter))
                ->get();

            if ($masteries->isEmpty()) {
                continue;
            }

            $rows[] = [
                'user_id' => $student->uuid,
                'name' => $student->displayName(),
                'class_name' => $student->siswa?->kelas
                    ? ($student->siswa->kelas->tingkat . $student->siswa->kelas->kelas)
                    : '-',
                'scores' => $masteries->mapWithKeys(fn ($m) => [$m->concept_key => $m->score])->all(),
                'level' => $this->levelFromScore((int) round($masteries->avg('score'))),
                'missions' => (int) $masteries->sum('missions_count'),
                'reflections' => (int) $masteries->max('reflections_count'),
            ];
        }

        return [
            'concepts' => $this->conceptCatalog(),
            'students' => $rows,
        ];
    }

    public function studentDetail(User $student): array
    {
        $this->syncMasteryFor($student);

        $masteries = MissionConceptMastery::query()
            ->where('user_id', $student->uuid)
            ->orderBy('subject')
            ->get();

        $attempts = MissionAttempt::query()
            ->with('mission')
            ->where('user_id', $student->uuid)
            ->where('status', 'completed')
            ->orderByDesc('completed_at')
            ->limit(10)
            ->get();

        return [
            'student' => [
                'id' => $student->uuid,
                'name' => $student->displayName(),
                'class_name' => $student->siswa?->kelas
                    ? ($student->siswa->kelas->tingkat . $student->siswa->kelas->kelas)
                    : '-',
            ],
            'masteries' => $masteries->map(fn ($m) => [
                'concept_key' => $m->concept_key,
                'concept_label' => $m->concept_label,
                'subject' => $m->subject,
                'score' => $m->score,
                'level' => $m->level,
                'recommendation' => $m->recommendation,
            ])->values()->all(),
            'recent_attempts' => $attempts->map(fn ($a) => [
                'mission_title' => $a->mission?->title,
                'score' => $a->score,
                'completed_at' => optional($a->completed_at)->toISOString(),
            ])->values()->all(),
            'average_score' => $masteries->isEmpty() ? 0 : (int) round($masteries->avg('score')),
        ];
    }

    public function report(User $student, string $format = 'parent'): array
    {
        $detail = $this->studentDetail($student);

        return [
            'format' => $format,
            'title' => $format === 'parent' ? 'Laporan Orang Tua' : 'Laporan Guru',
            'generated_at' => now()->toISOString(),
            'student' => $detail['student'],
            'average_score' => $detail['average_score'],
            'masteries' => $detail['masteries'],
            'summary' => $this->reportSummary($detail, $format),
        ];
    }

    private function studentUsers(?string $classFilter): Collection
    {
        return User::query()
            ->where('access', 'siswa')
            ->with('siswa.kelas')
            ->when($classFilter && $classFilter !== 'all', function ($q) use ($classFilter) {
                $q->whereHas('siswa.kelas', fn ($k) => $k->where('kelas', $classFilter));
            })
            ->get();
    }

    private function conceptKeyFor(?Mission $mission): string
    {
        if (! $mission) {
            return 'general';
        }

        return $mission->meta['concept_key'] ?? \Illuminate\Support\Str::slug($mission->subject . '-' . $mission->title);
    }

    private function levelFromScore(int $score): string
    {
        if ($score >= 80) {
            return 'strong';
        }
        if ($score >= 68) {
            return 'watch';
        }

        return 'support';
    }

    private function recommendationFor(string $level, ?string $subject): string
    {
        return match ($level) {
            'strong' => "Pertahankan performa di mapel {$subject}; beri tantangan lanjutan.",
            'watch' => "Pantau konsep {$subject}; ulangi misi dengan skor rendah.",
            default => "Butuh dukungan di {$subject}; mulai dari misi pendek dan refleksi wajib.",
        };
    }

    private function conceptCatalog(): array
    {
        return MissionConceptMastery::query()
            ->select('concept_key', 'concept_label', 'subject')
            ->distinct()
            ->get()
            ->map(fn ($m) => [
                'key' => $m->concept_key,
                'label' => $m->concept_label,
                'subject' => $m->subject,
            ])
            ->values()
            ->all();
    }

    private function reportSummary(array $detail, string $format): string
    {
        $name = $detail['student']['name'];
        $avg = $detail['average_score'];

        if ($format === 'parent') {
            return "{$name} menyelesaikan misi dengan rata-rata skor {$avg}. Lanjutkan dukungan belajar di rumah.";
        }

        return "{$name} memiliki rata-rata penguasaan {$avg}. Gunakan data konsep untuk intervensi kelas.";
    }
}
