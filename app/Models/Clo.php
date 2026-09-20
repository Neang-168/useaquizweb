<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Clo extends Model
{
    use HasFactory;

    protected $fillable = [
        'plo_id',
        'subject_id',
        'code',
        'title',
        'description',
        'bloom_level',
        'status',
        'created_by',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function plo(): BelongsTo
    {
        return $this->belongsTo(Plo::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function llos(): HasMany
    {
        return $this->hasMany(Llo::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
