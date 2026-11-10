<?php

namespace App\Support;

use App\Models\MissionStep;

/**
 * Menyajikan payload step misi ke klien tanpa kunci jawaban / rubrik penilaian.
 */
class MissionStepPayloadPresenter
{
    public static function forClient(MissionStep $step): array
    {
        $payload = $step->payload ?? [];

        return match ($step->module_key) {
            'interactive_narrative' => self::withoutKeys($payload, ['expected_path', 'accepted_end_nodes']),
            'strategic_decision' => self::sanitizeDecision($payload),
            'puzzle_sequencing' => self::withoutKeys($payload, ['correct_order']),
            'recall_quiz' => self::sanitizeRecallQuiz($payload),
            'matching' => self::sanitizeMatching($payload),
            default => $payload,
        };
    }

    /** @param  list<string>  $keys */
    private static function withoutKeys(array $payload, array $keys): array
    {
        foreach ($keys as $key) {
            unset($payload[$key]);
        }

        return $payload;
    }

    private static function sanitizeDecision(array $payload): array
    {
        unset($payload['thresholds'], $payload['bonus_points']);

        if (! isset($payload['rounds']) || ! is_array($payload['rounds'])) {
            return $payload;
        }

        $payload['rounds'] = array_map(function ($round) {
            if (! is_array($round)) {
                return $round;
            }

            unset($round['ideal_choice'], $round['weight']);

            if (isset($round['choices']) && is_array($round['choices'])) {
                $round['choices'] = array_map(function ($choice) {
                    if (! is_array($choice)) {
                        return $choice;
                    }
                    unset($choice['effects']);

                    return $choice;
                }, $round['choices']);
            }

            return $round;
        }, $payload['rounds']);

        return $payload;
    }

    private static function sanitizeRecallQuiz(array $payload): array
    {
        if (! isset($payload['questions']) || ! is_array($payload['questions'])) {
            return $payload;
        }

        $payload['questions'] = array_map(function ($question) {
            if (! is_array($question)) {
                return $question;
            }
            unset($question['answer']);

            return $question;
        }, $payload['questions']);

        return $payload;
    }

    private static function sanitizeMatching(array $payload): array
    {
        if (isset($payload['pairs']) && is_array($payload['pairs'])) {
            $payload['pairs'] = array_map(function ($pair) {
                if (! is_array($pair)) {
                    return $pair;
                }

                return [
                    'term' => $pair['term'] ?? null,
                ];
            }, $payload['pairs']);
        }

        return $payload;
    }
}
