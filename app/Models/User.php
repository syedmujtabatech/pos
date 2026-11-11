<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role_id',
        'phone',
        'is_active',
        'failed_login_attempts',
        'locked_until',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'locked_until' => 'datetime',
            'last_login_at' => 'datetime',
            'is_active' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function hasRole(string|array $roles): bool
    {
        if (!$this->role) return false;
        if (is_array($roles)) {
            return in_array($this->role->slug, $roles);
        }
        return $this->role->slug === $roles;
    }

    public function hasPermission(string $slug): bool
    {
        if ($this->isOwner()) {
            return true;
        }

        if (!$this->role) {
            return false;
        }

        // Check in loaded relationship or query
        if ($this->relationLoaded('role') && $this->role->relationLoaded('permissions')) {
            return $this->role->permissions->contains('slug', $slug);
        }

        return $this->role->permissions()->where('slug', $slug)->exists();
    }

    public function hasAnyPermission(array $slugs): bool
    {
        if ($this->isOwner()) {
            return true;
        }

        foreach ($slugs as $slug) {
            if ($this->hasPermission($slug)) {
                return true;
            }
        }

        return false;
    }

    public function isOwner(): bool
    {
        return $this->hasRole('owner') || $this->hasRole('super_admin');
    }

    public function isManager(): bool
    {
        return $this->hasRole(['owner', 'super_admin', 'manager']);
    }

    public function isSalesman(): bool
    {
        return $this->hasRole('salesman');
    }

    public function isCashier(): bool
    {
        return $this->hasRole('cashier');
    }

    public function isStoreKeeper(): bool
    {
        return $this->hasRole('store_keeper');
    }

    public function isAccountant(): bool
    {
        return $this->hasRole('accountant');
    }

    public function isPurchaseOfficer(): bool
    {
        return $this->hasRole('purchase_officer');
    }

    public function assignedCustomers()
    {
        return $this->hasMany(Customer::class, 'assigned_salesman_id');
    }

    public function sentMessages()
    {
        return $this->hasMany(InternalMessage::class, 'sender_id');
    }

    public function receivedMessages()
    {
        return $this->hasMany(InternalMessage::class, 'receiver_id');
    }

    public function unreadMessagesCount(): int
    {
        return $this->receivedMessages()->where('is_read', false)->count();
    }
}
