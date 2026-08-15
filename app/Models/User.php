<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

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
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
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
            'password'          => 'hashed',
            'is_active'         => 'boolean',
            'is_super'          => 'boolean',
        ];
    }

    public function companies()
    {
        return $this->belongsToMany(Company::class, 'company_user')
            ->withPivot('role', 'role_id', 'employee_id', 'is_active')
            ->withTimestamps();
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->is_super) {
            return true;
        }

        return in_array($permission, $this->resolvedPermissions());
    }

    public function isAdmin(): bool
    {
        if ($this->is_super) {
            return true;
        }

        return collect($this->resolvedPermissions())
            ->contains(fn ($p) => str_starts_with($p, 'admin.'));
    }

    /** @var array<string>|null */
    private ?array $resolvedPermissionsCache = null;

    private function resolvedPermissions(): array
    {
        if ($this->resolvedPermissionsCache !== null) {
            return $this->resolvedPermissionsCache;
        }

        $roleIds = $this->companies()
            ->wherePivot('is_active', true)
            ->get()
            ->pluck('pivot.role_id')
            ->filter()
            ->unique()
            ->toArray();

        if (empty($roleIds)) {
            return $this->resolvedPermissionsCache = [];
        }

        return $this->resolvedPermissionsCache = Permission::whereHas(
            'roles', fn ($q) => $q->whereIn('roles.id', $roleIds)
        )->pluck('name')->toArray();
    }
}
