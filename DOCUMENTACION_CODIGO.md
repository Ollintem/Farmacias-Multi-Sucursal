# Documentacion del codigo

## Proposito

Este documento explica para que sirve cada carpeta y archivo importante de Farmacias Multi Sucursal. La meta es que cualquier persona pueda seguir el recorrido de una funcionalidad y encontrar rapidamente el punto donde aparece un error.

El proyecto usa Laravel 13, PHP 8.3, Blade, Livewire, Fortify, Eloquent y Vite.

## Como leer el proyecto

Una solicitud del navegador normalmente sigue este recorrido:

```text
Navegador
  -> routes/web.php o routes/settings.php
  -> middleware y autenticacion
  -> controlador o componente Livewire
  -> validacion y modelos Eloquent
  -> base de datos
  -> vista Blade o redireccion
  -> navegador
```

Para un error, empieza en la ruta y avanza en ese mismo orden. No es necesario revisar todo el proyecto de una vez.

## Carpetas principales

### `app/`

Contiene la logica PHP de la aplicacion. Aqui se encuentran los controladores, modelos, acciones de autenticacion, componentes Livewire y proveedores de servicios.

### `app/Http/Controllers/`

Los controladores reciben las solicitudes HTTP, validan datos, consultan modelos y deciden si deben devolver una vista o una redireccion.

| Archivo | Responsabilidad | Vistas o rutas relacionadas |
| --- | --- | --- |
| `AuthController.php` | Muestra el login y autentica las credenciales. | `/`, `pages/auth/login.blade.php` |
| `InventarioController.php` | Lista productos, filtra por sucursal, carga catalogos y registra productos. | `/inventario`, `pages/inventario/` |
| `LotesController.php` | Lista lotes, busca por folio/producto/proveedor, calcula caducidad y registra lotes. | `/lotes-y-caducidades`, `pages/lotes/` |
| `SucursalesController.php` | Lista y registra sucursales. | `/sucursales`, `pages/sucursales/` |
| `UsuariosController.php` | Lista, registra, consulta, edita y elimina usuarios; tambien administra permisos. | `/usuarios`, `pages/usuarios/` |
| `Controller.php` | Clase base para controladores HTTP. | No contiene un modulo propio. |

Los metodos publicos de estos controladores tienen PHPDoc con sus entradas y salidas. Si un flujo falla al guardar, revisa primero el metodo `store` o `update` correspondiente.

### `app/Models/`

Los modelos representan tablas y relaciones de la base de datos. Tambien definen campos asignables, conversiones de tipo y relaciones Eloquent.

| Modelo | Representa | Relaciones principales |
| --- | --- | --- |
| `User.php` | Usuarios del sistema. | Rol, sucursal y permisos activados. |
| `Rol.php` | Roles de acceso. | Usuarios y permisos segun la configuracion existente. |
| `Sucursal.php` | Sucursales de la farmacia. | Usuarios y productos. |
| `Producto.php` | Productos del inventario. | Lote y sucursales mediante `inventario`. |
| `Lote.php` | Lotes y fechas de caducidad. | Proveedor y productos. |
| `Proveedor.php` | Proveedores de productos. | Lotes. |
| `PresentacionProducto.php` | Presentaciones o unidades de producto. | Productos. |
| `Modulo.php` | Modulos visibles del sistema. | Permisos activados. |
| `Permiso.php` | Tipos de permiso disponibles. | Permisos activados. |
| `PermisoActivado.php` | Permisos asignados a un usuario y modulo. | Usuario, modulo y permiso. |

Cuando aparece un error de columna, relacion o tipo de dato, compara el modelo con la migracion que creo la tabla. Revisa especialmente `protected $fillable`, `protected $casts` y los nombres de las llaves foraneas.

#### Puntos importantes de `User`

- Usa la tabla `usuarios`, no la tabla convencional `users`.
- Oculta contrasena y datos de autenticacion de dos factores.
- Convierte `password` usando el cast `hashed`.
- `modulosVisibles()` determina que modulos puede ver un usuario en el menu.
- `puedeVerModulo()` comprueba un modulo concreto.

Si el menu muestra modulos incorrectos, revisa `User.php`, `PermisoActivado.php`, `Modulo.php` y los datos creados por los seeders de permisos.

### `app/Actions/`

Contiene operaciones aisladas que Laravel o Fortify ejecutan como una unidad.

- `app/Actions/Fortify/ResetUserPassword.php`: aplica las reglas y actualiza la contrasena durante el restablecimiento.

Si falla el flujo de recuperar contrasena, revisa este archivo junto con `app/Concerns/PasswordValidationRules.php` y las rutas de Fortify.

### `app/Concerns/`

Agrupa logica reutilizable entre clases.

- `PasswordValidationRules.php`: centraliza las reglas de validacion de contrasenas para evitar duplicarlas.

### `app/Livewire/`

Contiene componentes y acciones Livewire para interacciones sin crear controladores HTTP tradicionales.

- `app/Livewire/Actions/Logout.php`: cierra la sesion del usuario.

Las pantallas de configuracion usan Livewire. Si un boton de configuracion no responde, revisa la vista Livewire y esta accion antes de revisar JavaScript.

### `app/Providers/`

Registra configuraciones y servicios al iniciar Laravel.

- `AppServiceProvider.php`: configuracion general de la aplicacion.
- `FortifyServiceProvider.php`: configuracion de autenticacion, login, recuperacion y dos factores.

Un error que ocurre al arrancar la aplicacion o durante autenticacion puede originarse en estos proveedores.

## Rutas

### `routes/web.php`

Define las rutas publicas y principales del panel.

- `/`: formulario y procesamiento del login.
- `/dashboard`: vista inicial autenticada.
- `/usuarios`: administracion de usuarios.
- `/sucursales`: administracion de sucursales.
- `/inventario`: consulta y alta de productos.
- `/lotes-y-caducidades`: consulta y alta de lotes.
- Punto de venta, entradas, traspasos, caja, reportes y alertas: vistas placeholder.

El modulo de sucursales permite consultar, registrar y editar nombre, direccion, telefono, correo de contacto, responsable, horario y estado activo. Estos datos se almacenan en `sucursales`; la migracion `add_operational_details_to_sucursales_table` agrega los campos operativos nuevos.

El grupo principal usa los middleware `auth` y `verified`. Si una URL redirige al login, revisa primero estos middleware y el estado de verificacion del usuario.

### `routes/settings.php`

Define las pantallas de configuracion del usuario mediante Livewire:

- `settings/profile`: perfil.
- `settings/appearance`: apariencia.
- `settings/security`: seguridad.

Si una pagina de configuracion da error de componente, compara el nombre usado en `Route::livewire()` con el archivo dentro de `resources/views/pages/settings/`.

### `routes/console.php`

Reservado para comandos Artisan personalizados. Si se agrega un comando de consola, su registro y uso deben documentarse aqui.

## Base de datos

### `database/migrations/`

Las migraciones crean y modifican tablas en orden cronologico. No se deben editar para corregir datos que ya existen en una base aplicada; para eso se crea una nueva migracion.

Tablas principales:

- roles y usuarios.
- sucursales y farmacias.
- proveedores, presentaciones, productos y lotes.
- modulos, permisos y permisos activados.
- pagos, cajas, ventas, cortes de caja y traspasos.
- tablas auxiliares de Laravel para cache y jobs.

Para un error SQL, identifica la tabla en el mensaje y abre su migracion antes de modificar el modelo o el controlador.

### `database/seeders/`

Carga catalogos y datos iniciales.

- `DatabaseSeeder.php`: punto de entrada; llama a los seeders en orden.
- `SucursalSeeder.php`: sucursales iniciales.
- `RolSeeder.php`: roles iniciales.
- `ModuloSeeder.php`: modulos disponibles.
- `PermisoSeeder.php`: tipos de permiso.
- `PresentacionProductoSeeder.php`: presentaciones.
- `UserSeeder.php`: usuarios iniciales.
- `PermisoActivadoSeeder.php`: permisos asignados.

Si una pantalla necesita un catalogo y aparece vacia, revisa primero el seeder correspondiente y confirma que `DatabaseSeeder.php` lo invoque.

### `database/factories/`

Crea datos de prueba. `UserFactory.php` genera usuarios para pruebas automatizadas o desarrollo.

## Vistas y frontend

### `resources/views/`

Contiene las vistas Blade y los componentes visuales.

- `layouts/`: estructura general de autenticacion y panel.
- `components/`: piezas Blade reutilizables, como logo, menus y mensajes.
- `pages/auth/`: login y recuperacion de contrasena.
- `pages/usuarios/`: listado, alta y edicion de usuarios.
- `pages/sucursales/`: listado y alta de sucursales.
- `pages/inventario/`: listado y alta de productos.
- `pages/lotes/`: listado y alta de lotes.
- `pages/modulos/placeholder.blade.php`: pantalla temporal para modulos pendientes.
- `pages/settings/`: componentes Livewire de perfil, apariencia y seguridad.
- `partials/`: fragmentos compartidos, como encabezados.

Si aparece `Undefined variable`, compara el nombre usado en Blade con las variables entregadas por `return view(...)` en el controlador. Si la variable existe pero la pantalla se ve incompleta, revisa el layout que la vista extiende.

### `resources/css/` y `resources/js/`

Contienen los estilos y scripts de frontend procesados por Vite. Los cambios aqui requieren `npm run build` o un servidor Vite activo para reflejarse en el navegador.

### `public/`

Es la carpeta publica del servidor web.

- `index.php`: entrada de Laravel.
- `build/`: archivos generados por Vite; no editar manualmente.
- `robots.txt`: reglas para rastreadores.

## Pruebas

### `tests/`

Contiene pruebas Pest.

- `tests/Feature/`: prueba rutas, autenticacion, seeders y flujos completos.
- `tests/Unit/`: prueba unidades aisladas.
- `tests/Pest.php`: configuracion global de Pest.
- `tests/TestCase.php`: clase base de pruebas Laravel.

Pruebas de negocio actuales:

- `DashboardTest.php`: acceso y comportamiento del dashboard.
- `InventarioTest.php`: inventario.
- `LotesCaducidadTest.php`: lotes y caducidades.
- `UsuarioControllerTest.php`: usuarios.
- `ModuloSeederTest.php` y `PresentacionProductoSeederTest.php`: catalogos.
- `Auth/`: login y recuperacion de contrasena.
- `Settings/`: perfil y seguridad.

Para una modificacion de comportamiento, ejecuta primero la prueba relacionada:

```bash
php artisan test --compact tests/Feature/InventarioTest.php
php artisan test --compact tests/Feature/LotesCaducidadTest.php
php artisan test --compact tests/Feature/UsuarioControllerTest.php
```

## Archivos de configuracion

- `.env`: valores locales y secretos; no se debe versionar.
- `.env.example`: plantilla de variables necesarias.
- `config/`: configuracion de Laravel para base de datos, autenticacion, cache, correo, archivos y sesiones.
- `composer.json`: dependencias PHP, scripts de instalacion, pruebas y desarrollo.
- `package.json`: scripts y dependencias de Vite y Tailwind.
- `vite.config.js`: entrada y compilacion de assets.
- `phpunit.xml`: configuracion de pruebas.
- `phpstan.neon`: configuracion de analisis estatico.
- `pint.json`: reglas de formato PHP.

No pongas contrasenas, tokens o datos de conexion reales en documentacion ni en archivos versionados.

## Diagnostico rapido

### La URL no existe

1. Ejecuta `php artisan route:list --except-vendor`.
2. Revisa `routes/web.php` o `routes/settings.php`.
3. Confirma que el nombre de la ruta coincida con el usado en `route()` o `redirect()->route()`.

### La pantalla muestra un error de variable o vista

1. Revisa el `return view(...)` del controlador.
2. Abre la vista dentro de `resources/views/pages/`.
3. Comprueba layout, nombre de variable y componentes incluidos.

### Falla una consulta o una relacion

1. Revisa el mensaje en `storage/logs/laravel.log`.
2. Identifica tabla, columna o relacion.
3. Compara controlador, modelo y migracion.
4. Comprueba si el seeder genero los datos relacionados.

### El formulario no guarda

1. Revisa las reglas de `$request->validate()`.
2. Compara los nombres `name` del formulario Blade.
3. Confirma `fillable`, casts y llaves foraneas del modelo.
4. Revisa errores de validacion en la vista.

### No aparece un modulo en el menu

Revisa `User::modulosVisibles()`, `PermisoActivado`, `ModuloSeeder`, `PermisoSeeder` y los permisos activos del usuario.

### Los estilos no se actualizan

```bash
php artisan optimize:clear
npm run build
```

### Logs y comandos utiles

```bash
Get-Content storage/logs/laravel.log -Tail 100
php artisan optimize:clear
php artisan route:list --except-vendor
php artisan migrate:status
php artisan test --compact
vendor/bin/pint --dirty --format agent
composer run types:check
```

## Regla para documentar nuevos modulos

Cada nuevo modulo debe dejar documentados estos puntos:

1. Ruta y middleware.
2. Controlador o componente Livewire.
3. Validaciones de entrada.
4. Modelos, tablas y relaciones.
5. Vista Blade asociada.
6. Seeder o datos necesarios.
7. Prueba automatizada.
8. Mensajes de error esperados y ubicacion del log.
