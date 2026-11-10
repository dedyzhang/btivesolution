<?php

namespace App\Support;

/**
 * Ubah outline teks (dari guru / Gemini) menjadi daftar slide untuk Studio Presentasi.
 */
final class PresentationSlides
{
    /**
     * @return list<array{title:string,body:string}>
     */
    public static function fromOutline(?string $outline): array
    {
        $text = trim((string) $outline);
        if ($text === '') {
            return [];
        }

        $lines = preg_split("/\r\n|\n|\r/", $text) ?: [];
        $slides = [];
        $current = null;

        $flush = function () use (&$slides, &$current) {
            if ($current === null) {
                return;
            }
            $current['body'] = trim($current['body']);
            if ($current['title'] !== '' || $current['body'] !== '') {
                $slides[] = $current;
            }
            $current = null;
        };

        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '') {
                if ($current !== null) {
                    $current['body'] .= "\n";
                }
                continue;
            }

            // "1. Judul", "1) Judul", "SLIDE 1: Judul", "## Judul"
            if (preg_match('/^(?:slide\s*)?(\d+)\s*[\.\)\:\-]\s*(.+)$/iu', $trimmed, $m)
                || preg_match('/^#{1,3}\s+(.+)$/u', $trimmed, $m2)) {
                $flush();
                $title = isset($m2[1]) ? trim($m2[1]) : trim($m[2]);
                $current = ['title' => $title, 'body' => ''];
                continue;
            }

            if ($current === null) {
                $current = ['title' => $trimmed, 'body' => ''];
            } else {
                $current['body'] .= ($current['body'] !== '' && ! str_ends_with($current['body'], "\n") ? "\n" : '').$trimmed;
            }
        }

        $flush();

        if ($slides === []) {
            $slides[] = ['title' => 'Presentasi', 'body' => $text];
        }

        return array_values($slides);
    }

    /**
     * @param  list<array{title?:string,body?:string}>|null  $slides
     * @return list<array{title:string,body:string}>
     */
    public static function normalize(?array $slides, ?string $outlineFallback = null): array
    {
        if (is_array($slides) && $slides !== []) {
            $out = [];
            foreach ($slides as $slide) {
                $title = trim((string) ($slide['title'] ?? ''));
                $body = trim((string) ($slide['body'] ?? ''));
                if ($title === '' && $body === '') {
                    continue;
                }
                $out[] = [
                    'title' => $title !== '' ? $title : 'Slide',
                    'body' => $body,
                ];
            }
            if ($out !== []) {
                return $out;
            }
        }

        return self::fromOutline($outlineFallback);
    }
}
