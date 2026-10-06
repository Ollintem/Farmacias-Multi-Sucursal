<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificacionLeida extends Model
{
    protected $table = 'notificaciones_leidas';

    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'id_sucursal',
        'aviso_id',
        'leida_en',
    ];

    protected $casts = [
        'leida_en' => 'datetime',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'id_sucursal');
    }
}
