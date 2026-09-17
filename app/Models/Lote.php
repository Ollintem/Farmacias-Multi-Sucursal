<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lote extends Model
{
    protected $table = 'lotes';

    public $timestamps = false;

    protected $fillable = [
        'folio',
        'id_proveedor',
        'entregado_en',
        'fecha_caducidad',
    ];

    protected $casts = [
        'entregado_en' => 'datetime',
        'fecha_caducidad' => 'datetime',
    ];

    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class, 'id_proveedor');
    }

    public function productos(): HasMany
    {
        return $this->hasMany(Producto::class, 'id_lote');
    }
}
