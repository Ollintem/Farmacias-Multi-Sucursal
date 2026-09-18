<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;

/**
 * @property int $id
 * @property string $nombre
 * @property string $apellido
 * @property string $nombre_usuario
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property Carbon|null $two_factor_confirmed_at
 * @property bool $es_activo
 * @property int|null $id_rol
 * @property int|null $id_sucursal
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['nombre', 'apellido', 'nombre_usuario', 'email', 'password', 'es_activo', 'id_rol', 'id_sucursal'])]
#[Hidden(['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * The table associated with the model.
     */
    protected $table = 'usuarios';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
            'es_activo' => 'boolean',
        ];
    }

    /**
     * Display name derived from nombre + apellido.
     *
     * Kept for compatibility with existing views that use `$user->name`.
     *
     * @return Attribute<string, never>
     */
    protected function name(): Attribute
    {
        return Attribute::get(fn (): string => trim("{$this->nombre} {$this->apellido}"));
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        $initials = Str::initials(trim("{$this->nombre} {$this->apellido}"), true);

        return Str::length($initials) > 1
            ? Str::substr($initials, 0, 1).Str::substr($initials, -1)
            : $initials;
    }

    /**
     * @return BelongsTo<Rol, $this>
     */
    public function rol(): BelongsTo
    {
        return $this->belongsTo(Rol::class, 'id_rol');
    }

    /**
     * @return BelongsTo<Sucursal, $this>
     */
    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'id_sucursal');
    }

    /**
     * @return HasMany<PermisoActivado, $this>
     */
    public function permisosActivados(): HasMany
    {
        return $this->hasMany(PermisoActivado::class, 'id_usuario');
    }

    /** @var array<int, string>|null */
    private ?array $modulosVisiblesCache = null;

    /**
     * Nombres de módulos con permiso de ver (Mostrar id 4 o Todos id 5) activo.
     *
     * Una sola consulta por request, memoizada para no hacer N+1 desde el sidebar.
     *
     * @return array<int, string>
     */
    public function modulosVisibles(): array
    {
        if ($this->modulosVisiblesCache !== null) {
            return $this->modulosVisiblesCache;
        }

        $modulosVisibles = $this->permisosActivados()
            ->where('es_activo', true)
            ->whereIn('id_permiso', [4, 5])
            ->with('modulo:id,nombre_modulo')
            ->get()
            ->pluck('modulo.nombre_modulo')
            ->filter()
            ->unique()
            ->values();

        if ($modulosVisibles->isNotEmpty()) {
            return $this->modulosVisiblesCache = $modulosVisibles->all();
        }

        $totalPermisosActivados = $this->permisosActivados()->count();
        if ($totalPermisosActivados === 0) {
            return $this->modulosVisiblesCache = Modulo::query()->pluck('nombre_modulo')->filter()->unique()->values()->all();
        }

        if ($this->rol?->tipo_rol === 'SuperAdmin') {
            return $this->modulosVisiblesCache = Modulo::query()->pluck('nombre_modulo')->filter()->unique()->values()->all();
        }

        return $this->modulosVisiblesCache = [];
    }

    public function puedeVerModulo(string $nombreModulo): bool
    {
        return in_array($nombreModulo, $this->modulosVisibles(), true);
    }

    /**
     * Mapas entre los nombres cortos que reciben las rutas
     * y los catálogos de la base de datos.
     *
     * @var array<string, string>
     */
    private const MAPA_PERMISOS = [
        'ver' => 'Mostrar',
        'crear' => 'Crear',
        'editar' => 'Editar',
        'eliminar' => 'Borrar',
    ];

    /**
     * @var array<string, string>
     */
    private const MAPA_MODULOS = [
        'dashboard' => 'Dashboard',
        'punto-venta' => 'Punto de venta',
        'inventario' => 'Inventario',
        'lotes' => 'Lotes y caducidades',
        'entradas' => 'Entradas de almacén',
        'traspasos' => 'Traspasos',
        'sucursales' => 'Sucursales',
        'usuarios' => 'Usuarios y roles',
        'caja' => 'Caja',
        'reportes' => 'Reportes',
        'alertas' => 'Alertas',
    ];

    /**
     * Indica si el usuario tiene activo un permiso sobre un módulo.
     * Acepta los nombres cortos de las rutas (ver/crear/editar/eliminar
     * y el slug del módulo) o los nombres tal como están en el catálogo.
     */
    public function permisosHabilitados(string $permiso, string $modulo): bool
    {
        if ($this->permisosActivados()->count() === 0) {
            return true;
        }

        $tipoPermiso = self::MAPA_PERMISOS[strtolower($permiso)] ?? $permiso;
        $nombreModulo = self::MAPA_MODULOS[strtolower($modulo)] ?? $modulo;

        return $this->permisosActivados()
            ->where('es_activo', true)
            ->whereHas('permiso', fn ($query) => $query->where('tipo_permiso', $tipoPermiso))
            ->whereHas('modulo', fn ($query) => $query->where('nombre_modulo', $nombreModulo))
            ->exists();
    }
}
