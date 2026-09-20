<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Question;
use App\Models\QuestionMatchingPair;
use App\Models\QuestionOption;
use App\Models\SubmissionAnswer;
use Illuminate\Support\Facades\Storage;

/**
 * Structured "what was picked" vs "what was correct" for one question — an
 * `options` array (multiple_choice/true_false) or a `matchingPairs` array
 * (matching), each entry carrying enough detail (text, image, correct/selected
 * flags) for a "View Answer" dialog to render the same option-card look as
 * the Question Bank. Shared by the teacher feedback view and the student
 * view-answer endpoint.
 */
trait DescribesQuestionAnswers
{
    private function describeAnswer(Question $question, ?SubmissionAnswer $answer): array
    {
        return match ($question->type) {
            'true_false', 'multiple_choice' => [
                'options' => $this->describeOptions($question, $answer),
            ],
            'matching' => [
                'matchingPairs' => $this->describeMatchingPairs($question, $answer),
            ],
            default => [],
        };
    }

    private function describeOptions(Question $question, ?SubmissionAnswer $answer): array
    {
        $selectedIds = $question->type === 'true_false'
            ? array_filter([$answer?->selected_option_id])
            : ($answer?->selectedOptions->pluck('question_option_id')->all() ?? []);

        return $question->options->sortBy('position')->values()
            ->map(fn (QuestionOption $option) => [
                'id' => $option->id,
                'text' => $option->text,
                'imageUrl' => $option->image_path ? Storage::disk('public')->url($option->image_path) : null,
                'isCorrect' => (bool) $option->is_correct,
                'isSelected' => in_array($option->id, $selectedIds, true),
            ])->all();
    }

    private function describeMatchingPairs(Question $question, ?SubmissionAnswer $answer): array
    {
        $matchesByLeftPair = $answer?->matches->keyBy('left_pair_id') ?? collect();

        return $question->matchingPairs->sortBy('position')->values()
            ->map(function (QuestionMatchingPair $pair) use ($matchesByLeftPair) {
                $match = $matchesByLeftPair->get($pair->id);
                $selected = $match?->selectedRightPair;

                return [
                    'id' => $pair->id,
                    'leftText' => $pair->left_text,
                    'leftImageUrl' => $pair->left_image_path ? Storage::disk('public')->url($pair->left_image_path) : null,
                    'rightText' => $pair->right_text,
                    'rightImageUrl' => $pair->right_image_path ? Storage::disk('public')->url($pair->right_image_path) : null,
                    'answered' => $match !== null,
                    'isCorrect' => $match !== null && $match->selected_right_pair_id === $pair->id,
                    'selectedRightText' => $selected?->right_text,
                    'selectedRightImageUrl' => $selected?->right_image_path ? Storage::disk('public')->url($selected->right_image_path) : null,
                ];
            })->all();
    }
}
