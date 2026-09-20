<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Llo extends Model
{
    use HasFactory;

    protected $fillable = [
        'clo_id',
        'code',
        'title',
        'description',
        'bloom_level',
        'lesson_no',
        'status',
        'created_by',
    ];

    protected $casts = [
        'status' => 'boolean',
        'lesson_no' => 'integer',
    ];

    public function clo(): BelongsTo
    {
        return $this->belongsTo(Clo::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
