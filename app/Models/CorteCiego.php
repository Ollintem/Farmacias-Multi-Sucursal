<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $id_usuarios
 * @property bool $corte_ciego
 * @property-read User $usuario
 */
#[Fillable(['id_usuarios', 'corte_ciego'])]
class CorteCiego extends Model
{
    public $timestamps = false;

    protected $table = 'cortes_ciegos';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'corte_ciego' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuarios');
    }
}
