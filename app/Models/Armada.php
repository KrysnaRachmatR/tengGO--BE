<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Armada extends Model
{
    protected $fillable = [
        'name',
        'plate_number',
        'seat_capacity',
        'status',
        'company_id',
        'image',
    ];

    // 🔗 Relasi ke Company
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    // 🔗 Relasi ke Trip (nanti kepake)
    public function trips()
    {
        return $this->hasMany(Trip::class);
    }

    // 🧠 Scope biar auto filter by company
    public function scopeByCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }
    protected $appends = ['image_url'];

    public function getImageUrlAttribute()
    {
        return $this->image 
            ? asset('storage/' . $this->image) 
            : null;
    }
}