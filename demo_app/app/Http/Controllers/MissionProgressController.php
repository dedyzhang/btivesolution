<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\MissionProgressionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class MissionProgressController extends Controller
{
    public function index(Request $request, MissionProgressionService $service): View
    {
        $profile = $service->profile($request->user());
        $leaderboard = $service->leaderboard();

        return view('jagat-misi.progress', compact('profile', 'leaderboard'));
    }

    public function show(Request $request, MissionProgressionService $service, ?User $user = null): JsonResponse
    {
        $actor = $request->user();
        $subject = $user ?? $actor;

        Gate::forUser($actor)->authorize('viewProgress', $subject);

        return response()->json([
            'data' => [
                'profile' => $service->profile($subject),
            ],
        ]);
    }

    public function leaderboard(Request $request, MissionProgressionService $service): JsonResponse
    {
        $actor = $request->user();
        Gate::forUser($actor)->authorize('viewLeaderboard', $actor);

        return response()->json([
            'data' => [
                'leaderboard' => $service->leaderboard(),
            ],
        ]);
    }

    public function updateVisibility(Request $request, MissionProgressionService $service): JsonResponse
    {
        $actor = $request->user();
        Gate::forUser($actor)->authorize('toggleLeaderboardVisibility', $actor);

        $validated = $request->validate([
            'leaderboard_visible' => ['required', 'boolean'],
        ]);

        $updated = $service->setLeaderboardVisibility($actor, (bool) $validated['leaderboard_visible']);

        return response()->json([
            'message' => 'Pengaturan leaderboard diperbarui.',
            'data' => [
                'leaderboard_visible' => (bool) $updated->leaderboard_visible,
            ],
        ]);
    }
}
