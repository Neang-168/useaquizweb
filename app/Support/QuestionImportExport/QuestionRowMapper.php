<?php

namespace App\Support\QuestionImportExport;

use App\Models\Question;

/**
 * Maps between the app's question shape and the flat spreadsheet row shape
 * used for CSV/XLSX import & export. Also holds the business-rule
 * validation shared by every import format (spreadsheet and GIFT alike),
 * so "at least 2 options" etc. can't drift between the two parsers.
 *
 * Bulk import is text-only — images stay an editor-only affordance and can
 * be attached to an imported question afterward.
 */
class QuestionRowMapper
{
    public const OPTION_SLOTS = 8;

    public const PAIR_SLOTS = 8;

    public static function headers(): array
    {
        $headers = ['type', 'subject_code', 'difficulty', 'points', 'title', 'correct_options'];

        for ($i = 1; $i <= self::OPTION_SLOTS; $i++) {
            $headers[] = "option_{$i}";
        }

        for ($i = 1; $i <= self::PAIR_SLOTS; $i++) {
            $headers[] = "left_{$i}";
            $headers[] = "right_{$i}";
        }

        return $headers;
    }

    /**
     * Turn one of this teacher's existing questions into a flat row, keyed
     * by header name, for export.
     */
    public static function toRow(Question $question): array
    {
        $row = array_fill_keys(self::headers(), '');
        $row['type'] = $question->type;
        $row['subject_code'] = $question->subject?->code ?? '';
        $row['difficulty'] = $question->difficulty;
        $row['points'] = $question->points;
        $row['title'] = $question->title ?? '';

        if ($question->type === 'multiple_choice') {
            $options = $question->options->sortBy('position')->values();
            $correctLetters = [];

            foreach ($options as $index => $option) {
                if ($index >= self::OPTION_SLOTS) {
                    break;
                }

                $row['option_'.($index + 1)] = $option->text ?? '';

                if ($option->is_correct) {
                    $correctLetters[] = chr(65 + $index);
                }
            }

            $row['correct_options'] = implode(',', $correctLetters);
        } elseif ($question->type === 'true_false') {
            $trueOption = $question->options->firstWhere('text', 'True');
            $row['correct_options'] = $trueOption?->is_correct ? 'True' : 'False';
        } elseif ($question->type === 'matching') {
            $pairs = $question->matchingPairs->sortBy('position')->values();

            foreach ($pairs as $index => $pair) {
                if ($index >= self::PAIR_SLOTS) {
                    break;
                }

                $row['left_'.($index + 1)] = $pair->left_text ?? '';
                $row['right_'.($index + 1)] = $pair->right_text ?? '';
            }
        }

        return $row;
    }

    /**
     * Two example rows (one MC, one matching) used to seed the downloadable
     * import template.
     */
    public static function sampleRows(): array
    {
        $mc = array_fill_keys(self::headers(), '');
        $mc['type'] = 'multiple_choice';
        $mc['subject_code'] = 'e.g. MATH101';
        $mc['difficulty'] = 'Medium';
        $mc['points'] = 1;
        $mc['title'] = 'What is 2 + 2?';
        $mc['correct_options'] = 'B';
        $mc['option_1'] = '3';
        $mc['option_2'] = '4';
        $mc['option_3'] = '5';

        $tf = array_fill_keys(self::headers(), '');
        $tf['type'] = 'true_false';
        $tf['subject_code'] = 'e.g. MATH101';
        $tf['difficulty'] = 'Easy';
        $tf['points'] = 1;
        $tf['title'] = 'The sky is blue.';
        $tf['correct_options'] = 'True';

        $matching = array_fill_keys(self::headers(), '');
        $matching['type'] = 'matching';
        $matching['subject_code'] = 'e.g. MATH101';
        $matching['difficulty'] = 'Hard';
        $matching['points'] = 2;
        $matching['title'] = 'Match each capital to its country.';
        $matching['left_1'] = 'France';
        $matching['right_1'] = 'Paris';
        $matching['left_2'] = 'Japan';
        $matching['right_2'] = 'Tokyo';

        return [$mc, $tf, $matching];
    }

    /**
     * Parse one spreadsheet row into the normalized shape shared with the
     * GIFT parser: ['type', 'subject_code', 'difficulty', 'title',
     * 'options' => [['text','isCorrect']], 'tfCorrect', 'matchingPairs' =>
     * [['leftText','rightText']]]. Returns null for a blank/instructional
     * row (no type given) rather than an error.
     */
    public static function fromRow(array $row): ?array
    {
        $row = array_change_key_case($row, CASE_LOWER);
        $type = strtolower(trim((string) ($row['type'] ?? '')));

        if ($type === '' || str_starts_with($type, '#') || str_starts_with($type, '//')) {
            return null;
        }

        $parsed = [
            'type' => $type,
            'subject_code' => trim((string) ($row['subject_code'] ?? '')),
            'difficulty' => self::normalizeDifficulty($row['difficulty'] ?? null),
            'points' => self::normalizePoints($row['points'] ?? null),
            'title' => trim((string) ($row['title'] ?? '')),
            'options' => [],
            'tfCorrect' => null,
            'matchingPairs' => [],
        ];

        if ($type === 'multiple_choice') {
            $correctLetters = array_map(
                fn ($letter) => strtoupper(trim($letter)),
                explode(',', (string) ($row['correct_options'] ?? ''))
            );

            for ($i = 1; $i <= self::OPTION_SLOTS; $i++) {
                $text = trim((string) ($row["option_{$i}"] ?? ''));

                if ($text === '') {
                    continue;
                }

                $parsed['options'][] = [
                    'text' => $text,
                    'isCorrect' => in_array(chr(64 + $i), $correctLetters, true),
                ];
            }
        } elseif ($type === 'true_false') {
            $answer = strtolower(trim((string) ($row['correct_options'] ?? '')));
            $parsed['tfCorrect'] = in_array($answer, ['true', 't', '1'], true) ? 'True' : 'False';
        } elseif ($type === 'matching') {
            for ($i = 1; $i <= self::PAIR_SLOTS; $i++) {
                $left = trim((string) ($row["left_{$i}"] ?? ''));
                $right = trim((string) ($row["right_{$i}"] ?? ''));

                if ($left === '' && $right === '') {
                    continue;
                }

                $parsed['matchingPairs'][] = ['leftText' => $left, 'rightText' => $right];
            }
        }

        return $parsed;
    }

    private static function normalizeDifficulty(?string $value): string
    {
        $value = ucfirst(strtolower(trim((string) $value)));

        return in_array($value, ['Easy', 'Medium', 'Hard'], true) ? $value : 'Medium';
    }

    private static function normalizePoints(mixed $value): int
    {
        $value = trim((string) $value);

        return $value !== '' && ctype_digit($value) && (int) $value >= 1 ? (int) $value : 1;
    }

    /**
     * Business-rule validation shared by every import format. Returns a
     * list of human-readable error strings; empty means valid.
     */
    public static function validate(array $parsed): array
    {
        $errors = [];

        if (! in_array($parsed['type'], ['multiple_choice', 'true_false', 'matching'], true)) {
            $errors[] = "Unknown question type \"{$parsed['type']}\" (must be multiple_choice, true_false, or matching).";

            return $errors;
        }

        if ($parsed['title'] === '') {
            $errors[] = 'Missing question title.';
        }

        if ($parsed['subject_code'] === '') {
            $errors[] = 'Missing subject_code.';
        }

        if ($parsed['type'] === 'multiple_choice') {
            $filled = array_filter($parsed['options'], fn ($o) => trim($o['text']) !== '');

            if (count($filled) < 2) {
                $errors[] = 'A multiple choice question needs at least 2 answer options.';
            } elseif (count(array_filter($filled, fn ($o) => $o['isCorrect'])) < 1) {
                $errors[] = 'At least one answer option must be marked correct (see correct_options).';
            }
        }

        if ($parsed['type'] === 'matching') {
            $complete = array_filter(
                $parsed['matchingPairs'],
                fn ($p) => trim($p['leftText']) !== '' && trim($p['rightText']) !== ''
            );

            if (count($complete) < 2) {
                $errors[] = 'A matching question needs at least 2 complete pairs.';
            }
        }

        return $errors;
    }
}
