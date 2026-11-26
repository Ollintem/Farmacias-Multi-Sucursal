# Farmacias Multi Sucursal

Aplicación Laravel para gestionar la operación de una farmacia con varias sucursales. El sistema reúne autenticación, usuarios y permisos, sucursales, inventario, lotes y caducidades, punto de venta, caja, compras, traspasos, proveedores, reportes y alertas.

## Estado actual

### Módulos operativos

- **Autenticación:** inicio de sesión y recuperación de contraseña mediante Laravel Fortify.
- **Dashboard:** selección y consulta inicial de sucursales.
- **Usuarios y roles:** listado, alta, consulta, edición y eliminación de usuarios; roles y permisos por módulo.
- **Sucursales:** listado, alta, edición, cambio de estado y eliminación.
- **Inventario:** catálogo de productos, pestañas de stock y productos, categorías, presentaciones, altas, ediciones y activación/desactivación.
- **Lotes y caducidades:** listado, búsqueda, filtros por sucursal, edición, anulación y registro de mermas.
- **Punto de venta:** búsqueda de productos, selección de presentación, carrito, cobro, receta para productos controlados y cierre de ticket.
- **Caja:** apertura por sucursal y turno, movimientos de ingreso/retiro, cierre con diferencia y auditoría de cortes.
- **Entradas de almacén:** registro de compras a proveedores y traspasos entre sucursales.
- **Proveedores:** listado, alta, edición y eliminación.
- **Traspasos:** alta, envío, aceptación, rechazo y cancelación entre sucursales.
- **Reportes:** consulta de ventas y exportación a PDF.
- **Alertas:** centro de notificaciones, feed, detalle y marcado de leídas/no leídas.
- **Configuración:** perfil, apariencia, seguridad y datos bancarios.

Cada módulo del panel está protegido por el middleware `checarPermisos`, que valida la acción (`ver`, `crear`, `editar`, `eliminar`) contra los permisos activados del usuario.

## Arquitectura del proyecto

La referencia detallada de cada carpeta, archivo y flujo se encuentra en [DOCUMENTACION_CODIGO.md](DOCUMENTACION_CODIGO.md). El esquema de tablas está en [ESTRUCTURA_BASE_DATOS.md](ESTRUCTURA_BASE_DATOS.md) y el historial de cambios en [DOCUMENTACION_CAMBIOS.md](DOCUMENTACION_CAMBIOS.md).

```text
app/
  Http/Controllers/    Controladores HTTP por módulo.
  Http/Middleware/     checarPermisos y sucursal activa.
  Livewire/            Componentes Livewire (POS, alertas, topbar, settings).
  Models/              Modelos Eloquent y relaciones de datos.
  Services/            Lógica de negocio compartida (inventario, ventas, reportes).
  Actions/             Acciones de Fortify y operaciones aisladas.
  Providers/           Registro de servicios de la aplicación.
  Console/Commands/    Comandos Artisan, como el cierre de cajas vencidas.
database/
  factories/           Fábricas para pruebas y datos de desarrollo.
  migrations/          Estructura evolutiva de la base de datos.
  seeders/             Datos iniciales, catálogos y permisos.
resources/views/
  components/          Componentes Blade reutilizables.
  layouts/             Layouts de autenticación y aplicación.
  pages/               Vistas de cada módulo y de configuración.
routes/
  web.php              Rutas web autenticadas y de acceso público.
  settings.php         Rutas de configuración de usuario.
  console.php          Tareas programadas.
tests/
  Feature/             Pruebas de flujos HTTP y de aplicación.
  Unit/                Pruebas unitarias aisladas.
```

La lógica HTTP se concentra en `app/Http/Controllers/`, las interacciones dinámicas en `app/Livewire/`, las consultas compartidas en `app/Services/` y las rutas en `routes/web.php`. Los cambios de estructura de datos deben hacerse mediante migraciones, no editando directamente tablas existentes.

## Rutas principales

Todas las rutas del panel requieren autenticación y verificación de correo; además, cada una exige el permiso correspondiente. Para consultar el inventario completo:

```bash
php artisan route:list --except-vendor
```

| Módulo | Rutas principales | Controlador / componente |
| --- | --- | --- |
| Dashboard | `/dashboard` | `routes/web.php` |
| Usuarios | `/usuarios`, `/usuarios/create`, `/usuarios/{usuario}` | `UsuariosController` |
| Roles y permisos | `/roles/permisos`, `/roles` | `RolPermisoController`, `RolesController` |
| Sucursales | `/sucursales`, `/sucursales/create` | `SucursalesController` |
| Inventario | `/inventario`, `/inventario/stock`, `/inventario/productos` | `InventarioController` |
| Lotes | `/lotes-y-caducidades`, `/lotes-y-caducidades/{lote}/edit` | `LotesController` |
| Punto de venta | `/punto-venta` | `Livewire\PuntoDeVenta` |
| Caja | `/caja`, `/caja/auditoria` | `CajaController` |
| Entradas | `/entradas-de-almacen`, `/compras`, `/traspasos` | `EntradasController`, `CompraController`, `TraspasoController` |
| Proveedores | `/proveedores`, `/proveedores/create` | `ProveedoresController` |
| Reportes | `/reportes`, `/reportes/pdf` | `ReporteController` |
| Alertas | `/alertas`, `/alertas/feed`, `/alertas/detalle` | `Livewire\CentroNotificaciones`, `AlertasController` |
| Configuración | `/settings/profile`, `/settings/security`, `/settings/transferencia` | `routes/settings.php` |

## Datos principales

Las migraciones actuales incluyen roles, usuarios, sucursales, proveedores, compras, productos, presentaciones, inventario, lotes, permisos, ventas, pagos, cortes de caja, movimientos de caja, traspasos, mermas y alertas. Ventas y cortes se enlazan directamente con la sucursal; la tabla `cajas` ya no existe. Antes de modificar un modelo o una relación, revisa primero la migración correspondiente y sus relaciones con otras tablas.

Los modelos de dominio más relevantes son `User`, `Rol`, `RolPermiso`, `Sucursal`, `Producto`, `PresentacionProducto`, `ProductoPresentacion`, `Inventario`, `Categoria`, `Lote`, `Merma`, `Proveedor`, `Compra`, `Venta`, `Pago`, `ProductoVenta`, `CorteCaja`, `MovimientoCaja`, `Traspaso`, `DetalleTraspaso`, `Modulo`, `Permiso`, `PermisoActivado`, `Alerta`, `TipoAlerta`, `AlertaUsuario` y `ConfiguracionBancaria`.

## Instalación y desarrollo

Requisitos mínimos:

- PHP 8.3
- Composer
- Node.js y npm
- Una base de datos MySQL configurada en `.env`

En una instalación nueva:

```bash
composer run setup
php artisan db:seed
```

`composer run setup` instala dependencias, genera la clave, ejecuta las migraciones y compila el frontend. Para cargar catálogos y permisos iniciales ejecuta además `php artisan db:seed`.

Para levantar el entorno de desarrollo:

```bash
composer run dev
```

El comando `composer run dev` inicia el servidor, Vite y el worker de colas. En producción o para probar cajas automáticas, el programador debe estar activo porque `routes/console.php` cierra las cajas vencidas cada minuto (`php artisan schedule:work` o el cron de Laravel).

Comandos frecuentes:

```bash
php artisan migrate
php artisan db:seed
php artisan optimize:clear
npm run build
php artisan test --compact
composer run types:check
vendor/bin/pint --dirty --format agent
```

## Como localizar un error

Sigue este orden para ubicar el origen sin revisar todo el proyecto:

1. **Identifica la URL y el método HTTP.** Ejecuta `php artisan route:list --except-vendor` y busca la ruta afectada. Ahí se indica el controlador o componente responsables.
2. **Revisa el controlador o componente Livewire.** Los métodos tienen PHPDoc con sus entradas, consultas principales y vista o redirección de salida.
3. **Revisa el permiso.** Si la ruta responde con acceso denegado, revisa `checarPermisos`, el módulo asociado y los permisos activados del usuario.
4. **Revisa la validación.** En los métodos `store` y `update`, confirma que el nombre del campo del formulario coincida con la regla de validación y con la columna de la base de datos.
5. **Revisa el modelo y la migración.** Si falla una relación o una consulta, compara el modelo en `app/Models/` con la migración que creó la tabla en `database/migrations/`.
6. **Revisa la vista.** Si el controlador termina correctamente pero la pantalla falla, abre la vista indicada en `resources/views/pages/` y comprueba que use los mismos nombres de variables enviados por el controlador o componente.
7. **Revisa el log.** Laravel registra excepciones y consultas fallidas en `storage/logs/laravel.log`. Busca la fecha y hora del error, junto con la primera referencia a un archivo de `app/`.

Comandos de diagnóstico:

```bash
php artisan route:list --except-vendor
php artisan optimize:clear
php artisan migrate:status
tail -n 80 storage/logs/laravel.log
php artisan test --compact
```

### Mapa rápido de archivos

| Síntoma | Primer archivo que revisar |
| --- | --- |
| La URL no existe o apunta al lugar equivocado | `routes/web.php` o `routes/settings.php` |
| Acceso denegado a un módulo | `app/Http/Middleware/ChecarPermisos.php` y permisos del usuario |
| Error al guardar o consultar inventario | `app/Http/Controllers/InventarioController.php` y `app/Services/InventarioService.php` |
| Error al guardar, editar, anular o mermar lotes | `app/Http/Controllers/LotesController.php` |
| Error en una venta o cobro | `app/Livewire/PuntoDeVenta.php` |
| Error al abrir, mover o cerrar caja | `app/Http/Controllers/CajaController.php` |
| Error en compras, traspasos o entradas | `app/Http/Controllers/CompraController.php`, `TraspasoController.php` y `EntradasController.php` |
| Error al crear o editar usuarios/permisos | `app/Http/Controllers/UsuariosController.php` |
| Error al registrar una sucursal | `app/Http/Controllers/SucursalesController.php` |
| No permite iniciar sesión | `app/Http/Controllers/AuthController.php` |
| Falla una columna o relación | `app/Models/` y `database/migrations/` |
| La consulta funciona pero la pantalla falla | `resources/views/pages/` |
| Error no visible en pantalla | `storage/logs/laravel.log` |

## Organización del trabajo

- `app/`: lógica de la aplicación, controladores, middleware, componentes Livewire, modelos, servicios y comandos.
- `routes/`: definición de rutas, middleware y tareas programadas.
- `resources/views/`: interfaz Blade y componentes visuales.
- `database/`: migraciones, seeders y factories.
- `tests/`: cobertura automatizada por módulo y flujos de negocio.
- `public/build/`: artefactos generados por Vite; no editar manualmente.

Antes de agregar un módulo, documenta su ruta, controlador o componente, vista, modelos involucrados, permisos requeridos y estado de pruebas. Los cambios visuales y el historial de cambios se registran por separado de esta guía en `DOCUMENTACION_CAMBIOS.md` y `DOCUMENTACION_DISENO.md`.
