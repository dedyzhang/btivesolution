<?php

namespace App\Services;

use App\Models\Mission;
use App\Models\MissionStep;

class MissionPlayerScoringService
{
    public function score(Mission $mission, array $responses): array
    {
        $steps = $mission->steps->sortBy('position')->values();
        $moduleScores = [];
        $totalPoints = 0;
        $maxPoints = 0;

        foreach ($steps as $step) {
            $moduleResponse = data_get($responses, $step->module_key, []);
            $score = $this->scoreStep($step, is_array($moduleResponse) ? $moduleResponse : []);
            $moduleScores[$step->module_key] = $score;
            $totalPoints += $score['points_awarded'];
            $maxPoints += $score['max_points'];
        }

        $percentage = $maxPoints > 0 ? (int) round(($totalPoints / $maxPoints) * 100) : 0;

        return [
            'percentage' => $percentage,
            'points_awarded' => $totalPoints,
            'max_points' => $maxPoints,
            'module_scores' => $moduleScores,
        ];
    }

    private function scoreStep(MissionStep $step, array $response): array
    {
        return match ($step->module_key) {
            'recall_quiz' => $this->scoreRecallQuiz($step, $response),
            'matching' => $this->scoreMatching($step, $response),
            default => [
                'module_key' => $step->module_key,
                'points_awarded' => 0,
                'max_points' => $step->max_points,
                'is_correct' => false,
                'details' => [],
            ],
        };
    }

    private function scoreRecallQuiz(MissionStep $step, array $response): array
    {
        $payload = $step->payload ?? [];
        $questions = array_values($payload['questions'] ?? []);
        $answers = array_values($response['answers'] ?? []);
        $pointsPerQuestion = $questions === [] ? 0 : (int) floor($step->max_points / count($questions));
        $correct = 0;
        $details = [];

        foreach ($questions as $index => $question) {
            $expected = $question['answer'] ?? null;
            $submitted = $answers[$index] ?? null;
            $isCorrect = $expected !== null && $submitted === $expected;
            if ($isCorrect) {
                $correct++;
            }
            $details[] = [
                'index' => $index,
                'expected' => $expected,
                'submitted' => $submitted,
                'is_correct' => $isCorrect,
            ];
        }

        $points = min($step->max_points, $correct * $pointsPerQuestion);

        return [
            'module_key' => $step->module_key,
            'points_awarded' => $points,
            'max_points' => $step->max_points,
            'is_correct' => $correct === count($questions) && count($questions) > 0,
            'details' => $details,
        ];
    }

    private function scoreMatching(MissionStep $step, array $response): array
    {
        $payload = $step->payload ?? [];
        $pairs = array_values($payload['pairs'] ?? []);
        $matches = is_array($response['matches'] ?? null) ? $response['matches'] : [];
        $pointsPerPair = $pairs === [] ? 0 : (int) floor($step->max_points / count($pairs));
        $correct = 0;
        $details = [];

        foreach ($pairs as $pair) {
            $term = $pair['term'] ?? null;
            $expected = $pair['answer'] ?? null;
            $submitted = $matches[$term] ?? null;
            $isCorrect = $expected !== null && $submitted === $expected;
            if ($isCorrect) {
                $correct++;
            }
            $details[] = [
                'term' => $term,
                'expected' => $expected,
                'submitted' => $submitted,
                'is_correct' => $isCorrect,
            ];
        }

        $points = min($step->max_points, $correct * $pointsPerPair);

        return [
            'module_key' => $step->module_key,
            'points_awarded' => $points,
            'max_points' => $step->max_points,
            'is_correct' => $correct === count($pairs) && count($pairs) > 0,
            'details' => $details,
        ];
    }
}
