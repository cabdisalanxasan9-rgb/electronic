<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\AuditLog;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'is_admin', 'role'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_SUPER_ADMIN = 'super_admin';
    public const ROLE_SALES_ADMIN = 'sales_admin';
    public const ROLE_INVENTORY_ADMIN = 'inventory_admin';
    public const ROLE_CUSTOMER = 'customer';

    public const ADMIN_ROLES = [
        self::ROLE_SUPER_ADMIN,
        self::ROLE_SALES_ADMIN,
        self::ROLE_INVENTORY_ADMIN,
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
            'is_admin' => 'boolean',
            'loyalty_points' => 'integer',
        ];
    }

    public function isAdmin(): bool
    {
        return (bool) $this->is_admin || in_array((string) $this->role, self::ADMIN_ROLES, true);
    }

    public function isSuperAdmin(): bool
    {
        return (string) $this->role === self::ROLE_SUPER_ADMIN || $this->isLegacyAdmin();
    }

    private function isLegacyAdmin(): bool
    {
        return (bool) $this->is_admin && in_array((string) $this->role, ['', self::ROLE_CUSTOMER], true);
    }

    public function canManageProducts(): bool
    {
        return $this->isSuperAdmin() || (string) $this->role === self::ROLE_INVENTORY_ADMIN;
    }

    public function canManageOrders(): bool
    {
        return $this->isSuperAdmin() || (string) $this->role === self::ROLE_SALES_ADMIN;
    }

    public function canManageUsers(): bool
    {
        return $this->isSuperAdmin();
    }

    public function roleLabel(): string
    {
        return match ((string) $this->role) {
            self::ROLE_SUPER_ADMIN => 'Super Admin',
            self::ROLE_SALES_ADMIN => 'Sales Admin',
            self::ROLE_INVENTORY_ADMIN => 'Inventory Admin',
            self::ROLE_CUSTOMER => $this->isLegacyAdmin() ? 'Admin' : 'Customer',
            default => 'Customer',
        };
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(UserAddress::class);
    }

    public function returns(): HasMany
    {
        return $this->hasMany(OrderReturn::class);
    }
}
