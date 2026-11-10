<?php

namespace App\Http\Controllers;

use App\Models\Mission;
use App\Models\MissionActivityLog;
use App\Models\MissionAttempt;
use App\Services\MissionAttemptCompletionService;
use App\Services\MissionPlayerScoringService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use App\Support\MissionStepPayloadPresenter;

class MissionPlayerController extends Controller
{
    public function play(Mission $mission): View
    {
        Gate::authorize('view', $mission);
        $mission->load(['steps' => fn ($q) => $q->orderBy('position')]);

        return view('jagat-misi.player', compact('mission'));
    }

    public function show(Request $request, Mission $mission): JsonResponse
    {
        Gate::authorize('view', $mission);
        $mission->load(['steps' => fn ($q) => $q->orderBy('position')]);

        return response()->json([
            'data' => [
                'mission' => [
                    'id' => $mission->uuid,
                    'slug' => $mission->slug,
                    'title' => $mission->title,
                    'subject' => $mission->subject,
                    'grade_level' => $mission->grade_level,
                    'duration_minutes' => $mission->duration_minutes,
                    'max_score' => $mission->max_score,
                ],
                'steps' => $mission->steps->map(fn ($step) => [
                    'module_key' => $step->module_key,
                    'title' => $step->title,
                    'prompt' => $step->prompt,
                    'body' => $step->body,
                    'payload' => MissionStepPayloadPresenter::forClient($step),
                    'max_points' => $step->max_points,
                ])->values(),
            ],
        ]);
    }

    public function store(
        Request $request,
        Mission $mission,
        MissionPlayerScoringService $scoringService,
        MissionAttemptCompletionService $completionService,
    ): JsonResponse {
        Gate::authorize('evaluate', $mission);

        $validated = $request->validate([
            'assignment_id' => ['nullable', 'uuid', 'exists:mission_assignments,uuid'],
            'responses' => ['required', 'array'],
            'responses.recall_quiz.answers' => ['nullable', 'array'],
            'responses.recall_quiz.answers.*' => ['nullable', 'string'],
            'responses.matching.matches' => ['nullable', 'array'],
            'duration_seconds' => ['nullable', 'integer', 'min:0'],
            'avatar_config' => ['nullable', 'array'],
        ]);

        $user = $request->user();
        $mission->load(['steps' => fn ($q) => $q->orderBy('position'), 'reflectionPrompts']);

        if (! empty($validated['avatar_config'])) {
            $user->forceFill(['mission_avatar_config' => $validated['avatar_config']])->save();
        }

        $attempt = DB::transaction(function () use ($mission, $user, $validated, $scoringService, $completionService) {
            $assignmentId = $completionService->resolveAssignmentId(
                $mission,
                $user,
                $validated['assignment_id'] ?? null,
            );
            $scoreData = $scoringService->score($mission, $validated['responses']);
            $status = $completionService->resolveStatusAfterScore($mission);

            $attempt = MissionAttempt::create([
                'mission_id' => $mission->uuid,
                'assignment_id' => $assignmentId,
                'user_id' => $user->uuid,
                'status' => $status,
                'started_at' => now()->subSeconds((int) ($validated['duration_seconds'] ?? 0)),
                'completed_at' => $status === 'completed' ? now() : null,
                'score' => $scoreData['percentage'],
                'duration_seconds' => (int) ($validated['duration_seconds'] ?? 0),
                'result_meta' => [
                    'source' => 'player',
                    'points_awarded' => $scoreData['points_awarded'],
                    'max_points' => $scoreData['max_points'],
                    'module_scores' => $scoreData['module_scores'],
                ],
            ]);

            foreach ($mission->steps as $step) {
                $moduleResponse = data_get($validated['responses'], $step->module_key, []);
                $moduleScore = $scoreData['module_scores'][$step->module_key] ?? [
                    'points_awarded' => 0,
                    'is_correct' => false,
                ];

                $attempt->responses()->create([
                    'mission_step_id' => $step->uuid,
                    'module_key' => $step->module_key,
                    'response_payload' => is_array($moduleResponse) ? $moduleResponse : [],
                    'is_correct' => $moduleScore['is_correct'],
                    'points_awarded' => $moduleScore['points_awarded'],
                    'evaluated_at' => now(),
                ]);
            }

            MissionActivityLog::create([
                'action' => 'mission_attempt.scored',
                'subject_type' => MissionAttempt::class,
                'subject_id' => $attempt->uuid,
                'causer_type' => $user::class,
                'causer_id' => $user->uuid,
                'properties' => [
                    'mission_id' => $mission->uuid,
                    'score' => $scoreData['percentage'],
                    'status' => $status,
                ],
            ]);

            return $attempt->load('responses');
        });

        return response()->json([
            'message' => $attempt->status === 'awaiting_reflection'
                ? 'Skor tersimpan. Lanjut ke debrief refleksi.'
                : 'Misi selesai.',
            'data' => [
                'attempt_id' => $attempt->uuid,
                'status' => $attempt->status,
                'score' => $attempt->score,
                'debrief_url' => $attempt->status === 'awaiting_reflection'
                    ? route('jagat-misi.debrief', $attempt)
                    : null,
            ],
        ], 201);
    }
}
