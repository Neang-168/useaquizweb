<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Stage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'order_no',
        'status',
    ];

    protected $casts = [
        'order_no' => 'integer',
        'status' => 'boolean',
    ];

    public function studentEnrollments(): HasMany
    {
        return $this->hasMany(StudentEnrollment::class);
    }
}