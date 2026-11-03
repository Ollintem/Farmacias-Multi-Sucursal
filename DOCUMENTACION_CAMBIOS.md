# Farmacias Multi Sucursal — Cambios aplicados

## 2026-11-03 — Módulo Caja (multi-caja, 2 turnos, auditoría y corrección de bloqueo POS)

### Qué se hizo
- **Nueva vista `Caja`** (`/caja`): multi-caja por sucursal (`Caja 1/2/3/Móvil`), **2 turnos fijos Mañana (Matutino 08-15) / Tarde (Vespertino 15-22)**, apertura con fondo inicial, movimientos (ingreso/retiro con motivo), cierre con cálculo `esperado = inicial + ventas + ingresos − retiros` y `diferencia`, soporte `corte ciego`.
- **Tabla `movimientos_caja`** (`id_corte_caja → cortes_caja`, `id_usuario`, `tipo ingreso/retiro`, `monto`, `motivo`, `creado_en`) + columna `cortes_caja.caja` (default `Caja 1`) + `ventas.id_corte_caja` (FK nullable) para ligar cada venta a su corte.
- **Bloqueo POS**: `PuntoDeVenta::confirmarVenta()` exige caja abierta en la sucursal (prioriza la del mismo usuario) y asigna `id_corte_caja` a la venta; sin caja muestra `No hay caja abierta...`.
- **Topbar + modales**: KPIs (Abiertas/Esperado/Ventas hoy/Historial) movidos al `farma-topbar` (ahorro de espacio), botones **Abrir caja / Resumen / Historial** arriba con modales; instrucciones “Cómo usar esta caja” debajo del listado.
- **Ventana dedicada auditoría** (`/caja/auditoria`): filtros (desde/hasta, turno, caja, operador, estado cuadrado/faltante/sobrante), resumen (cuadrados/faltantes/sobrantes/balance), tabla con trazabilidad (usuario, sucursal, timestamps, movs/vtas).
- **Corrección 500** `creado_en` ambiguo tras `JOIN pagos` (cualificado a `ventas.creado_en`) en `CajaController::calcularEsperado` y `sidebar` topbar.

### Migraciones incluidas
- `2026_11_03_030247_add_caja_to_cortes_caja_y_vincular_ventas`
- `2026_11_03_030252_create_movimientos_caja_table`

### Al bajar los cambios (obligatorio)
```bash
git pull
composer install
php artisan migrate --force   # aplica las 2 migraciones de caja (idempotentes)
php artisan view:clear && php artisan config:clear
npm install && npm run build  # si cambiaste Blade/CSS
```
En instalaciones nuevas `php artisan migrate:fresh --seed` ya lo deja listo. Si tu equipo usa `composer run setup` también ejecuta `migrate --force` y `npm run build`.

---

# Farmacias Multi Sucursal — Cambios aplicados (histórico)

## Resumen
Se mejoró la experiencia visual y funcional del sistema para un flujo ERP de farmacia con estos módulos principales:

- autenticación y diseño visual premium del login
- dashboard con paleta médica en verde/teal
- módulo de inventario conectado a la base de datos
- módulo de lotes y caducidades con filtros reales por sucursal
- registro de nuevos lotes y productos con captura por código de barras

## Cambios principales

### 1) Inventario
- Se habilitó la vista de inventario con selección real de sucursal.
- La tabla consume registros reales de la base de datos y no datos quemados en Blade.
- Se agregó búsqueda por código de barras, nombre o código interno.
- Se incorporó la opción de agregar producto nuevo con:
  - sucursal
  - código de barras
  - nombre
  - stock
  - precio
  - lote
  - presentación
  - producto controlado

### 2) Lotes y caducidades
- Se rediseñó la vista con un estilo más limpio y profesional.
- Se conectó con datos reales de lotes, proveedores y sucursales.
- Se agregó filtro por sucursal.
- Se agregó búsqueda por folio, producto o proveedor.
- Se calculan estados realistas de caducidad:
  - vigente
  - por caducar
  - caducado
- Se añadió la pantalla para registrar un nuevo lote y asociarlo a un producto.

### 3) UX y diseño
- Se eliminó el botón de regreso en el flujo de lote para no romper la experiencia.
- Se agregó botón de cancelar en el formulario de lotes.
- Se dejó un flujo más natural para la captura con lector de códigos de barras.

## Problemas comunes y cómo resolverlos

### 1) Los estilos no cargan o se ven viejos
Ejecuta:

```bash
php artisan view:clear
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan optimize:clear
npm run build
```

Esto limpia vistas compiladas y vuelve a construir el frontend.

### 2) Error de SQLite no disponible
Si la prueba o la base de datos local falla con el mensaje de driver SQLite no encontrado, revisa que la extensión `sqlite3` esté instalada en PHP y luego reinicia el servidor.

### 3) Vite/Frontend sin compilar
Si el CSS o la interfaz no reflejan cambios:

```bash
npm run build
```

### 4) Rutas no registradas
Si aparece un problema con rutas nuevas:

```bash
php artisan route:clear
php artisan route:list
```

## Recomendación final
Antes de seguir desarrollando módulos nuevos, se recomienda limpiar cachés y volver a compilar cada vez que haya cambios de Blade, estilos o rutas.
