<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Company extends Model
{
    protected $fillable = [
        'name',
        'logo',
        'domain',
        'api_key',
        'is_active',
        'primary_color',
        'secondary_color',
    ];

    // 🔗 Relasi
    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function roles()
    {
        return $this->hasMany(Role::class);
    }

    public function routes()
    {
        return $this->hasMany(Route::class);
    }

    public function series()
    {
        return $this->hasMany(Series::class);
    }

    public function armadas()
    {
        return $this->hasMany(Armada::class);
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    public function trips()
    {
        return $this->hasMany(Trip::class);
    }


    protected static function booted()
    {
        static::creating(function ($company) {
            if (!$company->api_key) {
                $company->api_key = Str::random(40);
            }
        });
    }
}