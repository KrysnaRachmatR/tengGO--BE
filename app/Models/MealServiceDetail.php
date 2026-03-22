<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MealServiceDetail extends Model
{
    protected $fillable = [
        'meal_service_id',
        'type',
        'total_served'
    ];

    public function mealService()
    {
        return $this->belongsTo(MealService::class);
    }
}