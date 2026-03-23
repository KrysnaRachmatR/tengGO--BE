<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait BelongsToTenant
{
    protected static function bootBelongsToTenant(): void
    {
        // Auto-filter semua query berdasarkan po_id user yang login
        // Super admin (po_id = null) bisa akses semua tenant
        static::addGlobalScope('tenant', function (Builder $query) {
            if (auth()->check() && auth()->user()->po_id !== null) {
                $query->where(
                    (new static())->getTable() . '.po_id',
                    auth()->user()->po_id
                );
            }
        });

        // Auto-inject po_id saat create
        static::creating(function ($model) {
            if (
                auth()->check() &&
                auth()->user()->po_id !== null &&
                empty($model->po_id)
            ) {
                $model->po_id = auth()->user()->po_id;
            }
        });
    }

    // Helper: bypass global scope untuk query lintas tenant (super admin use case)
    public static function allTenants(): Builder
    {
        return static::withoutGlobalScope('tenant');
    }
}