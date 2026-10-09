{{-- Modal único de breve detalle de notificación (una sola instancia a nivel de body).
     Se abre al seleccionar un aviso y ofrece ir al apartado + marcar/desmarcar. --}}
<div id="noti-detalle-modal" class="noti-modal" hidden>
    <div class="noti-modal-fondo" data-noti-cerrar></div>
    <div class="noti-modal-caja" role="dialog" aria-modal="true" aria-labelledby="noti-detalle-titulo">
        <div class="noti-modal-head">
            <span id="noti-detalle-icono" class="noti-icono grande prioridad-media" aria-hidden="true">•</span>
            <div class="min-w-0 flex-1">
                <p id="noti-detalle-tipo" class="noti-modal-tipo">Notificación</p>
                <h2 id="noti-detalle-titulo" class="noti-modal-titulo">Detalle</h2>
            </div>
            <span id="noti-detalle-estado-pill" class="noti-estado-pill no-leida">No leída</span>
            <button type="button" class="noti-modal-cerrar" data-noti-cerrar aria-label="Cerrar detalle">✕</button>
        </div>

        <div class="noti-modal-cuerpo">
            <p id="noti-detalle-detalle" class="noti-modal-detalle"></p>
            <blockquote id="noti-detalle-mensaje" class="noti-modal-mensaje" hidden></blockquote>
            <dl class="noti-modal-meta">
                <div class="noti-modal-meta-celda">
                    <dt>Estado</dt>
                    <dd id="noti-detalle-estado">No leída</dd>
                </div>
                <div class="noti-modal-meta-celda">
                    <dt>Recibida</dt>
                    <dd id="noti-detalle-tiempo">reciente</dd>
                </div>
                <div class="noti-modal-meta-celda">
                    <dt>Fecha</dt>
                    <dd id="noti-detalle-fecha">—</dd>
                </div>
            </dl>
            <p id="noti-detalle-error" class="noti-modal-error" hidden></p>
        </div>

        <div class="noti-modal-acciones">
            <a id="noti-detalle-ir" href="#" class="theme-button theme-button-primary" data-noti-ir="">Ir al apartado</a>
            <button type="button" id="noti-detalle-toggle" class="theme-button theme-button-secondary">Marcar como leída</button>
            <button type="button" class="theme-button theme-button-secondary" data-noti-cerrar>Cerrar</button>
        </div>
    </div>
</div>

<script>
(function () {
    if (window.__notiDetalleInit) {
        return;
    }
    window.__notiDetalleInit = true;

    const urls = {
        detalle: @json(route('alertas.detalle')),
        leer: @json(route('alertas.leer')),
        noLeida: @json(route('alertas.no-leida')),
    };

    const etiquetasTipo = {
        traspasos: 'Traspasos',
        caducidades: 'Caducidades',
        caducidad: 'Caducidades',
        stock: 'Stock',
        ventas: 'Ventas',
    };

    function csrf() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    }

    function modal() {
        return document.getElementById('noti-detalle-modal');
    }

    function cerrar() {
        modal()?.setAttribute('hidden', '');
        document.body.classList.remove('noti-modal-abierto');
    }

    function pintarEstado(leida) {
        document.getElementById('noti-detalle-estado').textContent = leida ? 'Leída' : 'No leída';

        const pill = document.getElementById('noti-detalle-estado-pill');
        pill.textContent = leida ? 'Leída' : 'No leída';
        pill.classList.toggle('leida', leida);
        pill.classList.toggle('no-leida', !leida);
    }

    function pintar(aviso) {
        document.getElementById('noti-detalle-tipo').textContent = etiquetasTipo[aviso.tipo] || 'Notificación';
        document.getElementById('noti-detalle-titulo').textContent = aviso.titulo || 'Detalle';
        document.getElementById('noti-detalle-detalle').textContent = aviso.detalle || '';

        const mensaje = document.getElementById('noti-detalle-mensaje');
        if (aviso.mensaje) {
            mensaje.textContent = '\u201C' + aviso.mensaje + '\u201D';
            mensaje.hidden = false;
        } else {
            mensaje.hidden = true;
        }

        const icono = document.getElementById('noti-detalle-icono');
        icono.textContent = aviso.icono || '•';
        icono.className = 'noti-icono grande prioridad-' + (aviso.prioridad || 'media');

        document.getElementById('noti-detalle-tiempo').textContent = aviso.tiempo || aviso.tiempo_corto || 'reciente';
        document.getElementById('noti-detalle-fecha').textContent = aviso.fecha_texto || '—';
        pintarEstado(!!aviso.leida);

        const ir = document.getElementById('noti-detalle-ir');
        ir.href = aviso.destino_url || aviso.url || '#';
        ir.textContent = aviso.destino_etiqueta || 'Ir al apartado';
        ir.setAttribute('data-noti-ir', aviso.id || '');

        const toggle = document.getElementById('noti-detalle-toggle');
        toggle.textContent = aviso.leida ? 'Desmarcar (no leída)' : 'Marcar como leída';
        toggle.dataset.id = aviso.id;
        toggle.dataset.leida = aviso.leida ? '1' : '0';
    }

    async function abrir(id) {
        const caja = modal();
        if (!caja) {
            return;
        }
        const error = document.getElementById('noti-detalle-error');
        error.hidden = true;

        caja.removeAttribute('hidden');
        document.body.classList.add('noti-modal-abierto');
        document.getElementById('noti-detalle-titulo').textContent = 'Cargando detalle…';
        document.getElementById('noti-detalle-detalle').textContent = '';
        document.getElementById('noti-detalle-fecha').textContent = '—';
        document.getElementById('noti-detalle-mensaje').hidden = true;

        try {
            const res = await fetch(urls.detalle + '?id=' + encodeURIComponent(id), { headers: { Accept: 'application/json' } });
            if (!res.ok) {
                throw new Error('No se pudo cargar el detalle.');
            }
            const data = await res.json();
            pintar(data.aviso);
        } catch (e) {
            error.textContent = 'No se pudo cargar el breve detalle. Intenta de nuevo.';
            error.hidden = false;
        }
    }

    function avisarActualizada(id, leida, noLeidas) {
        document.dispatchEvent(new CustomEvent('notificacion:actualizada', { detail: { id, leida, no_leidas: noLeidas } }));
    }

    async function alternarLeida(boton) {
        const id = boton.dataset.id || '';
        const esLeida = boton.dataset.leida === '1';
        const destino = esLeida ? urls.noLeida : urls.leer;

        try {
            const res = await fetch(destino, {
                method: 'POST',
                headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf() },
                body: JSON.stringify({ id }),
            });
            if (!res.ok) {
                throw new Error('error');
            }
            const data = await res.json();
            const leida = typeof data.leida === 'boolean' ? data.leida : !esLeida;
            boton.dataset.leida = leida ? '1' : '0';
            boton.textContent = leida ? 'Desmarcar (no leída)' : 'Marcar como leída';
            pintarEstado(leida);
            avisarActualizada(id, leida, data.no_leidas);
        } catch (e) {
            const error = document.getElementById('noti-detalle-error');
            error.textContent = 'No se pudo actualizar el estado. Intenta de nuevo.';
            error.hidden = false;
        }
    }

    // Ir al apartado marca el aviso como leído antes de navegar,
    // para que al regresar a notificaciones ya no aparezca pendiente.
    function irAlApartado(enlace) {
        const id = enlace.getAttribute('data-noti-ir') || '';
        const href = enlace.getAttribute('href') || '#';

        if (!id || href === '#') {
            return;
        }

        try {
            fetch(urls.leer, {
                method: 'POST',
                headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf() },
                body: JSON.stringify({ id }),
                keepalive: true,
            }).then(async (res) => {
                if (res.ok) {
                    const data = await res.json().catch(() => ({}));
                    avisarActualizada(id, true, data.no_leidas);
                }
            }).catch(() => {});
        } catch (e) {
            /* aunque falle la red, se navega al apartado */
        }

        cerrar();
        window.location.href = href;
    }

    document.addEventListener('click', (evento) => {
        const toggle = evento.target.closest('#noti-detalle-toggle');
        if (toggle) {
            alternarLeida(toggle);
            return;
        }

        const ir = evento.target.closest('[data-noti-ir]');
        if (ir && ir.getAttribute('data-noti-ir')) {
            evento.preventDefault();
            irAlApartado(ir);
            return;
        }

        const abrirBtn = evento.target.closest('[data-noti-detalle]');
        if (abrirBtn) {
            evento.preventDefault();
            abrir(abrirBtn.getAttribute('data-noti-detalle'));
            return;
        }

        if (evento.target.closest('[data-noti-cerrar]')) {
            cerrar();
        }
    });

    document.addEventListener('keydown', (evento) => {
        if (evento.key === 'Escape') {
            cerrar();
        }
    });

    window.abrirDetalleNotificacion = abrir;
})();
</script>
