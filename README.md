# Farmacias Multi Sucursal

Este proyecto es una aplicación Laravel para gestionar farmacias multi-sucursal, con módulos de usuarios, sucursales, inventario y lotes/caducidades.

## Estructura principal del proyecto

### app/
Carpeta principal de la lógica de la aplicación.

- app/Http/Controllers/
  Contiene los controladores HTTP. Aquí se define la lógica que responde a rutas como login, dashboard, usuarios y lotes.
  - Ejemplo: `LotesController` para la pantalla de lotes y caducidades.

- app/Models/
  Contiene los modelos de Eloquent que representan tablas de la base de datos.
  - Ejemplo: `Lote`, `Producto`, `Proveedor`, `Sucursal`, `User`.

- app/Providers/
  Registro de servicios y proveedores de Laravel.

### routes/
Aquí se definen las rutas de la aplicación.

- routes/web.php
  Contiene las rutas visibles del sistema web, como `/dashboard`, `/usuarios`, `/lotes-y-caducidades`.

### resources/views/
Aquí van todas las vistas Blade del sistema.

- resources/views/layouts/
  Plantillas base del panel y layout general.

- resources/views/lotes/
  Pantalla para Lotes y caducidades. Aquí se muestra la tabla, filtros y diseño del módulo.

- resources/views/usuarios/
  Vistas para gestión de usuarios.

- resources/views/sucursales/
  Vistas para alta y administración de sucursales.

### database/
Contiene todo relacionado con la persistencia de datos.

- database/migrations/
  Definen las tablas de la base de datos. Aquí se crean `lotes`, `productos`, `sucursales`, etc.

- database/seeders/
  Datos iniciales para poblar la base con roles, sucursales, usuarios y otros registros base.

### public/
Archivos públicos accesibles desde el navegador.

- public/index.php
  Punto de entrada de la aplicación.

- public/build/
  Archivos compilados de frontend si se usa Vite.

### config/
Configuración global de Laravel.

- config/app.php
- config/database.php
- config/auth.php
- config/filesystems.php
- etc.

### tests/
Pruebas automatizadas del sistema.

- tests/Feature/
  Pruebas de funcionalidad del sistema, por ejemplo la ruta de lotes.

## Módulo de lotes y caducidades

La pantalla de lotes está ubicada en:

- app/Http/Controllers/LotesController.php
- app/Models/Lote.php
- app/Models/Producto.php
- app/Models/Proveedor.php
- resources/views/lotes/index.blade.php
- routes/web.php

Con esto puedes:
- listar lotes desde la base de datos,
- obtener la fecha de caducidad,
- calcular el estado del lote,
- filtrar y mostrar la información en una tabla tipo ERP.

## Como trabajar en cada carpeta

- app/ -> agregar lógica de negocio y modelos.
- routes/ -> crear nuevas pantallas y endpoints.
- resources/views/ -> diseñar la interfaz del sistema.
- database/migrations/ -> crear o ajustar tablas.
- database/seeders/ -> preparar datos iniciales.
- tests/ -> validar que tu flujo funcione.

## Comandos útiles

```bash
php artisan serve
php artisan route:list
php artisan migrate
php artisan db:seed
php artisan test
```
