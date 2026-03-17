<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = [
        'name',
        'company_id',
    ];

    // 🔗 Relasi
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_roles');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}