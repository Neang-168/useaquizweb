<?php

namespace App\Services;

use App\Models\Question;
use App\Models\QuizSubmission;
use App\Models\SubmissionAnswer;

/**
 * Computes a submission's mcq_score from its answers and each question's
 * points. Runs automatically whenever a QuizSubmission is saved with
 * status "submitted" (see QuizSubmission::booted()) — nothing in the app
 * creates a submission yet, but whatever eventually does (the still-missing
 * student quiz-taking flow) only needs to write the raw answers and set
 * status; grading happens on its own from there.
 *
 * Scoring rules, per question type:
 * - true_false: full points if the selected option is the correct one.
 * - multiple_choice: all-or-nothing — full points only if the selected set
 *   of options exactly matches the correct set (this app allows more than
 *   one correct option), otherwise zero. No partial credit, since there's
 *   no well-defined "how wrong" measure for an arbitrary subset match.
 * - matching: partial credit — points are split evenly across the
 *   question's pairs, awarded per pair the student matched correctly.
 *
 * Every question in the quiz counts, answered or not (an unanswered
 * question scores 0 and its submission_answers row, if any, is left
 * untouched).
 */
class QuizGradingService
{
    public function grade(QuizSubmission $submission): void
    {
        $quiz = $submission->quiz()->with(['questions.options', 'questions.matchingPairs'])->first();

        if (! $quiz) {
            return;
        }

        $answersByQuestion = $submission->answers()
            ->with('selectedOptions', 'matches')
            ->get()
            ->keyBy('question_id');

        $total = 0;

        foreach ($quiz->questions as $question) {
            $answer = $answersByQuestion->get($question->id);

            if (! $answer) {
                continue;
            }

            $points = $quiz->pointsFor($question);
            ['awarded' => $awarded, 'correct' => $correct] = $this->scoreAnswer($question, $answer, $points);
            $total += $awarded;

            $answer->forceFill([
                'awarded_score' => $awarded,
                'is_correct' => $correct,
            ])->saveQuietly();
        }

        $submission->forceFill([
            'mcq_score' => $total,
            'total_points' => $quiz->computeTotalPoints(),
            'pass_mark' => $quiz->pass_mark ?? 50,
        ])->saveQuietly();
    }

    /**
     * @return array{awarded: int, correct: bool}
     */
    private function scoreAnswer(Question $question, SubmissionAnswer $answer, int $points): array
    {
        return match ($question->type) {
            'true_false' => $this->scoreTrueFalse($question, $answer, $points),
            'multiple_choice' => $this->scoreMultipleChoice($question, $answer, $points),
            'matching' => $this->scoreMatching($question, $answer, $points),
            default => ['awarded' => 0, 'correct' => false],
        };
    }

    private function scoreTrueFalse(Question $question, SubmissionAnswer $answer, int $points): array
    {
        $selected = $question->options->firstWhere('id', $answer->selected_option_id);
        $correct = (bool) $selected?->is_correct;

        return ['awarded' => $correct ? $points : 0, 'correct' => $correct];
    }

    private function scoreMultipleChoice(Question $question, SubmissionAnswer $answer, int $points): array
    {
        $correctIds = $question->options->where('is_correct', true)->pluck('id')->sort()->values()->all();
        $selectedIds = $answer->selectedOptions->pluck('question_option_id')->sort()->values()->all();
        $correct = $correctIds === $selectedIds;

        return ['awarded' => $correct ? $points : 0, 'correct' => $correct];
    }

    /**
     * Partial credit: points are split evenly across pairs, awarded per pair
     * matched correctly. "Correct" (for display) means every pair was
     * matched — kept separate from the awarded score, since rounding the
     * split can make a partial score equal to $points (e.g. 1 point over 2
     * pairs, 1 right: round(1 * 1/2) = 1) without every pair being right.
     */
    private function scoreMatching(Question $question, SubmissionAnswer $answer, int $points): array
    {
        $pairCount = $question->matchingPairs->count();

        if ($pairCount === 0) {
            return ['awarded' => 0, 'correct' => false];
        }

        $correctCount = $answer->matches
            ->filter(fn ($match) => $match->left_pair_id !== null && $match->left_pair_id === $match->selected_right_pair_id)
            ->count();

        return [
            'awarded' => (int) round($points * $correctCount / $pairCount),
            'correct' => $correctCount === $pairCount,
        ];
    }
}
