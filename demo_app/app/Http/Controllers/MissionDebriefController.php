<?php

namespace App\Http\Controllers;

use App\Models\MissionAttempt;
use App\Models\MissionReflection;
use App\Services\MissionAnalyticsService;
use App\Services\MissionAttemptCompletionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class MissionDebriefController extends Controller
{
    public function show(Request $request, MissionAttempt $attempt): View
    {
        Gate::authorize('view', $attempt);
        $attempt->load(['mission.reflectionPrompts', 'reflection', 'user']);

        return view('jagat-misi.debrief', compact('attempt'));
    }

    public function teacherPanel(Request $request): View
    {
        Gate::authorize('viewDebriefPanel', Mission::class);

        $classFilter = $request->query('kelas', 'all');
        $statusFilter = $request->query('status', 'all');

        $query = MissionReflection::query()
            ->with(['attempt.mission', 'user.siswa.kelas'])
            ->latest();

        $reflections = $query->get()->filter(function (MissionReflection $reflection) use ($classFilter, $statusFilter) {
            $className = $reflection->user?->siswa?->kelas?->kelas;
            if ($classFilter !== 'all' && $className !== $classFilter) {
                return false;
            }
            $status = $reflection->reviewed_at ? 'reviewed' : ($reflection->confirmed ? 'reflected' : 'pending');
            if ($statusFilter !== 'all' && $status !== $statusFilter) {
                return false;
            }

            return true;
        });

        return view('jagat-misi.debrief-teacher', compact('reflections', 'classFilter', 'statusFilter'));
    }

    public function store(Request $request, MissionAttempt $attempt, MissionAttemptCompletionService $completionService): JsonResponse
    {
        Gate::authorize('reflect', $attempt);

        $validated = $request->validate([
            'understand' => ['required', 'string', 'max:2000'],
            'barrier' => ['nullable', 'string', 'max:2000'],
            'next_step' => ['nullable', 'string', 'max:2000'],
            'mood' => ['nullable', 'string', 'max:32'],
            'prompts_meta' => ['nullable', 'array'],
            'confirmed' => ['required', 'boolean'],
        ]);

        $user = $request->user();

        $reflection = DB::transaction(function () use ($attempt, $user, $validated, $completionService) {
            $reflection = MissionReflection::updateOrCreate(
                ['mission_attempt_id' => $attempt->uuid],
                [
                    'user_id' => $user->uuid,
                    'understand' => $validated['understand'],
                    'barrier' => $validated['barrier'] ?? null,
                    'next_step' => $validated['next_step'] ?? null,
                    'mood' => $validated['mood'] ?? null,
                    'prompts_meta' => $validated['prompts_meta'] ?? [],
                    'confirmed' => (bool) $validated['confirmed'],
                ]
            );

            if ($reflection->confirmed && $attempt->status === 'awaiting_reflection') {
                $completionService->completeAfterReflection($attempt);
                app(MissionAnalyticsService::class)->syncMasteryFor($user);
            }

            return $reflection;
        });

        return response()->json([
            'message' => $reflection->confirmed ? 'Refleksi tersimpan. Misi selesai.' : 'Refleksi tersimpan.',
            'data' => ['reflection_id' => $reflection->uuid, 'attempt_status' => $attempt->fresh()->status],
        ]);
    }

    public function markReviewed(Request $request, MissionReflection $reflection): JsonResponse
    {
        Gate::authorize('viewDebriefPanel', Mission::class);

        $reflection->forceFill([
            'reviewed_at' => now(),
            'reviewed_by' => $request->user()->uuid,
        ])->save();

        return response()->json(['message' => 'Refleksi ditandai sudah dibahas.']);
    }
}
