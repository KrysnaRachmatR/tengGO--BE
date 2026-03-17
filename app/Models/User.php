<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'company_id',
        'is_super_admin',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'is_super_admin' => 'boolean',
    ];

    // 🔗 Relasi
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles');
    }

    // 🧠 Helper: cek role
    public function hasRole($roleName)
    {
        if ($this->is_super_admin) {
            return true;
        }

        return $this->roles()->where('name', $roleName)->exists();
    }

    // 🧠 Helper: assign role
    public function assignRole($roleId)
    {
        return $this->roles()->attach($roleId);
    }

    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = bcrypt($value);
    }
}