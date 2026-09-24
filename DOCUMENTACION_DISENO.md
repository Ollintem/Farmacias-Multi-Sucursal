# FarmaERP — Sistema de diseño y modo oscuro

## Resumen
Se aplicó un sistema de diseño unificado a todas las vistas con psicología del color
para farmacia y un interruptor global de modo claro/oscuro que persiste entre vistas.

## Paleta (psicología del color)
- **Teal medio `#0e9384`** (primario): salud, higiene, calma y confianza.
- **Petróleo `#245a6b` / `#20444c`** (secundario): profesionalismo médico; fondo del
  panel lateral en ambos modos.
- **Fondo claro salvia `#e9efec`** y tarjetas `#f7faf9`: se evita el blanco puro
  para reducir fatiga visual.
- **Modo oscuro petróleo suave `#1a2a32`** y superficies `#22353f` / `#1e3039`:
  se evita el negro puro.
- **Acentos semánticos**: éxito `#1a936f`, alerta ámbar `#b7791f`,
  peligro terracota `#c0564b`, info azul `#2a7fa0`.

## Modo oscuro
- Un único interruptor en el panel lateral (`x-theme-toggle`), sin emojis (iconos SVG).
- Persistencia en `localStorage` (`farmacia-theme-modo`: `claro | oscuro | sistema`);
  se aplica a todas las vistas y no cambia al navegar hasta que el usuario lo conmuta.
- `window.FarmaTheme.aplicar()/alternar()` en `resources/js/app.js`, con re-aplicación
  tras cada navegación Livewire (`livewire:navigated`) y script anti-parpadeo en
  `resources/views/partials/head.blade.php`.
- La página Ajustes → Apariencia conserva las 3 tarjetas (claro/oscuro/sistema) y
  guarda la preferencia en sesión (`theme_modo`).

## Correcciones de contraste
- Barra lateral: texto claro forzado en ambos modos con bloque CSS sin `@layer`
  (las utilidades de Flux/Tailwind viven en capas y ganaban en modo claro, dejando
  la letra invisible). Selector de item activo corregido a `[data-current]`, que es
  lo que Flux realmente renderiza.
- Formularios (usuario, inventario/producto, lotes, sucursales): la regla
  `:invalid` solo fuerza fondo claro en modo claro; colores explícitos para las
  `<option>` de los desplegables, `placeholder` por modo y `color-scheme: dark`.

## Archivos tocados
- `resources/css/app.css` — tokens `--color-farma-*`, superficies `.module-*`,
  botones `.theme-*`, sidebar `.erp-sidebar`, interruptor `.farma-theme-toggle`,
  reglas de formularios y opciones.
- `resources/js/app.js` — `window.FarmaTheme`, persistencia y re-aplicación.
- `resources/views/partials/head.blade.php` — tema antes del primer pintado.
- `resources/views/components/theme-toggle.blade.php` — nuevo interruptor (solo sidebar).
- `resources/views/layouts/app/sidebar.blade.php` — colores del menú y toggle único.
- `resources/views/components/desktop-user-menu.blade.php` — enlace a Apariencia.
- `resources/views/dashboard.blade.php`, `pages/auth/login.blade.php`,
  `livewire/pages/settings/appearance.blade.php`, `pages/lotes/create.blade.php`,
  `pages/usuarios/edit.blade.php` — paleta unificada y variantes oscuras.

## Dependencias de estilos (no se agregó ninguna nueva)
Instalar con el proyecto; solo hay que asegurarse de tenerlas y compilar:

| Paquete (npm)          | Versión   | Uso                              |
|------------------------|-----------|----------------------------------|
| `tailwindcss`          | `^4.0.7`  | Utilidades y `@theme`            |
| `@tailwindcss/vite`    | `^4.1.11` | Plugin de Vite                   |
| `laravel-vite-plugin`  | `^3.1`    | Integración Laravel + Vite       |
| `vite`                 | `^8.0.0`  | Compilador de assets             |

| Paquete (composer)     | Versión    | Uso                              |
|------------------------|------------|----------------------------------|
| `livewire/flux`        | `^2.13.1`  | Componentes UI (sidebar, menús)  |
| `livewire/livewire`    | `^4.1`     | Interactividad / navegación SPA  |
| `livewire/blaze`       | `^1.0`     | Compilador de componentes Flux   |

## Instalación y compilación
```bash
composer install
npm install
npm run build    # producción (genera public/build)
npm run dev      # desarrollo (recarga en caliente)
```
Si un cambio visual no se refleja, ejecutar `npm run dev` (o `composer run dev`)
y recargar. Verificado con `npm run build` y `php artisan view:cache` sin errores.
