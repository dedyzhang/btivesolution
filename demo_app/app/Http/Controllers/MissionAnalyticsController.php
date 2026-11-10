<?php

namespace App\Http\Controllers;

use App\Models\Mission;
use App\Models\User;
use App\Services\MissionAnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class MissionAnalyticsController extends Controller
{
    public function index(Request $request, MissionAnalyticsService $service): View
    {
        Gate::authorize('viewAnalytics', Mission::class);

        $matrix = $service->matrix(
            $request->query('kelas'),
            $request->query('mapel'),
            $request->query('status'),
        );

        return view('jagat-misi.analytics', compact('matrix'));
    }

    public function matrix(Request $request, MissionAnalyticsService $service): JsonResponse
    {
        Gate::authorize('viewAnalytics', Mission::class);

        return response()->json([
            'data' => $service->matrix(
                $request->query('kelas'),
                $request->query('mapel'),
                $request->query('status'),
            ),
        ]);
    }

    public function student(Request $request, User $user, MissionAnalyticsService $service): JsonResponse
    {
        Gate::authorize('viewAnalytics', Mission::class);
        Gate::authorize('viewStudentAnalytics', $user);

        return response()->json(['data' => $service->studentDetail($user)]);
    }

    public function report(Request $request, User $user, MissionAnalyticsService $service): View
    {
        Gate::authorize('viewAnalytics', Mission::class);
        Gate::authorize('viewStudentAnalytics', $user);

        $report = $service->report($user, $request->query('format', 'parent'));

        return view('jagat-misi.analytics-report', compact('report'));
    }
}
