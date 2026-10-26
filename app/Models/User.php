<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
 * @property-read string $name
 */
#[Fillable(['nombre', 'apellido', 'nombre_usuario', 'email', 'password', 'es_activo', 'id_rol', 'id_sucursal'])]
#[Hidden(['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    protected static function booted(): void
    {
        static::creating(function (User $user): void {
            if (empty($user->nombre_usuario)) {
                $user->nombre_usuario = static::generarNombreUsuario($user);
            }
        });

        static::updating(function (User $user): void {
            if (empty($user->nombre_usuario) && ! empty($user->email)) {
                $user->nombre_usuario = static::generarNombreUsuario($user);
            }
        });
    }

    /**
     * Genera un nombre_usuario único a partir del email o nombre+apellido.
     */
    public static function generarNombreUsuario(User $user): string
    {
        $base = '';

        if (! empty($user->email) && str_contains($user->email, '@')) {
            $base = strstr($user->email, '@', true);
        }

        if ($base === '' && ! empty($user->nombre)) {
            $base = strtolower(trim($user->nombre.(($user->apellido ?? '') !== '' ? '.'.$user->apellido : '')));
            $base = preg_replace('/[^a-z0-9._-]+/', '', $base) ?? 'usuario';
        }

        $base = $base !== '' ? $base : 'usuario';
        $candidato = $base;
        $contador = 1;

        while (static::where('nombre_usuario', $candidato)->whereKeyNot($user->id ?? 0)->exists()) {
            $candidato = $base.$contador;
            $contador++;
        }

        return $candidato;
    }

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

    /**
     * @return HasMany<Pedido, $this>
     */
    public function pedidosSolicitados(): HasMany
    {
        return $this->hasMany(Pedido::class, 'pedido_por');
    }

    /**
     * @return HasMany<Pedido, $this>
     */
    public function pedidosRecibidos(): HasMany
    {
        return $this->hasMany(Pedido::class, 'recibido_por');
    }

    /**
     * @return HasMany<CorteCaja, $this>
     */
    public function cortesCaja(): HasMany
    {
        return $this->hasMany(CorteCaja::class, 'id_usuario');
    }

    /**
     * @return HasMany<Traspaso, $this>
     */
    public function traspasosSolicitados(): HasMany
    {
        return $this->hasMany(Traspaso::class, 'pedido_por');
    }

    /**
     * @return HasMany<Traspaso, $this>
     */
    public function traspasosRecibidos(): HasMany
    {
        return $this->hasMany(Traspaso::class, 'recibido_por');
    }

    /**
     * @return HasMany<AlertaUsuario, $this>
     */
    public function alertasUsuarios(): HasMany
    {
        return $this->hasMany(AlertaUsuario::class, 'id_usuario');
    }

    /**
     * @return BelongsToMany<Alerta, $this>
     */
    public function alertas(): BelongsToMany
    {
        return $this->belongsToMany(Alerta::class, 'alertas_usuarios', 'id_usuario', 'id_alerta');
    }

    /** @var array<int, string>|null */
    private ?array $modulosVisiblesCache = null;

    private ?bool $rolPermisosConfiguradosCache = null;

    /**
     * Nombres de módulos con permiso de ver activo.
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

        if ($this->rol?->tipo_rol === 'SuperAdmin') {
            return $this->modulosVisiblesCache = Modulo::query()->pluck('nombre_modulo')->filter()->unique()->values()->all();
        }

        if ($this->rolTienePermisosConfigurados()) {
            $modulosVisibles = Modulo::query()
                ->whereHas('permisos', fn ($query) => $query
                    ->where('accion', 'ver')
                    ->whereHas('rolesPermisos', fn ($rolPermisoQuery) => $rolPermisoQuery
                        ->where('id_rol', $this->id_rol)
                        ->where('activo', true)))
                ->whereDoesntHave('permisosActivados', fn ($query) => $query
                    ->where('id_usuario', $this->id)
                    ->where('puede_ver', false))
                ->pluck('nombre_modulo')
                ->filter()
                ->unique()
                ->values();

            return $this->modulosVisiblesCache = $modulosVisibles->all();
        }

        $modulosVisibles = $this->permisosActivados()
            ->where('puede_ver', true)
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

        return $this->modulosVisiblesCache = [];
    }

    public function puedeVerModulo(string $nombreModulo): bool
    {
        return in_array($nombreModulo, $this->modulosVisibles(), true);
    }

    /**
     * Mapas entre los nombres cortos que reciben las rutas
     * y las columnas de flags en `permisos_activados`.
     *
     * @var array<string, string>
     */
    private const MAPA_PERMISOS = [
        'ver' => 'puede_ver',
        'mostrar' => 'puede_ver',
        'crear' => 'puede_crear',
        'editar' => 'puede_editar',
        'eliminar' => 'puede_borrar',
        'borrar' => 'puede_borrar',
        'todos' => 'todos',
    ];

    /**
     * Acciones equivalentes a cada bandera individual.
     *
     * @var array<string, string>
     */
    private const MAPA_ACCIONES = [
        'puede_ver' => 'ver',
        'puede_crear' => 'crear',
        'puede_editar' => 'editar',
        'puede_borrar' => 'borrar',
    ];

    /**
     * @var array<string, string>
     */
    private const MAPA_MODULOS = [
        'dashboard' => 'Dashboard',
        'punto-venta' => 'Punto de venta',
        'inventario' => 'Inventario',
        'lotes' => 'Lotes y caducidades',
        'lotes-y-caducidades' => 'Lotes y caducidades',
        'entradas' => 'Entradas de almacén',
        'entradas-de-almacen' => 'Entradas de almacén',
        'proveedores' => 'Proveedores',
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
     *
     * Precedencia:
     * 1. SuperAdmin siempre pasa.
     * 2. Si el rol tiene configuración en `roles_permisos`, el rol es la base.
     *    Un permiso individual de `permisos_activados` solo puede restringir,
     *    nunca ampliar lo que el rol ya permite.
     * 3. Si el rol no tiene configuración, se conserva la lógica individual
     *    existente para mantener compatibilidad.
     */
    public function permisosHabilitados(string $permiso, string $modulo): bool
    {
        if ($this->rol?->tipo_rol === 'SuperAdmin') {
            return true;
        }

        $columna = self::MAPA_PERMISOS[strtolower($permiso)] ?? $permiso;
        $nombreModulo = self::MAPA_MODULOS[strtolower($modulo)] ?? $modulo;

        if ($this->rolTienePermisosConfigurados()) {
            $columnas = $columna === 'todos'
                ? array_keys(self::MAPA_ACCIONES)
                : [$columna];

            foreach ($columnas as $columnaPermiso) {
                $accion = self::MAPA_ACCIONES[$columnaPermiso] ?? null;

                if ($accion === null || ! $this->rolPermite($accion, $nombreModulo)) {
                    return false;
                }

                if ($this->permisoIndividual($columnaPermiso, $nombreModulo) === false) {
                    return false;
                }
            }

            return true;
        }

        if ($this->permisosActivados()->count() === 0) {
            return true;
        }

        if ($columna === 'todos') {
            $registro = $this->permisosActivados()
                ->whereHas('modulo', fn ($q) => $q->where('nombre_modulo', $nombreModulo))
                ->first();

            return $registro !== null
                && $registro->puede_ver
                && $registro->puede_crear
                && $registro->puede_editar
                && $registro->puede_borrar;
        }

        if (! in_array($columna, ['puede_ver', 'puede_crear', 'puede_editar', 'puede_borrar'], true)) {
            return false;
        }

        return $this->permisosActivados()
            ->whereHas('modulo', fn ($q) => $q->where('nombre_modulo', $nombreModulo))
            ->where($columna, true)
            ->exists();
    }

    /**
     * Indica si el rol del usuario tiene al menos un permiso configurado.
     */
    private function rolTienePermisosConfigurados(): bool
    {
        if ($this->id_rol === null) {
            return false;
        }

        if ($this->rolPermisosConfiguradosCache !== null) {
            return $this->rolPermisosConfiguradosCache;
        }

        return $this->rolPermisosConfiguradosCache = RolPermiso::query()
            ->where('id_rol', $this->id_rol)
            ->exists();
    }

    /**
     * Indica si el rol permite una acción sobre un módulo.
     */
    private function rolPermite(string $accion, string $nombreModulo): bool
    {
        if ($this->id_rol === null) {
            return false;
        }

        return RolPermiso::query()
            ->where('id_rol', $this->id_rol)
            ->where('activo', true)
            ->whereHas('permiso', fn ($query) => $query
                ->where('accion', $accion)
                ->whereHas('modulo', fn ($moduloQuery) => $moduloQuery->where('nombre_modulo', $nombreModulo)))
            ->exists();
    }

    /**
     * Estado de la excepción individual del usuario para un módulo.
     *
     * Salida: null si no hay excepción registrada para ese módulo.
     */
    private function permisoIndividual(string $columna, string $nombreModulo): ?bool
    {
        $registro = $this->permisosActivados()
            ->whereHas('modulo', fn ($query) => $query->where('nombre_modulo', $nombreModulo))
            ->first();

        return $registro === null ? null : (bool) $registro->{$columna};
    }
}
