<?php

namespace App\Support\QuestionImportExport;

use App\Models\Question;
use Illuminate\Support\Collection;

/**
 * Reads and writes a practical subset of Moodle's GIFT plain-text quiz
 * format: multiple choice, true/false, and matching questions. Subject and
 * difficulty (neither of which exist in real GIFT) ride along as `//`
 * comment directives and a `$CATEGORY:` directive, which are both already
 * part of the GIFT spec, so a plain GIFT file without them still parses —
 * subject_code just falls back to blank (and fails validation) and
 * difficulty defaults to Medium.
 *
 * This is a deliberately simplified subset: backslash-escaped special
 * characters (\{, \}, \=, \~, \#, \:) and GIFT's weighted/partial-credit
 * answers and per-answer feedback text are not supported. Weights and
 * feedback are stripped gracefully rather than breaking the parse, since
 * this app's schema has no equivalent for them.
 */
class GiftCodec
{
    /**
     * Render this teacher's questions as one GIFT file, grouping
     * consecutive questions under a $CATEGORY: line per subject so it
     * isn't repeated for every single question.
     */
    public static function export(Collection $questions): string
    {
        $lines = [];
        $currentSubjectCode = null;
        $counter = 0;

        foreach ($questions as $question) {
            /** @var Question $question */
            $counter++;
            $subjectCode = $question->subject?->code ?? '';

            if ($subjectCode !== '' && $subjectCode !== $currentSubjectCode) {
                $lines[] = "\$CATEGORY: {$subjectCode}";
                $lines[] = '';
                $currentSubjectCode = $subjectCode;
            }

            $lines[] = "// Difficulty: {$question->difficulty}";
            $lines[] = "// Points: {$question->points}";
            $lines[] = self::questionToGift($question, $counter);
            $lines[] = '';
        }

        return implode("\n", $lines);
    }

    private static function questionToGift(Question $question, int $counter): string
    {
        $stem = $question->title !== null && $question->title !== '' ? $question->title : '(image question)';
        $label = "::Q{$counter}:: {$stem}";

        if ($question->type === 'true_false') {
            $trueOption = $question->options->firstWhere('text', 'True');
            $answer = $trueOption?->is_correct ? 'TRUE' : 'FALSE';

            return "{$label} {{$answer}}";
        }

        if ($question->type === 'multiple_choice') {
            $body = $question->options->sortBy('position')->map(
                fn ($option) => ($option->is_correct ? '=' : '~').($option->text ?? '')
            )->implode("\n\t");

            return "{$label} {\n\t{$body}\n}";
        }

        if ($question->type === 'matching') {
            $body = $question->matchingPairs->sortBy('position')->map(
                fn ($pair) => '='.($pair->left_text ?? '').' -> '.($pair->right_text ?? '')
            )->implode("\n\t");

            return "{$label} {\n\t{$body}\n}";
        }

        return $label;
    }

    /**
     * Parse a GIFT file into the normalized shape shared with the
     * spreadsheet importer, one entry per question block found, each
     * tagged with a 'source' label for error reporting.
     */
    public static function parse(string $content): array
    {
        $content = str_replace(["\r\n", "\r"], "\n", $content);

        // Braces mentioned inside a // comment (e.g. a syntax example in
        // instructions) must never be mistaken for a real question block,
        // so strip them from comment lines before block-splitting. This
        // doesn't affect directive detection, which matches on the text
        // that follows "//", not on braces.
        $sanitized = preg_replace_callback(
            '/^[ \t]*\/\/.*$/m',
            fn ($m) => str_replace(['{', '}'], '', $m[0]),
            $content
        );

        preg_match_all('/([^{}]*)\{([^{}]*)\}/s', $sanitized, $matches, PREG_SET_ORDER);

        $parsed = [];
        $currentSubjectCode = null;

        foreach ($matches as $index => $match) {
            [$directives, $stem] = self::extractDirectives($match[1]);
            $body = trim($match[2]);

            if (array_key_exists('category', $directives)) {
                $currentSubjectCode = $directives['category'];
            }

            if ($stem === '') {
                // A stray {...} with no preceding text isn't a question block.
                continue;
            }

            $question = [
                'type' => self::detectType($body),
                'subject_code' => $directives['subject'] ?? $currentSubjectCode ?? '',
                'difficulty' => self::normalizeDifficulty($directives['difficulty'] ?? null),
                'points' => self::normalizePoints($directives['points'] ?? null),
                'title' => $stem,
                'options' => [],
                'tfCorrect' => null,
                'matchingPairs' => [],
                'source' => 'question '.($index + 1),
            ];

            self::fillAnswerBody($question, $body);

            $parsed[] = $question;
        }

        return $parsed;
    }

    /**
     * Pulls $CATEGORY / // Subject: / // Difficulty: directive lines out of
     * the text preceding a {...} block, and strips the optional ::Title::
     * label, leaving just the question stem.
     */
    private static function extractDirectives(string $precedingText): array
    {
        $directives = [];
        $stemLines = [];

        foreach (explode("\n", $precedingText) as $line) {
            $trimmed = trim($line);

            if ($trimmed === '') {
                continue;
            }

            if (preg_match('/^\$CATEGORY:\s*(.+)$/i', $trimmed, $m)) {
                $directives['category'] = trim($m[1]);

                continue;
            }

            if (preg_match('/^\/\/\s*Subject:\s*(.+)$/i', $trimmed, $m)) {
                $directives['subject'] = trim($m[1]);

                continue;
            }

            if (preg_match('/^\/\/\s*Difficulty:\s*(.+)$/i', $trimmed, $m)) {
                $directives['difficulty'] = trim($m[1]);

                continue;
            }

            if (preg_match('/^\/\/\s*Points:\s*(.+)$/i', $trimmed, $m)) {
                $directives['points'] = trim($m[1]);

                continue;
            }

            if (str_starts_with($trimmed, '//')) {
                // An inert comment line — ignore it.
                continue;
            }

            $stemLines[] = $line;
        }

        $stem = trim(implode("\n", $stemLines));

        if (preg_match('/^::(.*?)::\s*(.*)$/s', $stem, $m)) {
            $stem = trim($m[2]);
        }

        return [$directives, $stem];
    }

    private static function detectType(string $body): string
    {
        $trimmed = strtoupper(trim($body));

        if (in_array($trimmed, ['TRUE', 'FALSE', 'T', 'F'], true)) {
            return 'true_false';
        }

        if (preg_match('/=[^=~]*->/', $body)) {
            return 'matching';
        }

        return 'multiple_choice';
    }

    private static function fillAnswerBody(array &$question, string $body): void
    {
        if ($question['type'] === 'true_false') {
            $question['tfCorrect'] = in_array(strtoupper(trim($body)), ['TRUE', 'T'], true) ? 'True' : 'False';

            return;
        }

        preg_match_all('/([=~])\s*(.*?)(?=[=~]|$)/s', $body, $entries, PREG_SET_ORDER);

        foreach ($entries as $entry) {
            $isCorrect = $entry[1] === '=';
            $text = trim($entry[2]);
            // Strip GIFT feedback (#...) and percentage weighting (%50%),
            // neither of which this app's schema has room for.
            $text = trim(preg_split('/#/', $text, 2)[0]);
            $text = preg_replace('/^%-?\d+(\.\d+)?%\s*/', '', $text);

            if ($text === '') {
                continue;
            }

            if ($question['type'] === 'matching') {
                $sides = preg_split('/\s*->\s*/', $text, 2);
                $question['matchingPairs'][] = [
                    'leftText' => trim($sides[0] ?? ''),
                    'rightText' => trim($sides[1] ?? ''),
                ];
            } else {
                $question['options'][] = ['text' => $text, 'isCorrect' => $isCorrect];
            }
        }
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
}
