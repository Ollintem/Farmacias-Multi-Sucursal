# Estructura de la base de datos — Farmacias Multi Sucursal

Motor: MySQL (`erp_multisucursal`). Convención del proyecto: PK `id` autoincremental,
auditoría `creado_en` / `actualizado_en` (o variantes) más `created_at` / `updated_at`,
y borrado en cascada hacia los catálogos padre.

> Generado desde el esquema real vigente (migraciones + `migrate:fresh --seed`).

## Diagrama de relaciones (dominio)

```text
sucursales ─┬─ usuarios ─┬─ pedidos ── lotes ── productos ─┬─ inventario ── sucursales
             │            │    │          │          │
             │            │    │          │          ├─ presentacion_producto ── presentaciones
             │            │    │          │          └─ producto_venta ── ventas ── cajas ── cortes_caja
             │            │    │          └─ proveedores ── pedidos
             │            │    └─ traspasos (sucursal_a / sucursal_b)
             │            └─ permisos_activados ── modulos
             └─ cajas / inventario / pedidos / traspasos
roles ── usuarios
pagos ── ventas
```

## Organización y accesos

### `sucursales`
| Columna | Tipo | Nulo | Default | Notas |
|---|---|---|---|---|
| id | bigint unsigned PK AI | No | — | |
| nombre_sucursal | varchar(255) | No | — | |
| direccion | varchar(255) | No | — | |
| telefono | varchar(30) | Sí | NULL | |
| correo_contacto | varchar(150) | Sí | NULL | |
| responsable | varchar(150) | Sí | NULL | |
| hora_apertura / hora_cierre | time | No | — | |
| es_activa | boolean | No | true | |
| fecha_creacion / fecha_actualizacion | timestamp | No | current | |
| created_at / updated_at | timestamp | Sí | NULL | |

### `roles`
| Columna | Tipo | Nulo | Default |
|---|---|---|---|
| id | bigint unsigned PK AI | No | — |
| tipo_rol | varchar(255) | No | — |
| descripcion | varchar(255) | Sí | NULL |
| created_at / updated_at | timestamp | Sí | NULL |

### `usuarios`
| Columna | Tipo | Nulo | Default | Notas |
|---|---|---|---|---|
| id | bigint unsigned PK AI | No | — | Tabla física `usuarios` (modelo `User`) |
| email | varchar(255) unique | No | — | |
| email_verified_at | timestamp | Sí | NULL | |
| password | varchar(255) | No | — | |
| two_factor_secret / two_factor_recovery_codes | text | Sí | NULL | Fortify 2FA |
| two_factor_confirmed_at | timestamp | Sí | NULL | |
| nombre / apellido / nombre_usuario | varchar(255) | No | — | `nombre_usuario` unique |
| es_activo | boolean | No | true | |
| id_rol → `roles.id` | FK | Sí | NULL | `nullOnDelete` |
| id_sucursal → `sucursales.id` | FK | Sí | NULL | `nullOnDelete` |
| remember_token | varchar(100) | Sí | NULL | |
| created_at / updated_at | timestamp | Sí | NULL | |

### `modulos`
| Columna | Tipo | Nulo | Default |
|---|---|---|---|
| id | bigint unsigned PK AI | No | — |
| nombre_modulo | varchar(20) | No | — |
| creado_en / actualizado_en | timestamp | No | current |
| created_at / updated_at | timestamp | Sí | NULL |

### `permisos_activados`
| Columna | Tipo | Nulo | Default | Notas |
|---|---|---|---|---|
| id | bigint unsigned PK AI | No | — | |
| id_modulo → `modulos.id` | FK | No | — | cascade |
| id_usuario → `usuarios.id` | FK | No | — | cascade |
| puede_ver / puede_crear / puede_editar / puede_borrar | boolean | No | true | |
| created_at / updated_at | timestamp | Sí | NULL | Unique (`id_modulo`, `id_usuario`) |

## Proveedores, pedidos y lotes

### `proveedores`
| Columna | Tipo | Nulo | Default |
|---|---|---|---|
| id | bigint unsigned PK AI | No | — |
| nombre_proveedor | varchar(20) | No | — |
| direccion | text | No | — |
| unidad_entrega | varchar(20) | No | — |
| telefono | varchar(20) | No | — |
| correo | varchar(30) | No | — |
| creado_en / actualizado_en | timestamp | No | current |
| created_at / updated_at | timestamp | Sí | NULL |

Relaciones: `pedidos` (1:N), `lotes` (1:N, legacy directo).

### `pedidos`
| Columna | Tipo | Nulo | Default | Notas |
|---|---|---|---|---|
| id | bigint unsigned PK AI | No | — | |
| id_proveedor → `proveedores.id` | FK | No | — | cascade |
| id_sucursal → `sucursales.id` | FK | No | — | cascade |
| pedido_por → `usuarios.id` | FK | No | — | quien solicita, cascade |
| recibido_por → `usuarios.id` | FK | Sí | NULL | quien recibe, `nullOnDelete` |
| estado | varchar(30) | No | `'pendiente'` | p. ej. pendiente / recibido / cancelado |
| entregado_en | timestamp | Sí | NULL | |
| creado_en / actualizado_en | timestamp | No | current | |
| created_at / updated_at | timestamp | Sí | NULL | |

Relaciones: `lotes` (1:N vía `lotes.id_pedido`).

### `lotes`
| Columna | Tipo | Nulo | Default | Notas |
|---|---|---|---|---|
| id | bigint unsigned PK AI | No | — | Sin timestamps propios |
| folio | varchar(20) | No | — | |
| stock_lote | int | No | 0 | Cantidad del lote |
| id_pedido → `pedidos.id` | FK | Sí | NULL | `nullOnDelete` (lógica nueva) |
| id_proveedor → `proveedores.id` | FK | Sí | NULL | Legacy directo, cascade |
| entregado_en | timestamp | No | current | |
| fecha_caducidad | timestamp | Sí | NULL | Columna histórica |
| fecha_de_caducidad | timestamp | Sí | NULL | Nombre de la nueva especificación; el modelo `Lote` mantiene ambas sincronizadas |

Relaciones: `productos` (1:N vía `productos.id_lote`).

## Productos, presentaciones e inventario

### `productos`
| Columna | Tipo | Nulo | Default | Notas |
|---|---|---|---|---|
| id | bigint unsigned PK AI | No | — | |
| codigo_barras | varchar(20) | No | — | |
| nombre_producto | varchar(120) | No | — | |
| descripcion | text | No | — | |
| stock | int | No | — | Stock global del producto |
| precio | double | No | — | Precio base |
| id_lote → `lotes.id` | FK | Sí | NULL | cascade |
| id_presentacion → `presentaciones.id` | FK | Sí | NULL | cascade |
| es_controlado | boolean | No | false | Requiere receta |
| entregado_en | timestamp | Sí | NULL | |
| es_activo | boolean | No | true | |
| creado_en / actualizado_en | timestamp | No | current | |
| created_at / updated_at | timestamp | Sí | NULL | |

### `presentaciones` 
| Columna | Tipo | Nulo | Default | Notas |
|---|---|---|---|---|
| id | bigint unsigned PK AI | No | — | Catálogo (Caja, Paquete, …), sin timestamps |
| presentacion | varchar(20) | No | — | |
| descripcion | text | No | — | |

### `presentacion_producto` (precio por presentación)
| Columna | Tipo | Nulo | Default | Notas |
|---|---|---|---|---|
| id | bigint unsigned PK AI | No | — | |
| id_presentacion → `presentaciones.id` | FK | No | — | cascade |
| producto → `productos.id` | FK | No | — | cascade (la FK se llama `producto`) |
| precio_presentacion | double | No | — | |
| created_at / updated_at | timestamp | Sí | NULL | Unique (`id_presentacion`, `producto`) |

### `inventario` (antes `producto_sucursal`)
| Columna | Tipo | Nulo | Default | Notas |
|---|---|---|---|---|
| id | bigint unsigned PK AI | No | — | Stock por sucursal |
| id_sucursal → `sucursales.id` | FK | No | — | cascade |
| id_producto → `productos.id` | FK | No | — | cascade |
| stock | int | No | 0 | |
| created_at / updated_at | timestamp | Sí | NULL | Unique (`id_sucursal`, `id_producto`) |

> La relación `Producto::sucursales()` usa esta misma tabla como pivot
> (`withPivot('stock')`), por lo que el attach crea la fila con stock 0 y el
> modelo `Inventario` gestiona el stock con `updateOrCreate`.

## Ventas y caja

### `cajas`
| Columna | Tipo | Nulo | Default |
|---|---|---|---|
| id | bigint unsigned PK AI | No | — |
| id_sucursal → `sucursales.id` | FK | No | —, cascade |
| creado_en / actualizado_en | timestamp | No | current |
| created_at / updated_at | timestamp | Sí | NULL |

### `pagos`
| Columna | Tipo | Nulo | Default |
|---|---|---|---|
| id | bigint unsigned PK AI | No | — |
| monto | double | No | — |
| metodo | varchar(20) | No | — |
| estado | varchar(20) | No | — |
| referencia | varchar(50) | No | — |
| creado_en | timestamp | No | current |

### `ventas`
| Columna | Tipo | Nulo | Default | Notas |
|---|---|---|---|---|
| id | bigint unsigned PK AI | No | — | |
| id_caja → `cajas.id` | FK | No | — | cascade |
| folio | varchar(20) | No | — | |
| descuento / total | double | No | — | |
| id_pago → `pagos.id` | FK | No | — | cascade |
| estado | varchar(20) | No | — | |
| creado_en | timestamp | No | current | |

### `producto_venta`
| Columna | Tipo | Nulo | Default | Notas |
|---|---|---|---|---|
| id | bigint unsigned PK AI | No | — | Detalle de venta |
| venta → `ventas.id` | FK | No | — | cascade |
| producto → `productos.id` | FK | No | — | cascade |
| cantidad | int | No | — | |
| precio_unidad | double | No | — | |

### `cortes_caja`
| Columna | Tipo | Nulo | Default |
|---|---|---|---|
| id | bigint unsigned PK AI | No | — |
| id_caja → `cajas.id` | FK | No | —, cascade |
| id_usuario → `usuarios.id` | FK | No | —, cascade |
| turno | varchar(20) | No | — |
| efectivo_inicial / efectivo_declarado / efectivo_esperado / diferencia | double | No | — |
| fecha_inicio | timestamp | No | — |
| fecha_cierre | timestamp | Sí | NULL |
| estado | varchar(20) | No | — |
| creado_en | timestamp | No | current |

### `configuracion_bancaria`
| Columna | Tipo | Nulo | Default |
|---|---|---|---|
| id | bigint unsigned PK AI | No | — |
| banco | varchar(100) | No | `''` |
| clabe | varchar(20) | No | `''` |
| beneficiario | varchar(150) | No | `''` |
| numero_cuenta | varchar(30) | No | `''` |
| correo_contacto | varchar(100) | No | `''` |
| created_at / updated_at | timestamp | Sí | NULL |

## Logística

### `traspasos`
| Columna | Tipo | Nulo | Default | Notas |
|---|---|---|---|---|
| id | bigint unsigned PK AI | No | — | |
| sucursal_a → `sucursales.id` | FK | No | — | origen, cascade |
| sucursal_b → `sucursales.id` | FK | No | — | destino, cascade |
| pedido_por → `usuarios.id` | FK | No | — | cascade |
| recibido_por → `usuarios.id` | FK | Sí | NULL | `nullOnDelete` |
| estado | varchar(20) | No | — | |
| creado_en | timestamp | No | current | |

## Tablas de sistema (Laravel)

| Tabla | Uso |
|---|---|
| `migrations` | Registro de migraciones aplicadas (`migration`, `batch`) |
| `sessions` | Sesiones (`id`, `user_id`, `payload`, `last_activity`) |
| `cache` / `cache_locks` | Caché de la aplicación |
| `jobs` / `job_batches` / `failed_jobs` | Colas de trabajos |
| `password_reset_tokens` | Tokens de recuperación (`email` PK) |


