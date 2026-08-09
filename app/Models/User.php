<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'role_id',
        'username',
        'email',
        'password',
        'first_name',
        'last_name',
        'name_kh',
        'gender',
        'dob',
        'phone',
        'avatar',
        'address',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The accessors to append to the model's array/JSON form.
     */
    protected $appends = [
        'avatar_url',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'dob' => 'date',
            'status' => 'boolean',
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * User belongs to one role.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * User has one admin profile.
     */
    public function adminProfile(): HasOne
    {
        return $this->hasOne(AdminProfile::class);
    }

    /**
     * User has one teacher profile.
     */
    public function teacherProfile(): HasOne
    {
        return $this->hasOne(TeacherProfile::class);
    }

    /**
     * User has one student profile.
     */
    public function studentProfile(): HasOne
    {
        return $this->hasOne(StudentProfile::class);
    }

    /**
     * Get user's full name.
     */
    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    /**
     * Get the public URL of the user's avatar, if any.
     *
     * Deliberately relative (not built from APP_URL) so it resolves against
     * whatever origin is currently serving the SPA — the same reasoning the
     * frontend's axios client uses a relative "/api" base instead of an
     * absolute one.
     */
    public function getAvatarUrlAttribute(): ?string
    {
        return $this->avatar ? '/storage/' . ltrim($this->avatar, '/') : null;
    }

    /**
     * Check if user has a specific role.
     */
    public function hasRole(string $role): bool
    {
        return $this->role?->name === $role;
    }

    /**
     * Check if user has one of the given roles.
     */
    public function hasAnyRole(array $roles): bool
    {
        return $this->role && in_array($this->role->name, $roles);
    }

    /**
     * Check if user has a specific permission.
     */
    public function hasPermission(string $permission): bool
    {
        return $this->role
            ? $this->role->permissions()
                ->where('name', $permission)
                ->exists()
            : false;
    }

    /**
     * Check if user has any of the given permissions.
     */
    public function hasAnyPermission(array $permissions): bool
    {
        if (!$this->role) {
            return false;
        }

        return $this->role
            ->permissions()
            ->whereIn('name', $permissions)
            ->exists();
    }

    /**
     * Check if user is active.
     */
    public function isActive(): bool
    {
        return $this->status === true;
    }
}
