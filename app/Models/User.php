<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];

    // ──────────────────────────────────────────────────────────────────────────
    //  Filament panel access gate
    //  Employees will get their own separate panel later;
    //  the admin panel is for superadmin, subadmin and hr only.
    // ──────────────────────────────────────────────────────────────────────────
    public function canAccessPanel(Panel $panel): bool
    {
        if (!$this->is_active) {
            return false;
        }

        return true;
        // return match ($panel->getId()) {
        //     'admin' => $this->hasAnyRole(['superadmin', 'subadmin', 'hr','employee']),
        //     default => false,
        // };
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  Convenience helpers
    // ──────────────────────────────────────────────────────────────────────────
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('superadmin');
    }

    public function isSubAdmin(): bool
    {
        return $this->hasRole('subadmin');
    }

    public function isHR(): bool
    {
        return $this->hasRole('hr');
    }

    public function isEmployee(): bool
    {
        return $this->hasRole('employee');
    }

    /** Human-readable role label for the currently assigned (first) role. */
    public function getRoleLabelAttribute(): string
    {
        return match ($this->roles->first()?->name) {
            'superadmin' => 'Super Admin',
            'subadmin' => 'Sub Admin',
            'hr' => 'HR',
            'employee' => 'Employee',
            default => 'No Role',
        };
    }
}