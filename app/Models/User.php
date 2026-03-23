<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'po_id',
        'name',
        'email',
        'password',
        'role',
        'phone',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
        'is_active'         => 'boolean',
    ];

    // -------------------------------------------------------------------------
    // Role constants — pakai konstanta biar tidak typo di seluruh codebase
    // -------------------------------------------------------------------------

    const ROLE_SUPER_ADMIN  = 'super_admin';
    const ROLE_ADMIN_PO     = 'admin_po';
    const ROLE_OPERASIONAL  = 'operasional';
    const ROLE_STAFF        = 'staff';

    const ROLES = [
        self::ROLE_SUPER_ADMIN,
        self::ROLE_ADMIN_PO,
        self::ROLE_OPERASIONAL,
        self::ROLE_STAFF,
    ];

    // -------------------------------------------------------------------------
    // Role helpers
    // -------------------------------------------------------------------------

    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPER_ADMIN;
    }

    public function isAdminPo(): bool
    {
        return $this->role === self::ROLE_ADMIN_PO;
    }

    public function isOperasional(): bool
    {
        return $this->role === self::ROLE_OPERASIONAL;
    }

    public function isStaff(): bool
    {
        return $this->role === self::ROLE_STAFF;
    }

    // Super admin tidak terikat PO manapun
    public function belongsToPo(): bool
    {
        return $this->po_id !== null;
    }

    // -------------------------------------------------------------------------
    // Relations
    // -------------------------------------------------------------------------

    public function po(): BelongsTo
    {
        return $this->belongsTo(Po::class, 'po_id');
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeRole($query, string|array $role)
    {
        return $query->whereIn('role', (array) $role);
    }

    public function scopeOfPo($query, int $poId)
    {
        return $query->where('po_id', $poId);
    }
}