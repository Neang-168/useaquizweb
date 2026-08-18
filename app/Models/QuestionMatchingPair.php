<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestionMatchingPair extends Model
{
    use HasFactory;

    protected $fillable = [
        'question_id',
        'left_text',
        'left_image_path',
        'right_text',
        'right_image_path',
        'position',
    ];

    protected $casts = [
        'position' => 'integer',
    ];

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }
}
