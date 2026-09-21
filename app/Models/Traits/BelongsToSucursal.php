<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

trait BelongsToSucursal
{
    /**
     * Boot the trait.
     */
    protected static function bootBelongsToSucursal(): void
    {
        static::addGlobalScope('sucursal', function (Builder $builder) {
            $sucursalId = session('active_sucursal_id');

            if ($sucursalId && $builder->getModel()->isScopeable('sucursal')) {
                $builder->whereHas('sucursales', function ($query) use ($sucursalId) {
                    $query->where('sucursales.id', $sucursalId);
                });
            }
        });

        static::created(function ($model) {
            if ($model->isScopeable('sucursal') && ! $model->sucursales()->exists()) {
                $sucursalId = session('active_sucursal_id') ?? Auth::user()?->id_sucursal;

                if ($sucursalId) {
                    $model->sucursales()->syncWithoutDetaching([$sucursalId]);
                }
            }
        });
    }

    /**
     * Determine if the model should be scoped by sucursal.
     */
    protected function isScopeable(string $scope): bool
    {
        return method_exists($this, 'sucursales') && ! request()->has('all_sucursales');
    }

    /**
     * Get the active sucursal ID from session.
     */
    public static function getActiveSucursalId(): ?int
    {
        return session('active_sucursal_id');
    }

    /**
     * Scope a query to the active sucursal.
     */
    public function scopeActiveSucursal(Builder $query): Builder
    {
        $sucursalId = session('active_sucursal_id');

        if ($sucursalId && method_exists($this, 'sucursales')) {
            return $query->whereHas('sucursales', function ($q) use ($sucursalId) {
                $q->where('sucursales.id', $sucursalId);
            });
        }

        return $query;
    }

    /**
     * Scope a query to a specific sucursal.
     */
    public function scopeForSucursal(Builder $query, int $sucursalId): Builder
    {
        if (method_exists($this, 'sucursales')) {
            return $query->whereHas('sucursales', function ($q) use ($sucursalId) {
                $q->where('sucursales.id', $sucursalId);
            });
        }

        return $query;
    }

    /**
     * Scope a query to ignore sucursal filtering.
     */
    public function scopeAllSucursales(Builder $query): Builder
    {
        return $query->withoutGlobalScope('sucursal');
    }
}
