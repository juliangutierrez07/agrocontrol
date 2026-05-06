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
                alert('Por favor selecciona el estado productivo del animal.');
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