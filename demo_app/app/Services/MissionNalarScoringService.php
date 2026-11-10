<?php

namespace App\Services;

use App\Models\Mission;
use App\Models\MissionStep;

class MissionNalarScoringService
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
            'completed_modules' => array_keys(array_filter($moduleScores, fn (array $score) => $score['is_correct'])),
        ];
    }

    private function scoreStep(MissionStep $step, array $response): array
    {
        return match ($step->module_key) {
            'interactive_narrative' => $this->scoreNarrative($step, $response),
            'strategic_decision' => $this->scoreDecision($step, $response),
            'puzzle_sequencing' => $this->scorePuzzle($step, $response),
            default => [
                'module_key' => $step->module_key,
                'points_awarded' => 0,
                'max_points' => $step->max_points,
                'is_correct' => false,
                'details' => [],
            ],
        };
    }

    private function scoreNarrative(MissionStep $step, array $response): array
    {
        $payload = $step->payload ?? [];
        $expectedPath = array_values(array_filter($payload['expected_path'] ?? []));
        $submittedPath = array_values(array_filter($response['path'] ?? []));
        $acceptedEndNodes = array_values($payload['accepted_end_nodes'] ?? []);
        $finalNode = $response['final_node'] ?? null;

        $matchedPrefix = 0;
        foreach ($expectedPath as $index => $expectedNode) {
            if (($submittedPath[$index] ?? null) !== $expectedNode) {
                break;
            }
            $matchedPrefix++;
        }

        $prefixScore = $expectedPath === []
            ? 0
            : (int) round(($step->max_points * 0.75) * ($matchedPrefix / count($expectedPath)));

        $endingCorrect = $finalNode !== null && in_array($finalNode, $acceptedEndNodes, true);
        $endingBonus = $endingCorrect ? (int) round($step->max_points * 0.25) : 0;
        $points = min($step->max_points, $prefixScore + $endingBonus);

        return [
            'module_key' => $step->module_key,
            'points_awarded' => $points,
            'max_points' => $step->max_points,
            'is_correct' => $matchedPrefix === count($expectedPath) && $endingCorrect,
            'details' => [
                'matched_prefix' => $matchedPrefix,
                'expected_path' => $expectedPath,
                'submitted_path' => $submittedPath,
                'final_node' => $finalNode,
                'accepted_end_nodes' => $acceptedEndNodes,
            ],
        ];
    }

    private function scoreDecision(MissionStep $step, array $response): array
    {
        $payload = $step->payload ?? [];
        $rounds = array_values($payload['rounds'] ?? []);
        $choices = array_values($response['choices'] ?? []);
        $thresholds = array_merge([
            'stability' => 0,
            'trust' => 0,
            'budget' => 0,
        ], $payload['thresholds'] ?? []);

        $points = 0;
        $roundDetails = [];
        foreach ($rounds as $index => $round) {
            $idealChoice = $round['ideal_choice'] ?? null;
            $weight = (int) ($round['weight'] ?? 0);
            $selectedChoice = $choices[$index] ?? null;
            $roundCorrect = $selectedChoice !== null && $selectedChoice === $idealChoice;
            if ($roundCorrect) {
                $points += $weight;
            }
            $roundDetails[] = [
                'round' => $index + 1,
                'ideal_choice' => $idealChoice,
                'selected_choice' => $selectedChoice,
                'weight' => $weight,
                'is_correct' => $roundCorrect,
            ];
        }

        $stats = $this->computeDecisionStats($payload, $choices);
        $thresholdMet =
            ($stats['stability'] ?? 0) >= ($thresholds['stability'] ?? 0) &&
            ($stats['trust'] ?? 0) >= ($thresholds['trust'] ?? 0) &&
            ($stats['budget'] ?? 0) >= ($thresholds['budget'] ?? 0);

        if ($thresholdMet) {
            $points += (int) ($payload['bonus_points'] ?? 0);
        }

        $points = min($step->max_points, $points);
        $allRoundsCorrect = count($roundDetails) > 0
            && count(array_filter($roundDetails, fn (array $detail) => $detail['is_correct'])) === count($rounds);

        return [
            'module_key' => $step->module_key,
            'points_awarded' => $points,
            'max_points' => $step->max_points,
            'is_correct' => $allRoundsCorrect && $thresholdMet,
            'details' => [
                'rounds' => $roundDetails,
                'stats' => $stats,
                'thresholds' => $thresholds,
                'threshold_met' => $thresholdMet,
            ],
        ];
    }

    /**
     * @param  list<string>  $choices
     * @return array{stability: int, trust: int, budget: int}
     */
    private function computeDecisionStats(array $payload, array $choices): array
    {
        $initial = array_merge(
            ['stability' => 50, 'trust' => 50, 'budget' => 50],
            is_array($payload['initial_stats'] ?? null) ? $payload['initial_stats'] : [],
        );
        $rounds = array_values($payload['rounds'] ?? []);
        $stats = $initial;

        foreach ($rounds as $index => $round) {
            $selected = $choices[$index] ?? null;
            if ($selected === null) {
                continue;
            }

            $effects = $this->effectsForChoice($round, $selected);
            if ($effects === null) {
                continue;
            }

            foreach (['stability', 'trust', 'budget'] as $key) {
                $stats[$key] = max(0, min(100, $stats[$key] + (int) ($effects[$key] ?? 0)));
            }
        }

        return $stats;
    }

    /** @return array{stability?: int, trust?: int, budget?: int}|null */
    private function effectsForChoice(array $round, string $selectedChoice): ?array
    {
        foreach ($round['choices'] ?? [] as $choice) {
            if (! is_array($choice)) {
                continue;
            }
            if (($choice['label'] ?? null) === $selectedChoice && is_array($choice['effects'] ?? null)) {
                return $choice['effects'];
            }
        }

        return null;
    }

    private function scorePuzzle(MissionStep $step, array $response): array
    {
        $payload = $step->payload ?? [];
        $correctOrder = array_values(array_filter($payload['correct_order'] ?? []));
        $submittedOrder = array_values(array_filter($response['order'] ?? []));

        $correctPositions = 0;
        foreach ($correctOrder as $index => $expectedId) {
            if (($submittedOrder[$index] ?? null) === $expectedId) {
                $correctPositions++;
            }
        }

        $points = $correctOrder === []
            ? 0
            : (int) round($step->max_points * ($correctPositions / count($correctOrder)));

        return [
            'module_key' => $step->module_key,
            'points_awarded' => $points,
            'max_points' => $step->max_points,
            'is_correct' => $correctPositions === count($correctOrder),
            'details' => [
                'correct_positions' => $correctPositions,
                'correct_order' => $correctOrder,
                'submitted_order' => $submittedOrder,
            ],
        ];
    }
}
