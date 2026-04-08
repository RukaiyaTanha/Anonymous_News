<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getNameAttribute(): ?string
    {
        return $this->attributes['username'] ?? null;
    }

    public function setNameAttribute(?string $value): void
    {
        $this->attributes['username'] = $value;
    }

    public function getProfilePhotoUrlAttribute(): ?string
    {
        $path = $this->profile_photo_path;

        if (! $path) {
            return null;
        }

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        $normalizedPath = ltrim(str_replace('\\', '/', $path), '/');

        if (str_starts_with($normalizedPath, 'storage/')) {
            $normalizedPath = substr($normalizedPath, 8);
        }

        if ($normalizedPath === '' || ! Storage::disk('public')->exists($normalizedPath)) {
            return null;
        }

        return '/storage/' . $normalizedPath;
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }
    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    // User has many reports
    public function reports()
    {
        return $this->hasMany(Report::class);
    }

    // User has many votes
    public function votes()
    {
        return $this->hasMany(Vote::class);
    }

    // User has many flags
    public function flags()
    {
        return $this->hasMany(Flag::class);
    }

    // User has many notifications
    public function userNotifications()
    {
        return $this->hasMany(Notification::class);
    }

    // Admin reviewed reports
    public function reviewedReports()
    {
        return $this->hasMany(Report::class, 'reviewed_by');
    }

    // Admin audit logs
    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class, 'admin_id');
    }
}
