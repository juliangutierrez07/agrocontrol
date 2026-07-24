<?php
require_once("../Config/conexion.php");
require_role(['administrador']);
$con = conexion();
$uid = current_user_id();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgroControl | Gestión de Usuarios</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../Css/layout-base.css">
    <link rel="stylesheet" href="../Css/Dashboard.css">
    <link rel="stylesheet" href="../Css/usuarios.css">
</head>

<body>

<!-- ═══════════════ SIDEBAR ═══════════════ -->
<?php include 'sidebar.php'; ?>

<!-- ═══════════════ MAIN ═══════════════ -->
<div class="main">

    <!-- TOPBAR -->
    <div class="topbar">
        <div class="topbar-left">
            <div class="topbar-breadcrumb">Administración / <span>Usuarios</span></div>
            <div class="topbar-title">Gestión de Usuarios</div>
        </div>
        <div class="topbar-right">
            <button class="theme-toggle" id="themeToggle" title="Cambiar tema">
                <svg class="icon-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                    <path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/>
                </svg>
                <svg class="icon-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                    <circle cx="12" cy="12" r="5"/>
                    <line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/>
                    <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
                    <line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/>
                    <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
                </svg>
            </button>
            <button class="btn-primary" onclick="abrirModal('modalCrear')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                    <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                Nuevo Usuario
            </button>
        </div>
    </div>

    <!-- STATS -->
    <div class="stats-grid">
        <?php
            $totalUsuarios  = (int)db_value($con, "SELECT COUNT(*) FROM usuarios");
            $totalAdmins    = (int)db_value($con, "SELECT COUNT(*) FROM usuarios WHERE rol = 'administrador'");
            $totalActivos   = (int)db_value($con, "SELECT COUNT(*) FROM usuarios WHERE activo = 1");
            $totalInactivos = (int)db_value($con, "SELECT COUNT(*) FROM usuarios WHERE activo = 0");
        ?>
        <div class="stat-card">
            <div class="stat-top">
                <div class="stat-label">Total Usuarios</div>
                <div class="stat-icon si-green">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                        <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/>
                        <path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/>
                    </svg>
                </div>
            </div>
            <div class="stat-value"><?= $totalUsuarios ?></div>
            <div class="stat-meta">usuarios registrados</div>
        </div>
        <div class="stat-card">
            <div class="stat-top">
                <div class="stat-label">Administradores</div>
                <div class="stat-icon si-amber">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                        <path d="M12 2l2.9 6.3 6.9.9-5 4.9 1.2 6.9L12 17.8l-6 3.2 1.2-6.9-5-4.9 6.9-.9z"/>
                    </svg>
                </div>
            </div>
            <div class="stat-value"><?= $totalAdmins ?></div>
            <div class="stat-meta">con rol administrador</div>
        </div>
        <div class="stat-card">
            <div class="stat-top">
                <div class="stat-label">Activos</div>
                <div class="stat-icon si-blue">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                        <circle cx="12" cy="12" r="10"/><polyline points="8,12 11,15 16,9"/>
                    </svg>
                </div>
            </div>
            <div class="stat-value"><?= $totalActivos ?></div>
            <div class="stat-meta">cuentas habilitadas</div>
        </div>
        <div class="stat-card">
            <div class="stat-top">
                <div class="stat-label">Inactivos</div>
                <div class="stat-icon si-danger">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                        <circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>
                    </svg>
                </div>
            </div>
            <div class="stat-value"><?= $totalInactivos ?></div>
            <div class="stat-meta">cuentas desactivadas</div>
        </div>
    </div>

    <!-- TABLE USUARIOS -->
    <div class="contenedorTabla">
        <div class="table-toolbar">
            <div class="tt-left">
                <div class="table-heading">Usuarios Registrados</div>
                <div class="count-pill"><?= $totalUsuarios ?> usuarios</div>
            </div>
        </div>

        <?php if (isset($_GET['ok'])): ?>
        <div style="padding:10px 20px; font-size:12.5px; color:var(--accent-text); background:var(--accent-glow); border-bottom:1px solid var(--accent-border); display:flex; align-items:center; gap:6px;">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="20,6 9,17 4,12"/></svg>
            <?= match($_GET['ok']) {
                'editado'       => 'Usuario actualizado correctamente',
                'password'      => 'Contraseña restablecida correctamente',
                'desactivado'   => 'Usuario desactivado correctamente',
                'activado'      => 'Usuario activado correctamente',
                default         => 'Usuario creado correctamente'
            } ?>
        </div>
        <?php endif; ?>
        <?php if (isset($_GET['error'])): ?>
        <div style="padding:10px 20px; font-size:12.5px; color:var(--danger-text); background:var(--danger-dim); border-bottom:1px solid var(--danger-border); display:flex; align-items:center; gap:6px;">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
            <?= match($_GET['error']) {
                'correo_existente'  => 'El correo ya se encuentra registrado',
                'password_debil'    => 'La contraseña debe tener mínimo 8 caracteres',
                'auto_desactivar'   => 'No puedes desactivar tu propia cuenta',
                'auto_degradar'     => 'No puedes quitarte a ti mismo el rol de administrador',
                'ultimo_admin'      => 'Debe existir al menos un administrador activo. Asigna el rol de administrador a otro usuario antes de realizar este cambio.',
                default             => 'Ocurrió un error. Intente de nuevo'
            } ?>
        </div>
        <?php endif; ?>

        <table>
            <thead>
                <tr>
                    <th>Usuario</th>
                    <th>Correo</th>
                    <th>Rol</th>
                    <th>Estado</th>
                    <th>Registrado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    $query = db_result($con, "SELECT * FROM usuarios ORDER BY nombre ASC");
                    while ($row = mysqli_fetch_assoc($query)):
                        $initials = strtoupper(substr($row['nombre'], 0, 2));
                        $esUnoMismo = ((int)$row['id'] === $uid);
                        $activo = (int)$row['activo'] === 1;
                ?>
                <tr class="<?= $activo ? '' : 'fila-inactiva' ?>">
                    <td>
                        <div class="potrero-cell">
                            <div class="potrero-avatar"><?= $initials ?></div>
                            <div class="potrero-name">
                                <?= htmlspecialchars($row['nombre']) ?>
                                <?php if ($esUnoMismo): ?><span style="color:var(--text-muted); font-weight:400;"> (tú)</span><?php endif; ?>
                            </div>
                        </div>
                    </td>
                    <td><span class="correo-tag"><?= htmlspecialchars($row['correo']) ?></span></td>
                    <td>
                        <span class="badge-rol <?= $row['rol'] === 'administrador' ? 'badge-rol-admin' : 'badge-rol-usuario' ?>">
                            <?= $row['rol'] === 'administrador' ? 'Administrador' : 'Usuario' ?>
                        </span>
                    </td>
                    <td>
                        <span class="badge-estado <?= $activo ? 'badge-estado-activo' : 'badge-estado-inactivo' ?>">
                            <?= $activo ? 'Activo' : 'Inactivo' ?>
                        </span>
                    </td>
                    <td><span class="fecha-tag"><?= date('d/m/Y', strtotime($row['creado_en'])) ?></span></td>
                    <td>
                        <div class="actions-cell">
                            <button class="btn-icon-edit"
                                onclick="abrirEditar(
                                    <?= (int)$row['id'] ?>,
                                    <?= htmlspecialchars(json_encode($row['nombre']), ENT_QUOTES) ?>,
                                    <?= htmlspecialchars(json_encode($row['correo']), ENT_QUOTES) ?>,
                                    <?= htmlspecialchars(json_encode($row['rol']), ENT_QUOTES) ?>
                                )">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                                    <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/>
                                    <path d="M18.5 2.5a2.12 2.12 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                </svg>
                                Editar
                            </button>
                            <button class="btn-icon-key"
                                onclick="abrirPassword(<?= (int)$row['id'] ?>, <?= htmlspecialchars(json_encode($row['nombre']), ENT_QUOTES) ?>)">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                                    <circle cx="8" cy="15" r="4"/><path d="M10.5 12.5L20 3M17 6l3 3M14 9l2 2"/>
                                </svg>
                                Contraseña
                            </button>
                            <?php if ($esUnoMismo): ?>
                                <button class="btn-icon-del btn-icon-disabled" title="No puedes desactivar tu propia cuenta">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                                        <circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>
                                    </svg>
                                    Desactivar
                                </button>
                            <?php elseif ($activo): ?>
                                <form method="POST" action="ToggleUsuario.php" style="display:inline"
                                      onsubmit="return confirm('¿Está seguro de desactivar a <?= htmlspecialchars(addslashes($row['nombre'])) ?>? Podrá reactivarlo cuando quiera.')">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                                    <button type="submit" class="btn-icon-del">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                                            <circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>
                                        </svg>
                                        Desactivar
                                    </button>
                                </form>
                            <?php else: ?>
                                <form method="POST" action="ToggleUsuario.php" style="display:inline">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                                    <button type="submit" class="btn-icon-restore">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                                            <polyline points="20,6 9,17 4,12"/>
                                        </svg>
                                        Activar
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

</div><!-- /main -->

<!-- ═══════════════ MODAL CREAR USUARIO ═══════════════ -->
<div class="modalOverlay" id="modalCrear">
    <div class="contenedorModal1">
        <div class="modal-header">
            <div class="modal-header-info">
                <div class="modal-eyebrow"><div class="eyebrow-dot"></div>Nuevo registro</div>
                <h2>Crear Usuario</h2>
                <div class="modal-subtitle">Complete los datos del nuevo usuario</div>
            </div>
            <button class="btn-cerrar" onclick="cerrarModal('modalCrear')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>
        <div class="modal-divider"></div>
        <form action="CrearUsuario.php" method="POST">
            <?= csrf_field() ?>
            <div class="modal-body">
                <div class="field">
                    <label class="field-label" for="nombre">Nombre <span class="field-req">*</span></label>
                    <input type="text" id="nombre" name="nombre" placeholder="Ej: Juan Pérez" maxlength="50" required>
                </div>
                <div class="field">
                    <label class="field-label" for="correo">Correo electrónico <span class="field-req">*</span></label>
                    <input type="email" id="correo" name="correo" placeholder="usuario@ejemplo.com" maxlength="150" required>
                </div>
                <div class="field">
                    <label class="field-label" for="password">Contraseña <span class="field-req">*</span></label>
                    <input type="password" id="password" name="password" placeholder="Mínimo 8 caracteres" minlength="8" maxlength="150" required>
                </div>
                <div class="field">
                    <label class="field-label" for="rol">Rol <span class="field-req">*</span></label>
                    <select name="rol" id="rol" required>
                        <option value="usuario">Usuario</option>
                        <option value="administrador">Administrador</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <div class="modal-hint">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                        <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                    Los campos con * son obligatorios
                </div>
                <div class="modal-footer-btns">
                    <button type="button" class="btn-modal-cancel" onclick="cerrarModal('modalCrear')">Cancelar</button>
                    <button type="submit" class="btn-modal-submit">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                            <polyline points="20,6 9,17 4,12"/>
                        </svg>
                        Crear Usuario
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- ═══════════════ MODAL EDITAR USUARIO ═══════════════ -->
<div class="modalOverlay" id="modalEditar">
    <div class="contenedorModal1">
        <div class="modal-header">
            <div class="modal-header-info">
                <div class="modal-eyebrow modal-eyebrow-edit">
                    <div class="eyebrow-dot eyebrow-dot-edit"></div>
                    Editar registro
                </div>
                <h2>Editar Usuario</h2>
                <div class="modal-subtitle">Modifica los datos del usuario</div>
            </div>
            <button class="btn-cerrar" onclick="cerrarModal('modalEditar')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>
        <div class="modal-divider"></div>
        <form action="ActualizarUsuario.php" method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="id" id="edit_id">
            <div class="modal-body">
                <div class="field">
                    <label class="field-label" for="edit_nombre">Nombre <span class="field-req">*</span></label>
                    <input type="text" id="edit_nombre" name="nombre" maxlength="50" required>
                </div>
                <div class="field">
                    <label class="field-label" for="edit_correo">Correo electrónico <span class="field-req">*</span></label>
                    <input type="email" id="edit_correo" name="correo" maxlength="150" required>
                </div>
                <div class="field">
                    <label class="field-label" for="edit_rol">Rol <span class="field-req">*</span></label>
                    <select name="rol" id="edit_rol" required>
                        <option value="usuario">Usuario</option>
                        <option value="administrador">Administrador</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <div class="modal-hint">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                        <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                    Los campos con * son obligatorios
                </div>
                <div class="modal-footer-btns">
                    <button type="button" class="btn-modal-cancel" onclick="cerrarModal('modalEditar')">Cancelar</button>
                    <button type="submit" class="btn-modal-submit btn-modal-edit">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                            <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/>
                            <path d="M18.5 2.5a2.12 2.12 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>
                        </svg>
                        Guardar Cambios
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- ═══════════════ MODAL RESTABLECER CONTRASEÑA ═══════════════ -->
<div class="modalOverlay" id="modalPassword">
    <div class="contenedorModal1">
        <div class="modal-header">
            <div class="modal-header-info">
                <div class="modal-eyebrow" style="color:var(--blue-text)">
                    <div class="eyebrow-dot" style="background:var(--blue)"></div>
                    Seguridad
                </div>
                <h2>Restablecer Contraseña</h2>
                <div class="modal-subtitle" id="password_subtitulo">Define una nueva contraseña para el usuario</div>
            </div>
            <button class="btn-cerrar" onclick="cerrarModal('modalPassword')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>
        <div class="modal-divider"></div>
        <form action="ResetPasswordUsuario.php" method="POST" id="formPassword" onsubmit="return validarPasswords()">
            <?= csrf_field() ?>
            <input type="hidden" name="id" id="password_id">
            <div class="modal-body">
                <div class="field">
                    <label class="field-label" for="nueva_password">Nueva contraseña <span class="field-req">*</span></label>
                    <input type="password" id="nueva_password" name="password" placeholder="Mínimo 8 caracteres" minlength="8" maxlength="150" required>
                </div>
                <div class="field">
                    <label class="field-label" for="confirmar_password">Confirmar contraseña <span class="field-req">*</span></label>
                    <input type="password" id="confirmar_password" placeholder="Repite la contraseña" minlength="8" maxlength="150" required>
                </div>
                <div class="field-hint-block">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                        <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                    No se necesita la contraseña anterior para restablecerla
                </div>
            </div>
            <div class="modal-footer">
                <div class="modal-hint">&nbsp;</div>
                <div class="modal-footer-btns">
                    <button type="button" class="btn-modal-cancel" onclick="cerrarModal('modalPassword')">Cancelar</button>
                    <button type="submit" class="btn-modal-submit btn-modal-key">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                            <circle cx="8" cy="15" r="4"/><path d="M10.5 12.5L20 3M17 6l3 3M14 9l2 2"/>
                        </svg>
                        Restablecer
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
/* ══════════════════════════════════════════════
   MODALES — abrir / cerrar
   ══════════════════════════════════════════════ */
function abrirModal(id)  { document.getElementById(id).classList.add('activo'); }
function cerrarModal(id) { document.getElementById(id).classList.remove('activo'); }
document.querySelectorAll('.modalOverlay').forEach(overlay => {
    overlay.addEventListener('click', e => { if (e.target === overlay) overlay.classList.remove('activo'); });
});

function abrirEditar(id, nombre, correo, rol) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_nombre').value = nombre;
    document.getElementById('edit_correo').value = correo;
    document.getElementById('edit_rol').value = rol;
    abrirModal('modalEditar');
}

function abrirPassword(id, nombre) {
    document.getElementById('password_id').value = id;
    document.getElementById('password_subtitulo').textContent = 'Nueva contraseña para ' + nombre;
    document.getElementById('formPassword').reset();
    document.getElementById('password_id').value = id;
    abrirModal('modalPassword');
}

function validarPasswords() {
    const p1 = document.getElementById('nueva_password').value;
    const p2 = document.getElementById('confirmar_password').value;
    if (p1 !== p2) {
        showToast('⚠️ Las contraseñas no coinciden', 'error');
        return false;
    }
    return true;
}

/* ══════════════════════════════════════════════
   THEME TOGGLE
   ══════════════════════════════════════════════ */
const toggle = document.getElementById('themeToggle');
if (localStorage.getItem('theme') === 'light') document.documentElement.classList.add('light');
toggle.addEventListener('click', () => {
    document.documentElement.classList.toggle('light');
    localStorage.setItem('theme', document.documentElement.classList.contains('light') ? 'light' : 'dark');
});

/* ══════════════════════════════════════════════
   TOAST
   ══════════════════════════════════════════════ */
function showToast(msg, type = 'success') {
    const t = document.getElementById('toast');
    document.getElementById('toast-msg').textContent = msg;
    t.style.borderColor = type === 'error' ? 'var(--danger-border)' : 'var(--accent-border)';
    t.style.color       = type === 'error' ? 'var(--danger-text)'   : 'var(--accent-text)';
    t.classList.add('show');
    setTimeout(() => t.classList.remove('show'), 3500);
}
</script>

<div id="toast" class="toast"><span id="toast-msg"></span></div>

</body>
</html>
