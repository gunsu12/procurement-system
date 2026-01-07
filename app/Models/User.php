<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'username',
        'password',
        'role',
        'default_item_purchasing',
        'unit_id',
        'company_id',
        'sso_id',
        'employee_id',
        'department',
        'position',
        'avatar_url',
        'last_sso_sync',
        'is_first_login',
        'password_reset_at',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_sso_sync' => 'datetime',
        'password_reset_at' => 'datetime',
    ];

    /**
     * Check if user is synced from SSO
     */
    public function isSSOUser()
    {
        return !empty($this->sso_id);
    }

    /**
     * Get user by SSO ID or create new
     */
    public static function findOrCreateFromSSO($ssoUser)
    {
        $ssoId = $ssoUser->sub ?? $ssoUser->id ?? null;

        if (!$ssoId) {
            throw new \Exception('No SSO ID (sub or id) found in user info');
        }

        $user = static::where('sso_id', $ssoId)
            ->orWhere('email', $ssoUser->email)
            ->first();

        if ($user) {
            // Update existing user with latest SSO data
            $user->update([
                'sso_id' => $ssoId,
                'name' => $ssoUser->name,
                'email' => $ssoUser->email,
                'employee_id' => $ssoUser->employee_id ?? null,
                'department' => $ssoUser->department ?? null,
                'position' => $ssoUser->position ?? null,
                'avatar_url' => $ssoUser->avatar_url ?? null,
                'last_sso_sync' => now(),
            ]);
        } else {
            // Create new user from SSO
            $user = static::create([
                'sso_id' => $ssoId,
                'name' => $ssoUser->name,
                'email' => $ssoUser->email,
                // Default role/unit/company might need handling here or defaulting
                'role' => 'user', // Default role
                'username' => explode('@', $ssoUser->email)[0], // Generate username
                'employee_id' => $ssoUser->employee_id ?? null,
                'department' => $ssoUser->department ?? null,
                'position' => $ssoUser->position ?? null,
                'avatar_url' => $ssoUser->avatar_url ?? null,
                'password' => bcrypt(\Illuminate\Support\Str::random(32)), // Random password
                'last_sso_sync' => now(),
                'is_first_login' => true, // Flag for password reset requirement
            ]);
        }

        return $user;
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function requests()
    {
        return $this->hasMany(ProcurementRequest::class);
    }



    // Helper to check role
    public function hasRole($role)
    {
        return $this->role === $role;
    }

    public function adminlte_profile_url()
    {
        return route('profile.show');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty();
    }
}
