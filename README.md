# Farmacias Multi Sucursal

Aplicación Laravel para gestionar la operación de una farmacia con varias sucursales. El sistema reúne autenticación, usuarios, sucursales, inventario y control de lotes y caducidades.

## Estado actual

### Módulos operativos

- **Autenticación:** inicio de sesión y recuperación de contraseña mediante Laravel Fortify.
- **Dashboard:** selección y consulta inicial de sucursales.
- **Usuarios:** listado, alta, consulta, edición y eliminación.
- **Sucursales:** listado y alta de sucursales.
- **Inventario:** listado, búsqueda y alta de productos asociados a una sucursal.
- **Lotes y caducidades:** listado, búsqueda, filtros por sucursal y registro de lotes.

### Módulos visibles como placeholder

Las siguientes rutas existen para mantener disponible la navegación, pero todavía no tienen un flujo propio:

- Punto de venta
- Entradas de almacén
- Traspasos
- Caja
- Reportes
- Alertas

## Arquitectura del proyecto

La referencia detallada de cada carpeta, archivo y flujo se encuentra en [DOCUMENTACION_CODIGO.md](DOCUMENTACION_CODIGO.md).

```text
app/
  Http/Controllers/   Controladores HTTP por módulo.
  Livewire/            Acciones y componentes Livewire.
  Models/              Modelos Eloquent y relaciones de datos.
  Providers/           Registro de servicios de la aplicación.
database/
  factories/           Fábricas para pruebas y datos de desarrollo.
  migrations/          Estructura evolutiva de la base de datos.
  seeders/             Datos iniciales y catálogos.
resources/views/
  components/          Componentes Blade reutilizables.
  layouts/              Layouts de autenticación y aplicación.
  pages/                Vistas de cada módulo y de configuración.
routes/
  web.php              Rutas web autenticadas y de acceso público.
  settings.php          Rutas de configuración de usuario.
tests/
  Feature/              Pruebas de flujos HTTP y de aplicación.
  Unit/                 Pruebas unitarias aisladas.
```

La lógica HTTP se concentra en `app/Http/Controllers/`, las vistas de módulo en `resources/views/pages/` y las rutas web en `routes/web.php`. Los cambios de estructura de datos deben hacerse mediante migraciones, no editando directamente tablas existentes.

## Rutas principales

Todas las rutas del panel requieren autenticación y verificación de correo. Para consultar el inventario completo:

```bash
php artisan route:list --except-vendor
```

| Módulo | Rutas principales | Controlador |
| --- | --- | --- |
| Usuarios | `/usuarios`, `/usuarios/create`, `/usuarios/{usuario}` | `UsuariosController` |
| Sucursales | `/sucursales`, `/sucursales/create` | `SucursalesController` |
| Inventario | `/inventario`, `/inventario/create` | `InventarioController` |
| Lotes | `/lotes-y-caducidades`, `/lotes-y-caducidades/create` | `LotesController` |

## Datos principales

Las migraciones actuales incluyen roles, usuarios, sucursales, proveedores, productos, presentaciones, lotes, permisos, ventas, caja y traspasos. Antes de modificar un modelo o una relación, revisa primero la migración correspondiente y sus relaciones con otras tablas.

Los modelos de dominio más relevantes son `User`, `Rol`, `Sucursal`, `Producto`, `PresentacionProducto`, `Proveedor`, `Lote`, `Modulo`, `Permiso` y `PermisoActivado`.

## Instalación y desarrollo

Requisitos mínimos:

- PHP 8.3
- Composer
- Node.js y npm
- Una base de datos configurada en `.env`

En una instalación nueva:

```bash
composer run setup
```

Para levantar el entorno de desarrollo:

```bash
composer run dev
```

Comandos frecuentes:

```bash
php artisan migrate
php artisan db:seed
php artisan optimize:clear
npm run build
php artisan test --compact
composer run types:check
```

## Como localizar un error

Sigue este orden para ubicar el origen sin revisar todo el proyecto:

1. **Identifica la URL y el método HTTP.** Ejecuta `php artisan route:list --except-vendor` y busca la ruta afectada. Ahí se indica el controlador y método responsables.
2. **Revisa el controlador.** Los métodos tienen PHPDoc con sus entradas, consultas principales y vista o redirección de salida.
3. **Revisa la validación.** En los métodos `store` y `update`, confirma que el nombre del campo del formulario coincida con la regla de validación y con la columna de la base de datos.
4. **Revisa el modelo y la migración.** Si falla una relación o una consulta, compara el modelo en `app/Models/` con la migración que creó la tabla en `database/migrations/`.
5. **Revisa la vista.** Si el controlador termina correctamente pero la pantalla falla, abre la vista indicada en `resources/views/pages/` y comprueba que use los mismos nombres de variables enviados por el controlador.
6. **Revisa el log.** Laravel registra excepciones y consultas fallidas en `storage/logs/laravel.log`. Busca la fecha y hora del error, junto con la primera referencia a un archivo de `app/`.

Comandos de diagnóstico:

```bash
php artisan route:list --except-vendor
php artisan optimize:clear
Get-Content storage/logs/laravel.log -Tail 80
php artisan test --compact
```

### Mapa rápido de archivos

| Síntoma | Primer archivo que revisar |
| --- | --- |
| La URL no existe o apunta al lugar equivocado | `routes/web.php` |
| Error al guardar inventario | `app/Http/Controllers/InventarioController.php` |
| Error al guardar o filtrar lotes | `app/Http/Controllers/LotesController.php` |
| Error al crear o editar usuarios/permisos | `app/Http/Controllers/UsuariosController.php` |
| Error al registrar una sucursal | `app/Http/Controllers/SucursalesController.php` |
| No permite iniciar sesión | `app/Http/Controllers/AuthController.php` |
| Falla una columna o relación | `app/Models/` y `database/migrations/` |
| La consulta funciona pero la pantalla falla | `resources/views/pages/` |
| Error no visible en pantalla | `storage/logs/laravel.log` |

## Organización del trabajo

- `app/`: lógica de la aplicación, controladores, modelos y acciones.
- `routes/`: definición de rutas y middleware.
- `resources/views/`: interfaz Blade y componentes visuales.
- `database/`: migraciones, seeders y factories.
- `tests/`: cobertura automatizada.
- `public/build/`: artefactos generados por Vite; no editar manualmente.

Antes de agregar un módulo, documenta su ruta, controlador, vista, modelos involucrados y estado de pruebas. Los cambios visuales y el historial de cambios se registran por separado de esta guía en `DOCUMENTACION_CAMBIOS.md`.
