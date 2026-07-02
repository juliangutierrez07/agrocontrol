/* ═══════════════════════════════════════════
   AgroControl — registro_vacas.js
   Mantiene la lógica original del modal PHP
   + selector visual de estado
   + toggle de tema claro/oscuro
   ═══════════════════════════════════════════ */

/* ── MODAL (funciones originales conservadas) ── */
function abrirModal() {
    document.getElementById('modalOverlay').classList.add('activo');
}

function cerrarModal() {
    document.getElementById('modalOverlay').classList.remove('activo');
}

function cerrarModalFuera(event) {
    if (event.target === document.getElementById('modalOverlay')) {
        cerrarModal();
    }
}

/* ── SELECTOR VISUAL DE ESTADO ── */
function selEstado(valor) {
    // Actualiza el input hidden que se envía al PHP
    document.getElementById('estadoHidden').value = valor;

    // Mapeo id → clase seleccionada
    const opts = {
        'opt-prod': { val: 'produccion', cls: 'sel-prod' },
        'opt-sec':  { val: 'secado',     cls: 'sel-sec'  },
        'opt-enr':  { val: 'enrazada',   cls: 'sel-enr'  },
    };

    Object.keys(opts).forEach(function(id) {
        const el = document.getElementById(id);
        // Quitar todas las clases de selección
        el.classList.remove('sel-prod', 'sel-sec', 'sel-enr');
        // Aplicar la correcta al seleccionado
        if (opts[id].val === valor) {
            el.classList.add(opts[id].cls);
        }
    });
}

/* ── VALIDACIÓN: asegurar que se seleccionó un estado antes de enviar ── */
(function() {
    var form = document.querySelector('.contenedorModal1 form');
    if (form) {
        form.addEventListener('submit', function(e) {
            var estado = document.getElementById('estadoHidden').value;
            if (!estado) {
                e.preventDefault();
                // Resalta los botones de estado
                document.querySelectorAll('.status-opt').forEach(function(el) {
                    el.style.borderColor = 'var(--danger)';
                    setTimeout(function() { el.style.borderColor = ''; }, 1500);
                });
                mostrarToast('Por favor selecciona el estado productivo del animal.', 'error');
            }
        });
    }
})();

/* ── TOGGLE TEMA CLARO / OSCURO ── */
function toggleTheme() {
    var html = document.documentElement;
    html.classList.toggle('light');
    try {
        localStorage.setItem('acTheme', html.classList.contains('light') ? 'light' : 'dark');
    } catch(e) {}
}

/* Aplica el tema guardado al cargar la página */
(function() {
    try {
        if (localStorage.getItem('acTheme') === 'light') {
            document.documentElement.classList.add('light');
        }
    } catch(e) {}
})();

/* ── TOAST SYSTEM ── */
var _toastContainer = null;

function _getToastContainer() {
    if (!_toastContainer) {
        _toastContainer = document.getElementById('toast-container');
        if (!_toastContainer) {
            _toastContainer = document.createElement('div');
            _toastContainer.id = 'toast-container';
            _toastContainer.className = 'toast-container';
            document.body.appendChild(_toastContainer);
        }
    }
    return _toastContainer;
}

function _toastIcon(tipo) {
    var icons = {
        success: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><polyline points="20,6 9,17 4,12"/></svg>',
        error:   '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>',
        info:    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><circle cx="12" cy="8" r="0.5" fill="currentColor"/></svg>',
    };
    return icons[tipo] || icons.info;
}

function _toastTitle(tipo) {
    var titles = { success: 'Éxito', error: 'Error', info: 'Información' };
    return titles[tipo] || 'Aviso';
}

function mostrarToast(mensaje, tipo, duracion) {
    tipo = tipo || 'success';
    duracion = duracion || 4000;
    var container = _getToastContainer();

    var el = document.createElement('div');
    el.className = 'toast ' + tipo;
    el.setAttribute('role', 'alert');
    el.setAttribute('aria-live', 'assertive');
    el.innerHTML =
        '<div class="toast-icon">' + _toastIcon(tipo) + '</div>' +
        '<div class="toast-body">' +
            '<div class="toast-title">' + _toastTitle(tipo) + '</div>' +
            '<div class="toast-msg">' + _escapeHtml(mensaje) + '</div>' +
        '</div>' +
        '<button class="toast-close" aria-label="Cerrar notificación">' +
            '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>' +
        '</button>';

    container.appendChild(el);

    // Forzar reflow para activar la transición de entrada
    el.getBoundingClientRect();
    el.classList.add('show');

    var timer = setTimeout(function() { _cerrarToast(el); }, duracion);

    // Pausar barra de progreso al hacer hover
    el.addEventListener('mouseenter', function() {
        clearTimeout(timer);
        var bar = el.querySelector('::after');
        el.style.setProperty('animation-play-state', 'paused');
    });
    el.addEventListener('mouseleave', function() {
        timer = setTimeout(function() { _cerrarToast(el); }, 1200);
    });

    // Botón cerrar manual
    el.querySelector('.toast-close').addEventListener('click', function() {
        clearTimeout(timer);
        _cerrarToast(el);
    });
}

function _cerrarToast(el) {
    el.classList.add('hiding');
    el.classList.remove('show');
    setTimeout(function() {
        if (el.parentNode) el.parentNode.removeChild(el);
    }, 300);
}

function _escapeHtml(str) {
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

/* ── MENSAJES DE RESPUESTA VÍA QUERY PARAMS ── */
(function() {
    var mensajes = {
        'ok=creado'              : ['Vaca registrada correctamente en el sistema.', 'success'],
        'ok=editado'             : ['Datos de la vaca actualizados correctamente.', 'success'],
        'ok=eliminado'           : ['La vaca fue eliminada del sistema.', 'success'],
        'error=no_encontrada'    : ['No se encontró la vaca solicitada.', 'error'],
        'error=datos_invalidos'  : ['Datos inválidos. Verifica el formulario.', 'error'],
        'error=codigo_existente' : ['Ya existe una vaca con ese código. Usa uno diferente.', 'error'],
        'error=db'               : ['Error al procesar la solicitud. Intenta de nuevo.', 'error'],
    };

    var qs = window.location.search.slice(1);
    for (var key in mensajes) {
        if (qs.indexOf(key) !== -1) {
            (function(info) {
                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', function() { mostrarToast(info[0], info[1]); });
                } else {
                    mostrarToast(info[0], info[1]);
                }
            })(mensajes[key]);
            // Limpiar el query param de la URL sin recargar
            try {
                var cleanUrl = window.location.pathname;
                window.history.replaceState({}, '', cleanUrl);
            } catch(e) {}
            break;
        }
    }
})();
