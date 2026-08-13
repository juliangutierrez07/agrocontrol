<?php
require_once("../Config/conexion.php");
require_login();
$con = conexion();

$usuario_id = current_user_id();

// ── PROCESAR EDICIÓN ──
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['editar_id'])) {
    try {
        require_csrf();
        $id           = input_int($_POST, 'editar_id', 1);
        $nombre       = input_string($_POST, 'nombre', 100);
        $raza         = input_string($_POST, 'raza', 100, false);
        $edad         = input_int($_POST, 'edad', 0, 40, false);
        $estado       = input_string($_POST, 'estado', 40);
        if (!estado_vaca_valido($estado)) {
            throw new InvalidArgumentException('Estado de vaca no válido.');
        }
        $descripcion  = input_string($_POST, 'descripcion', 1000, false);
        $vacunas_info = input_string($_POST, 'vacunas_info', 1000, false);
        $partos       = input_int($_POST, 'partos', 0, 30, false);

        $fotoAnterior = db_value($con, "SELECT foto FROM vacas WHERE id = ? AND usuario_id = ? LIMIT 1", "ii", [$id, $usuario_id]);
        $fotoUpdate = isset($_FILES['editar_foto']) ? save_uploaded_image($_FILES['editar_foto'], $usuario_id, 'foto') : null;

        if ($fotoUpdate) {
            db_execute(
                $con,
                "UPDATE vacas SET nombre = ?, raza = ?, edad = ?, estado = ?, foto = ?, descripcion = ?, vacunas_info = ?, partos = ? WHERE id = ? AND usuario_id = ?",
                "ssissssiii",
                [$nombre, $raza, $edad, $estado, $fotoUpdate, $descripcion, $vacunas_info, $partos, $id, $usuario_id]
            );
            delete_cow_image($fotoAnterior);
        } else {
            db_execute(
                $con,
                "UPDATE vacas SET nombre = ?, raza = ?, edad = ?, estado = ?, descripcion = ?, vacunas_info = ?, partos = ? WHERE id = ? AND usuario_id = ?",
                "ssisssiii",
                [$nombre, $raza, $edad, $estado, $descripcion, $vacunas_info, $partos, $id, $usuario_id]
            );
        }
    } catch (Throwable $e) {
        app_log($e->getMessage());
    }

    header('Location: Registro_Vacas.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgroControl | Registro De Vacas</title>
    <link rel="stylesheet" href="../Css/layout-base.css">
    <link rel="stylesheet" href="../Css/Dashboard.css">
    <link rel="stylesheet" href="../Css/registro_vacas.css?v=20260702">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet"/>

</head>

<body>
    <?php include 'sidebar.php'; ?>
    <div class="main">
        <div class="topbar">
            <div class="topbar-left">
                <div class="topbar-breadcrumb">Principal / Vacas</div>
                <div class="topbar-title">Gestión de Vacas</div>
            </div>
            <div class="topbar-right">
                <form method="GET" class="search-form">
                    <div class="search-wrap">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><line x1="16.5" y1="16.5" x2="22" y2="22"/></svg>
                        <input type="text" name="busqueda" placeholder="Buscar nombre, código o raza…"
                            value="<?php echo isset($_GET['busqueda']) ? htmlspecialchars($_GET['busqueda']) : ''; ?>">
                        <button type="submit" class="btn-buscar">Buscar</button>
                    </div>
                </form>
                <button class="theme-toggle" id="themeToggle" onclick="toggleTheme()" title="Cambiar tema">
                    <svg class="icon-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/></svg>
                    <svg class="icon-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
                </button>
                <div class="topbar-separator"></div>
                <button type="button" class="btn-primary" onclick="abrirModal()">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Registrar vaca
                </button>
            </div>
        </div>
        <div class="stats-grid">
            <?php
$total = (int)(db_value($con, "SELECT COUNT(*) FROM vacas WHERE usuario_id = ?", "i", [$usuario_id]) ?? 0);
$prod = (int)(db_value($con, "SELECT COUNT(*) FROM vacas WHERE estado = 'produccion' AND usuario_id = ?", "i", [$usuario_id]) ?? 0);
$secado = (int)(db_value($con, "SELECT COUNT(*) FROM vacas WHERE estado = 'secado' AND usuario_id = ?", "i", [$usuario_id]) ?? 0);
$enrazada = (int)(db_value($con, "SELECT COUNT(*) FROM vacas WHERE estado = 'enrazada' AND usuario_id = ?", "i", [$usuario_id]) ?? 0);
?>
            <div class="stat-card">
                <div class="stat-top">
                    <div class="stat-label">Total registradas</div>
                    <div class="stat-icon si-green">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><ellipse cx="12" cy="8" rx="7" ry="5"/><path d="M5 13c0 3.3 3.1 6 7 6s7-2.7 7-6"/></svg>
                    </div>
                </div>
                <div class="stat-value"><?php echo $total; ?></div>
                <div class="stat-meta">animales en sistema</div>
            </div>
            <div class="stat-card">
                <div class="stat-top">
                    <div class="stat-label">En producción</div>
                    <div class="stat-icon si-green">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><path d="M8 12h8M12 8v8"/></svg>
                    </div>
                </div>
                <div class="stat-value"><?php echo $prod; ?></div>
                <div class="stat-meta">activas dando leche</div>
            </div>
            <div class="stat-card">
                <div class="stat-top">
                    <div class="stat-label">En secado</div>
                    <div class="stat-icon si-blue">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/></svg>
                    </div>
                </div>
                <div class="stat-value"><?php echo $secado; ?></div>
                <div class="stat-meta">fase de descanso</div>
            </div>
            <div class="stat-card">
                <div class="stat-top">
                    <div class="stat-label">Enrazadas</div>
                    <div class="stat-icon si-amber">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/></svg>
                    </div>
                </div>
                <div class="stat-value"><?php echo $enrazada; ?></div>
                <div class="stat-meta">gestación activa</div>
            </div>
        </div>

        <!-- TABLA -->
        <?php
            $busqueda = input_string($_GET, 'busqueda', 100, false);
        ?>
        <div class="contenedorTabla">
            <div class="table-toolbar">
                <div class="tt-left">
                    <span class="table-heading">Vacas registradas</span>
                    <?php if (isset($_GET['busqueda']) && $_GET['busqueda'] !== ""): ?>
                        <span class="search-badge">Búsqueda: "<?php echo htmlspecialchars($_GET['busqueda']); ?>"</span>
                    <?php endif; ?>
                </div>
            </div>

            <?php
                if ($busqueda !== '') {
                    $like = '%' . $busqueda . '%';
                    $query = db_result($con, "SELECT * FROM vacas WHERE usuario_id = ? AND (nombre LIKE ? OR codigo LIKE ? OR raza LIKE ?) ORDER BY nombre ASC", "isss", [$usuario_id, $like, $like, $like]);
                } else {
                    $query = db_result($con, "SELECT * FROM vacas WHERE usuario_id = ? ORDER BY nombre ASC", "i", [$usuario_id]);
                }
            ?>

            <div class="table-scroll">
                <table>
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Animal</th>
                        <th>Raza</th>
                        <th>Edad</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $hayRegistros = false;
                    while ($row = mysqli_fetch_array($query)):
                        $hayRegistros = true;
                    ?>
                    <tr>
                        <td><span class="code-tag">#<?php echo e($row['codigo']); ?></span></td>
                        <td>
                            <div class="cow-cell">
                                <div class="cow-avatar av-<?php echo strtolower(substr($row['nombre'], 0, 1)); ?>">
                                    <?php echo strtoupper(substr($row['nombre'], 0, 1)); ?>
                                </div>
                                <span class="cow-name"><?php echo e($row['nombre']); ?></span>
                            </div>
                        </td>
                        <td class="td-raza"><?php echo e($row['raza']); ?></td>
                        <td class="td-edad"><?php echo $row['edad'] ? (int)$row['edad'].' años' : '—'; ?></td>
                        <td>
                            <?php
                                $estado = $row['estado'];
                                $badgeClass = 'badge-default';
                                $badgeLabel = $estado;
                                if ($estado === 'produccion')  { $badgeClass = 's-prod'; $badgeLabel = 'Producción'; }
                                if ($estado === 'secado')      { $badgeClass = 's-sec';  $badgeLabel = 'Secado'; }
                                if ($estado === 'enrazada')    { $badgeClass = 's-enr';  $badgeLabel = 'Enrazada'; }
                            ?>
                            <span class="status-badge <?php echo e($badgeClass); ?>"><?php echo e($badgeLabel); ?></span>
                        </td>
                        <td>
                            <div class="actions-cell">
                                <a href="historial_leche.php?vaca_id=<?php echo $row['id']; ?>"
                                   class="btn-icon-view">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6-10-6-10-6z"/><circle cx="12" cy="12" r="3"/></svg>
                                    Historial
                                </a>
                                <form method="POST" action="eliminar.php" style="display:inline"
                                      onsubmit="return confirm('¿Seguro que deseas eliminar esta vaca? Esta acción no se puede deshacer.')">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>">
                                    <button type="submit" class="btn-icon-del">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="3,6 5,6 21,6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>
                                        Eliminar
                                    </button>
                                </form>
                                <!-- ── BOTÓN EDITAR → abre modal ── -->
                                <button type="button" class="btn-icon-edit"
                                    onclick="abrirModalEditar(
                                        <?php echo (int)$row['id']; ?>,
                                        <?php echo htmlspecialchars(json_encode($row['nombre']), ENT_QUOTES, 'UTF-8'); ?>,
                                        <?php echo htmlspecialchars(json_encode($row['raza']), ENT_QUOTES, 'UTF-8'); ?>,
                                        <?php echo (int)$row['edad']; ?>,
                                        <?php echo htmlspecialchars(json_encode($row['estado']), ENT_QUOTES, 'UTF-8'); ?>,
                                        <?php echo htmlspecialchars(json_encode($row['descripcion'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>,
                                        <?php echo htmlspecialchars(json_encode($row['vacunas_info'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>,
                                        <?php echo (int) ($row['partos'] ?? 0); ?>,
                                        <?php echo htmlspecialchars(json_encode($row['foto'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                                    )">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.12 2.12 0 013 3L12 15l-4 1 1-4z"/></svg>
                                    Editar
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
                </table>
                <?php if (!$hayRegistros): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"><ellipse cx="12" cy="8" rx="7" ry="5"/><path d="M5 13c0 3.3 3.1 6 7 6s7-2.7 7-6"/></svg>
                    </div>
                    <div class="empty-state-title">
                        <?php echo ($busqueda !== '') ? 'Sin resultados para "' . htmlspecialchars($busqueda) . '"' : 'No hay vacas registradas'; ?>
                    </div>
                    <div class="empty-state-desc">
                        <?php echo ($busqueda !== '') ? 'Prueba con otro nombre, código o raza.' : 'Registra tu primer animal usando el botón "Registrar vaca" en la parte superior.'; ?>
                    </div>
                    <?php if ($busqueda !== ''): ?>
                    <a href="Registro_Vacas.php" style="font-size:12px;color:var(--accent-text);text-decoration:none;margin-top:4px;">Ver todas las vacas</a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

    </div><!-- fin .main -->

    <!-- ══════════════════════════════════════
         MODAL REGISTRAR (original intacto)
         ══════════════════════════════════════ -->
    <div class="modalOverlay" id="modalOverlay" onclick="cerrarModalFuera(event)">
        <div class="contenedorModal1">
            <div class="modal-header">
                <div class="modal-header-info">
                    <div class="modal-eyebrow">
                        <span class="eyebrow-dot"></span>
                        Nuevo registro
                    </div>
                    <h2>Registrar nueva vaca</h2>
                    <p class="modal-subtitle">Completa los campos para añadir un animal al sistema</p>
                </div>
                <button class="btn-cerrar" onclick="cerrarModal()">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>

            <div class="modal-divider"></div>

            <form action="CrearV.php" method="POST" enctype="multipart/form-data">
                <?php echo csrf_field(); ?>
                <div class="modal-body">
                    <div class="field-row-2">
                        <div class="field">
                            <label class="field-label">Código <span class="field-req">*</span></label>
                            <input type="text" name="codigo" id="codigoInput" placeholder="Ej. 06"
                                   list="codigosSugeridos" autocomplete="off" required>
                            <datalist id="codigosSugeridos"></datalist>
                            <span class="field-error-msg" id="codigoError" hidden>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                                Ya existe una vaca con ese código.
                            </span>
                        </div>
                        <div class="field">
                            <label class="field-label">Edad (años)</label>
                            <input type="number" name="edad" placeholder="Opcional" min="0" max="30">
                        </div>
                    </div>

                    <div class="field-row-2">
                        <div class="field">
                            <label class="field-label">Nombre del animal <span class="field-req">*</span></label>
                            <input type="text" name="nombre" placeholder="Ej. Pepita, La Roja…" required>
                        </div>
                        <div class="field">
                            <label class="field-label">Raza <span class="field-req">*</span></label>
                            <select name="raza" id="razaSelect" required>
                                <option value="" disabled selected>Selecciona una raza…</option>
                                <option value="Holstein">Holstein</option>
                                <option value="Jersey">Jersey</option>
                                <option value="Pardo Suizo">Pardo Suizo</option>
                                <option value="Normando">Normando</option>
                                <option value="Gyr">Gyr</option>
                                <option value="Girolando">Girolando</option>
                                <option value="Simmental">Simmental</option>
                                <option value="Ayrshire">Ayrshire</option>
                                <option value="Criollo (BON/Romosinuano)">Criollo (BON/Romosinuano)</option>
                                <option value="otra">Otra (especificar)</option>
                            </select>
                            <input type="text" name="raza_otra" id="razaOtra" class="raza-otra-input"
                                   placeholder="Especifica la raza" maxlength="50" autocomplete="off" hidden>
                        </div>
                    </div>

                    <div class="field-row-2">
                        <div class="field">
                            <label class="field-label">Foto de la vaca</label>
                            <input type="file" name="foto" accept=".jpg,.jpeg,.png,.webp,.avif,image/*" class="field-file">
                        </div>
                        <div class="field">
                            <label class="field-label">Partos</label>
                            <input type="number" name="partos" placeholder="Ej. 2" min="0" max="30" value="0">
                        </div>
                    </div>

                    <div class="field">
                        <label class="field-label">Descripción / procedencia</label>
                        <textarea name="descripcion" rows="2" placeholder="Ej. Viene de la finca El Rosario, ingresó en 2024, de buen comportamiento..."></textarea>
                    </div>

                    <div class="field">
                        <label class="field-label">Vacunas registradas</label>
                        <textarea name="vacunas_info" rows="2" placeholder="Ej. Brucelosis, aftosa, carbón sintomático..."></textarea>
                    </div>

                    <div class="field">
                        <label class="field-label">Estado productivo <span class="field-req">*</span></label>
                        <input type="hidden" name="estado" id="estadoHidden" required>
                        <div class="status-selector">
                            <div class="status-opt" id="opt-prod" onclick="selEstado('produccion')">
                                <div class="status-opt-check">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><polyline points="20,6 9,17 4,12"/></svg>
                                </div>
                                <div class="status-dot-lg sdl-prod"></div>
                                <div class="status-opt-label">Producción</div>
                                <div class="status-opt-desc">Activa dando leche</div>
                            </div>
                            <div class="status-opt" id="opt-sec" onclick="selEstado('secado')">
                                <div class="status-opt-check">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><polyline points="20,6 9,17 4,12"/></svg>
                                </div>
                                <div class="status-dot-lg sdl-sec"></div>
                                <div class="status-opt-label">Secado</div>
                                <div class="status-opt-desc">Período de descanso</div>
                            </div>
                            <div class="status-opt" id="opt-enr" onclick="selEstado('enrazada')">
                                <div class="status-opt-check">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><polyline points="20,6 9,17 4,12"/></svg>
                                </div>
                                <div class="status-dot-lg sdl-enr"></div>
                                <div class="status-opt-label">Enrazada</div>
                                <div class="status-opt-desc">En gestación activa</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <span class="modal-hint">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        Campos con * son obligatorios
                    </span>
                    <div class="modal-footer-btns">
                        <button type="button" class="btn-modal-cancel" onclick="cerrarModal()">Cancelar</button>
                        <button type="submit" class="btn-modal-submit">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="20,6 9,17 4,12"/></svg>
                            Registrar vaca
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- ══════════════════════════════════════
         MODAL EDITAR VACA
         ══════════════════════════════════════ -->
    <div id="modalEditarOverlay" onclick="cerrarModalEditarFuera(event)">
        <div class="modal-editar-box">

            <div class="modal-header">
                <div class="modal-header-info">
                    <div class="modal-eyebrow">
                        <span class="eyebrow-dot"></span>
                        Editar registro
                    </div>
                    <h2>Editar información</h2>
                    <p class="modal-subtitle">Modifica los datos del animal y guarda los cambios</p>
                </div>
                <button class="btn-cerrar" onclick="cerrarModalEditar()">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>

            <div class="modal-divider"></div>

            <form method="POST" enctype="multipart/form-data">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="editar_id" id="editar_id">

                <div class="modal-body">

                    <!-- Info visual de la vaca seleccionada -->
                    <div class="modal-cow-info">
                        <div class="modal-cow-avatar" id="editar_avatar">P</div>
                        <div class="modal-cow-meta">
                            <div class="modal-cow-name-label">Editando animal</div>
                            <div class="modal-cow-name-val" id="editar_nombre_display">—</div>
                        </div>
                    </div>

                    <div class="field-row-2">
                        <div class="field">
                            <label class="field-label">Nombre</label>
                            <input type="text" name="nombre" id="editar_nombre" required>
                        </div>
                        <div class="field">
                            <label class="field-label">Edad (años)</label>
                            <input type="number" name="edad" id="editar_edad" min="0" max="30">
                        </div>
                    </div>

                    <div class="field">
                        <label class="field-label">Foto actual / nueva foto</label>
                        <div class="edit-photo-wrap">
                            <img src="../Assets/Imagenes/imagvaca.png" alt="Foto actual de la vaca" id="editar_foto_preview" class="edit-photo-preview">
                            <input type="file" name="editar_foto" accept=".jpg,.jpeg,.png,.webp,.avif,image/*">
                        </div>
                    </div>

                    <div class="field">
                        <label class="field-label">Raza</label>
                        <input type="text" name="raza" id="editar_raza" required>
                    </div>

                    <div class="field-row-2">
                        <div class="field">
                            <label class="field-label">Partos</label>
                            <input type="number" name="partos" id="editar_partos" min="0" max="30">
                        </div>
                    </div>

                    <div class="field">
                        <label class="field-label">Descripción / procedencia</label>
                        <textarea name="descripcion" id="editar_descripcion" rows="3"></textarea>
                    </div>

                    <div class="field">
                        <label class="field-label">Vacunas registradas</label>
                        <textarea name="vacunas_info" id="editar_vacunas_info" rows="3"></textarea>
                    </div>

                    <div class="field">
                        <label class="field-label">Estado productivo</label>
                        <select name="estado" id="editar_estado" required>
                            <option value="">Seleccione estado</option>
                            <option value="produccion">En Producción</option>
                            <option value="secado">En Secado</option>
                            <option value="enrazada">Enrazada</option>
                        </select>
                    </div>

                </div>

                <div class="modal-footer">
                    <span class="modal-hint">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        Los cambios se guardan inmediatamente
                    </span>
                    <div class="modal-footer-btns">
                        <button type="button" class="btn-modal-edit-cancel" onclick="cerrarModalEditar()">Cancelar</button>
                        <button type="submit" class="btn-modal-edit-submit">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="20,6 9,17 4,12"/></svg>
                            Guardar cambios
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script src="../JS/registro_vacas.js"></script>

    <div id="toast-container" class="toast-container"></div>

    <!-- ── JS MODAL EDITAR ── -->
    <script>
        // Abrir modal editar con los datos de la fila
        function abrirModalEditar(id, nombre, raza, edad, estado, descripcion, vacunasInfo, partos, foto) {
            // Llenar campos
            document.getElementById('editar_id').value      = id;
            document.getElementById('editar_nombre').value  = nombre;
            document.getElementById('editar_raza').value    = raza;
            document.getElementById('editar_edad').value    = edad;
            document.getElementById('editar_estado').value  = estado;
            document.getElementById('editar_descripcion').value = descripcion;
            document.getElementById('editar_vacunas_info').value = vacunasInfo;
            document.getElementById('editar_partos').value = partos;
            document.getElementById('editar_foto_preview').src = foto ? foto : '../Assets/Imagenes/imagvaca.png';

            // Info visual
            document.getElementById('editar_avatar').textContent       = nombre.charAt(0).toUpperCase();
            document.getElementById('editar_nombre_display').textContent = nombre;

            // Mostrar overlay
            document.getElementById('modalEditarOverlay').classList.add('activo');
            document.body.style.overflow = 'hidden';
        }

        // Cerrar modal editar
        function cerrarModalEditar() {
            document.getElementById('modalEditarOverlay').classList.remove('activo');
            document.body.style.overflow = '';
        }

        // Cerrar al hacer click fuera de la caja
        function cerrarModalEditarFuera(event) {
            if (event.target === document.getElementById('modalEditarOverlay')) {
                cerrarModalEditar();
            }
        }

        // Cerrar con tecla Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                cerrarModalEditar();
            }
        });
    </script>
</body>
</html>


