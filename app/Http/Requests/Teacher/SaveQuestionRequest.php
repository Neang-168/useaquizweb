<?php

namespace App\Http\Requests\Teacher;

use App\Models\Question;
use App\Rules\UploadedImageToken;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Shared validation for creating and updating a teacher's question bank
 * entries. Used for both POST /teacher/questions and PUT /teacher/questions/{question}.
 */
class SaveQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'subject_id' => ['required', 'exists:subjects,id'],
            'type' => ['required', Rule::in(['multiple_choice', 'true_false', 'matching'])],
            'difficulty' => ['required', Rule::in(['Easy', 'Medium', 'Hard'])],
            'points' => ['required', 'integer', 'min:1', 'max:1000'],

            // Question stem: text is no longer unconditionally required,
            // since an image can stand in for it (see withValidator()).
            'title' => ['nullable', 'string'],
            'image_token' => ['nullable', 'string', new UploadedImageToken],
            'image_alt' => ['nullable', 'string', 'max:255'],
            'remove_image' => ['nullable', 'boolean'],

            'options' => ['required_if:type,multiple_choice', 'array'],
            'options.*.text' => ['nullable', 'string'],
            'options.*.isCorrect' => ['boolean'],
            'options.*.image_token' => ['nullable', 'string', new UploadedImageToken],
            'options.*.remove_image' => ['nullable', 'boolean'],

            'tfCorrect' => ['required_if:type,true_false', Rule::in(['True', 'False'])],

            'matchingPairs' => ['required_if:type,matching', 'array'],
            'matchingPairs.*.leftText' => ['nullable', 'string'],
            'matchingPairs.*.leftImageToken' => ['nullable', 'string', new UploadedImageToken],
            'matchingPairs.*.removeLeftImage' => ['nullable', 'boolean'],
            'matchingPairs.*.rightText' => ['nullable', 'string'],
            'matchingPairs.*.rightImageToken' => ['nullable', 'string', new UploadedImageToken],
            'matchingPairs.*.removeRightImage' => ['nullable', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $type = $this->input('type');

            /** @var Question|null $question */
            $question = $this->route('question');

            $this->validateStem($validator, $question);

            if ($type === 'multiple_choice') {
                $this->validateOptions($validator, $question);
            }

            if ($type === 'matching') {
                $this->validateMatchingPairs($validator, $question);
            }
        });
    }

    /**
     * An element "has content" once it carries text, a fresh upload, or
     * (on update, when not explicitly removed) an image it already has.
     */
    private function hasContent(?string $text, ?string $token, bool $removeImage, ?string $existingImagePath): bool
    {
        if (trim((string) $text) !== '') {
            return true;
        }

        if ($token) {
            return true;
        }

        if ($removeImage) {
            return false;
        }

        return filled($existingImagePath);
    }

    private function validateStem(Validator $validator, ?Question $question): void
    {
        $hasContent = $this->hasContent(
            $this->input('title'),
            $this->input('image_token'),
            (bool) $this->boolean('remove_image'),
            $question?->image_path,
        );

        if (! $hasContent) {
            $validator->errors()->add('title', 'The question needs a title, an image, or both.');
        }
    }

    private function validateOptions(Validator $validator, ?Question $question): void
    {
        $existing = $question && $question->type === 'multiple_choice'
            ? $question->options()->orderBy('position')->pluck('image_path')->values()
            : collect();

        $filled = collect($this->input('options', []))->filter(
            fn ($option, $index) => $this->hasContent(
                $option['text'] ?? null,
                $option['image_token'] ?? null,
                (bool) ($option['remove_image'] ?? false),
                $existing->get($index),
            )
        );

        if ($filled->count() < 2) {
            $validator->errors()->add('options', 'A multiple choice question needs at least 2 answer options (text, image, or both).');

            return;
        }

        $correctCount = $filled->filter(fn ($option) => (bool) ($option['isCorrect'] ?? false))->count();

        if ($correctCount < 1) {
            $validator->errors()->add('options', 'At least one answer option must be marked as correct.');
        }
    }

    private function validateMatchingPairs(Validator $validator, ?Question $question): void
    {
        $existing = $question && $question->type === 'matching'
            ? $question->matchingPairs()->orderBy('position')->get(['left_image_path', 'right_image_path'])->values()
            : collect();

        $complete = collect($this->input('matchingPairs', []))->filter(function ($pair, $index) use ($existing) {
            $row = $existing->get($index);

            $leftFilled = $this->hasContent(
                $pair['leftText'] ?? null,
                $pair['leftImageToken'] ?? null,
                (bool) ($pair['removeLeftImage'] ?? false),
                $row?->left_image_path,
            );

            $rightFilled = $this->hasContent(
                $pair['rightText'] ?? null,
                $pair['rightImageToken'] ?? null,
                (bool) ($pair['removeRightImage'] ?? false),
                $row?->right_image_path,
            );

            return $leftFilled && $rightFilled;
        });

        if ($complete->count() < 2) {
            $validator->errors()->add('matchingPairs', 'A matching question needs at least 2 complete pairs (each side needs text, an image, or both).');
        }
    }
}
