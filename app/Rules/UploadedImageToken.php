<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Storage;

/**
 * Validates a token returned by POST /teacher/uploads/question-image.
 * The token doubles as the temp filename, so its shape is restricted to
 * "{uuid}.{ext}" to rule out path traversal before it ever touches Storage.
 */
class UploadedImageToken implements ValidationRule
{
    private const PATTERN = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}\.(jpg|jpeg|png|webp)$/i';

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || ! preg_match(self::PATTERN, $value)) {
            $fail('The :attribute is not a valid image upload token.');

            return;
        }

        if (! Storage::disk('public')->exists('tmp/'.$value)) {
            $fail('The uploaded image for :attribute has expired or was not found. Please re-upload it.');
        }
    }
}
