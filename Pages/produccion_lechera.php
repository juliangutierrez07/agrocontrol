<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgroControl | Producción Lechera</title>
    <link rel="stylesheet" href="../Css/produccion_lechera.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet"/>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>

<?php
session_start();
include("../Config/conexion.php");
$con = conexion();

$usuario_id = $_SESSION['id'];
$toastData = null;

if (isset($_SESSION['toast']) && is_array($_SESSION['toast'])) {
    $toastData = $_SESSION['toast'];
    unset($_SESSION['toast']);
}

date_default_timezone_set('America/Bogota');

$vacas = mysqli_query($con, 
"SELECT * FROM vacas 
 WHERE estado='produccion' 
 AND usuario_id='$usuario_id'");

/* ================= FILTRO ================= */
$condicionFecha = "";
$desde = "";
$hasta = "";

if (isset($_GET['desde']) && isset($_GET['hasta']) && $_GET['desde'] != "" && $_GET['hasta'] != "") {
    $desde = $_GET['desde'];
    $hasta = $_GET['hasta'];
    $condicionFecha = "WHERE rl.fecha BETWEEN '$desde' AND '$hasta'";
}

/* ================= STATS ================= */
$totalLitros    = mysqli_fetch_row(mysqli_query($con, 
"SELECT COALESCE(SUM(litros),0) 
 FROM registroleche 
 WHERE usuario_id='$usuario_id'"))[0];

$totalRegistros = mysqli_fetch_row(mysqli_query($con, 
"SELECT COUNT(*) 
 FROM registroleche 
 WHERE usuario_id='$usuario_id'"))[0];

$hoy            = date('Y-m-d');

$litrosHoy      = mysqli_fetch_row(mysqli_query($con, 
"SELECT COALESCE(SUM(litros),0) 
 FROM registroleche 
 WHERE usuario_id='$usuario_id' 
 AND DATE(fecha)='$hoy'"))[0];

$totalVacas     = mysqli_fetch_row(mysqli_query($con, 
"SELECT COUNT(*) 
 FROM vacas 
 WHERE estado='produccion' 
 AND usuario_id='$usuario_id'"))[0];

/* ================= CARGAR DATOS PARA EDITAR ================= */
/* Si viene ?editar=ID abrimos el modal con los datos precargados */
$editRow = null;
if (isset($_GET['editar']) && is_numeric($_GET['editar'])) {
    $editId  = (int)$_GET['editar'];
    $editRes = mysqli_query($con, "
        SELECT rl.id, rl.vaca_id, rl.fecha, rl.litros, v.nombre
        FROM registroleche rl
        INNER JOIN vacas v ON rl.vaca_id = v.id
        WHERE rl.id = $editId
        LIMIT 1
    ");
    $editRow = mysqli_fetch_assoc($editRes);
}
?>

<!-- TOAST -->
<div class="toast<?php echo ($toastData && ($toastData['type'] ?? '') === 'alert') ? ' toast-alert' : ''; ?>" id="toast">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="20,6 9,17 4,12"/></svg>
    <span id="toastMsg">Registro guardado correctamente</span>
</div>

<!-- SIDEBAR -->
<aside>
    <div class="sidebar">
        <div class="logo">
            <div class="logo-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="#061006" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 2C6 2 3 7 3 12s3 10 9 10 9-5 9-10S18 2 12 2"/>
                    <path d="M8 14s1.5 2 4 2 4-2 4-2"/>
                    <circle cx="9" cy="9" r=".8" fill="#061006"/>
                    <circle cx="15" cy="9" r=".8" fill="#061006"/>
                </svg>
            </div>
            <div class="logo-text">
                <div class="logo-name">AGRO<span>CONTROL</span></div>
                <div class="logo-sub">Gestión Ganadera</div>
            </div>
        </div>

        <div class="nav-section">
            <div class="menu-label">Principal</div>
            <div class="menu">
                <a href="Dashboard.php">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/></svg>
                    Dashboard
                </a>
                <a href="Registro_Vacas.php">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><ellipse cx="12" cy="8" rx="7" ry="5"/><path d="M5 13c0 3.3 3.1 6 7 6s7-2.7 7-6"/></svg>
                    Gestión de Vacas
                </a>
                <a href="produccion_lechera.php" class="active">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M3 9l9-7 9 7v11a1 1 0 01-1 1H4a1 1 0 01-1-1z"/><polyline points="9,22 9,12 15,12 15,22"/></svg>
                    Producción Lechera
                </a>
                <a href="potrero.php">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M17 3a2.83 2.83 0 114 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg>
                    Potreros y Mangas
                </a>
                    <a href="vacunaciones.html" >
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M19 3l2 2-7 7"/><path d="M17 5l2 2"/><path d="M3 21l9-9"/><path d="M14.5 5.5l-11 11 4 4 11-11z"/></svg>
                        Vacunaciones
                    </a>
            </div>
        </div>

        <div class="nav-divider"></div>

        <div class="nav-section">
            <div class="menu-label">Sistema</div>
            <div class="menu">
                <a href="logout.php" class="logout-link">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16,17 21,12 16,7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                    Cerrar sesión
                </a>
            </div>
        </div>
    </div>
</aside>

<!-- MAIN -->
<div class="main">

    <!-- TOPBAR -->
    <div class="topbar">
        <div class="topbar-left">
            <div class="topbar-breadcrumb">Principal / Producción</div>
            <div class="topbar-title">Producción Lechera</div>
        </div>
        <div class="topbar-right">
            <a href="historial_quincenas_leche.php" class="btn-secondary-link">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 1v22"/><path d="M17 5H9.5a3.5 3.5 0 000 7H14.5a3.5 3.5 0 010 7H6"/></svg>
                Historial quincenas
            </a>
            <form method="GET" class="filtro-form">
                <div class="filtro-wrap">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    <input type="date" name="desde" value="<?php echo $desde; ?>" title="Desde">
                    <span class="filtro-sep">—</span>
                    <input type="date" name="hasta" value="<?php echo $hasta; ?>" title="Hasta">
                    <button type="submit" class="btn-filtrar">Filtrar</button>
                    <a href="produccion_lechera.php" class="btn-reset">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </a>
                </div>
            </form>
            <button class="theme-toggle" onclick="toggleTheme()" title="Cambiar tema">
                <svg class="icon-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/></svg>
                <svg class="icon-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
            </button>
            <button class="btn-primary" onclick="abrirModal()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Registrar litros
            </button>
        </div>
    </div>

    <!-- STATS -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-top">
                <div class="stat-label">Total litros</div>
                <div class="stat-icon si-green">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 2C6 2 3 9 3 14a9 9 0 0018 0c0-5-3-12-9-12z"/></svg>
                </div>
            </div>
            <div class="stat-value"><?php echo number_format($totalLitros, 0); ?></div>
            <div class="stat-meta">litros acumulados</div>
        </div>
        <div class="stat-card">
            <div class="stat-top">
                <div class="stat-label">Litros hoy</div>
                <div class="stat-icon si-blue">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/></svg>
                </div>
            </div>
            <div class="stat-value"><?php echo number_format($litrosHoy, 0); ?></div>
            <div class="stat-meta"><?php echo date('d/m/Y'); ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-top">
                <div class="stat-label">Registros</div>
                <div class="stat-icon si-amber">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14,2 14,8 20,8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                </div>
            </div>
            <div class="stat-value"><?php echo $totalRegistros; ?></div>
            <div class="stat-meta">entradas totales</div>
        </div>
        <div class="stat-card">
            <div class="stat-top">
                <div class="stat-label">Vacas activas</div>
                <div class="stat-icon si-green">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><ellipse cx="12" cy="8" rx="7" ry="5"/><path d="M5 13c0 3.3 3.1 6 7 6s7-2.7 7-6"/></svg>
                </div>
            </div>
            <div class="stat-value"><?php echo $totalVacas; ?></div>
            <div class="stat-meta">en producción</div>
        </div>
    </div>

    <!-- CUERPO: gráfica + tabla -->
    <div class="body-grid">

        <!-- GRÁFICA -->
        <div class="grafica-card">
            <div class="card-header">
                <span class="card-title">Producción por vaca</span>
                <?php if ($condicionFecha): ?>
                    <span class="filtro-badge">
                        <?php echo date('d/m', strtotime($desde)); ?> — <?php echo date('d/m', strtotime($hasta)); ?>
                    </span>
                <?php endif; ?>
            </div>
            <div class="grafica-wrap">
                <canvas id="graficaLeche"></canvas>
            </div>
        </div>

        <!-- TABLA -->
        <div class="contenedorTabla">
            <div class="table-toolbar">
                <div class="tt-left">
                    <span class="table-heading">Litros registrados</span>
                    <?php if ($condicionFecha): ?>
                        <span class="search-badge">
                            <?php echo htmlspecialchars($desde); ?> — <?php echo htmlspecialchars($hasta); ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>

            <?php
            // Filtrar siempre por usuario_id para que cada usuario vea solo sus registros
            $filtroUsuario = "WHERE rl.usuario_id = '$usuario_id'";
            $filtroFecha   = $condicionFecha ? " AND rl.fecha BETWEEN '$desde' AND '$hasta'" : "";

            $query = mysqli_query($con, "
                SELECT rl.id, rl.vaca_id, v.nombre AS vaca, rl.fecha, rl.litros
                FROM registroleche rl
                INNER JOIN vacas v ON rl.vaca_id = v.id
                $filtroUsuario $filtroFecha
                ORDER BY rl.fecha DESC
            ");
            ?>

            <table>
                <thead>
                    <tr>
                        <th>Vaca</th>
                        <th>Fecha</th>
                        <th>Litros</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($query)): ?>
                    <tr>
                        <td>
                            <div class="cow-cell">
                                <div class="cow-avatar">
                                    <?php echo strtoupper(substr($row['vaca'], 0, 1)); ?>
                                </div>
                                <span class="cow-name"><?php echo $row['vaca']; ?></span>
                            </div>
                        </td>
                        <td>
                            <span class="fecha-tag"><?php echo date('d/m/Y', strtotime($row['fecha'])); ?></span>
                        </td>
                        <td>
                            <span class="litros-val">
                                <?php echo rtrim(rtrim($row['litros'], '0'), '.'); ?>
                                <span class="litros-unit">L</span>
                            </span>
                        </td>
                        <td>
                            <div class="actions-cell">
                                <a href="historial_leche.php?vaca_id=<?php echo $row['vaca_id']; ?>"
                                   class="btn-icon-view">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6-10-6-10-6z"/><circle cx="12" cy="12" r="3"/></svg>
                                    Historial
                                </a>
                                <a href="eliminarl.php?id=<?php echo $row['id']; ?>"
                                   class="btn-icon-del"
                                   onclick="return confirm('¿Eliminar este registro de <?php echo $row['vaca']; ?>?')">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="3,6 5,6 21,6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>
                                    Eliminar
                                </a>
                                <!-- ✅ Ahora abre modal en lugar de ir a editar.php -->
                                <button type="button" class="btn-icon-edit"
                                    onclick="abrirModalEditar(
                                        <?php echo $row['id']; ?>,
                                        '<?php echo addslashes($row['vaca']); ?>',
                                        '<?php echo $row['fecha']; ?>',
                                        '<?php echo $row['litros']; ?>'
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
        </div>

    </div><!-- fin body-grid -->

</div><!-- fin .main -->

<!-- ══════════════════════════════════════
     MODAL REGISTRAR LITROS
     ══════════════════════════════════════ -->
<div class="modalOverlay" id="modalOverlay" onclick="cerrarModalFuera(event)">
    <div class="contenedorModal1">
        <div class="modal-header">
            <div class="modal-header-info">
                <div class="modal-eyebrow">
                    <span class="eyebrow-dot"></span>
                    Nuevo registro
                </div>
                <h2>Registrar producción</h2>
                <p class="modal-subtitle">Ingresa los litros producidos por la vaca</p>
            </div>
            <button class="btn-cerrar" onclick="cerrarModal()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        <div class="modal-divider"></div>

        <form action="CrearL.php" method="POST">
            <div class="modal-body">
                <div class="field">
                    <label class="field-label">
                        Vaca <span class="field-req">*</span>
                        <span class="field-hint">Solo vacas en producción</span>
                    </label>
                    <select name="vaca_id" required>
                        <option value="">Seleccione una vaca…</option>
                        <?php
                        $vacas2 = mysqli_query($con, "SELECT * FROM vacas WHERE estado='produccion' AND usuario_id='$usuario_id' ORDER BY nombre ASC");
                        while ($vac = mysqli_fetch_assoc($vacas2)):
                        ?>
                        <option value="<?php echo $vac['id']; ?>">
                            <?php echo $vac['nombre']; ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                    <?php
                    $countProd = mysqli_fetch_row(mysqli_query($con, "SELECT COUNT(*) FROM vacas WHERE estado='produccion' AND usuario_id='$usuario_id'"))[0];
                    if ($countProd == 0):
                    ?>
                    <div class="field-warning">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        No hay vacas en producción. <a href="Registro_Vacas.php">Gestionar vacas →</a>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="field-row-2">
                    <div class="field">
                        <label class="field-label">Fecha <span class="field-req">*</span></label>
                        <input type="date" name="fecha" required value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="field">
                        <label class="field-label">Litros <span class="field-req">*</span></label>
                        <input type="number" name="litros" placeholder="Ej. 12.5" step="0.1" min="0" required>
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
                    <button type="submit" class="btn-modal-submit" <?php echo $countProd == 0 ? 'disabled style="opacity:.5;cursor:not-allowed;"' : ''; ?>>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="20,6 9,17 4,12"/></svg>
                        Registrar
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- ══════════════════════════════════════
     MODAL EDITAR REGISTRO
     ══════════════════════════════════════ -->
<div class="modalOverlay" id="modalEditarOverlay" onclick="cerrarModalEditarFuera(event)">
    <div class="contenedorModal1">
        <div class="modal-header">
            <div class="modal-header-info">
                <div class="modal-eyebrow modal-eyebrow-edit">
                    <span class="eyebrow-dot-edit"></span>
                    Editar registro
                </div>
                <h2>Editar producción</h2>
                <p class="modal-subtitle" id="editSubtitle">Modifica los datos del registro seleccionado</p>
            </div>
            <button class="btn-cerrar" onclick="cerrarModalEditar()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        <div class="modal-divider"></div>

        <!-- action apunta a tu archivo de actualización existente -->
        <form action="ActualizarL.php" method="POST">
            <input type="hidden" name="id" id="editId">

            <div class="modal-body">
                <div class="field">
                    <label class="field-label">
                        Vaca <span class="field-req">*</span>
                        <span class="field-hint">Solo vacas en producción</span>
                    </label>
                    <select name="vaca_id" id="editVacaId" required>
                        <option value="">Seleccione una vaca…</option>
                        <?php
                        $vacas3 = mysqli_query($con, "SELECT * FROM vacas WHERE estado='produccion' AND usuario_id='$usuario_id' ORDER BY nombre ASC");
                        while ($vac3 = mysqli_fetch_assoc($vacas3)):
                        ?>
                        <option value="<?php echo $vac3['id']; ?>">
                            <?php echo $vac3['nombre']; ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="field-row-2">
                    <div class="field">
                        <label class="field-label">Fecha <span class="field-req">*</span></label>
                        <input type="date" name="fecha" id="editFecha" required>
                    </div>
                    <div class="field">
                        <label class="field-label">Litros <span class="field-req">*</span></label>
                        <input type="number" name="litros" id="editLitros" placeholder="Ej. 12.5" step="0.1" min="0" required>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <span class="modal-hint">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    Campos con * son obligatorios
                </span>
                <div class="modal-footer-btns">
                    <button type="button" class="btn-modal-cancel" onclick="cerrarModalEditar()">Cancelar</button>
                    <button type="submit" class="btn-modal-submit btn-modal-edit">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><polyline points="17,21 17,13 7,13 7,21"/><polyline points="7,3 7,8 15,8"/></svg>
                        Guardar cambios
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php
/* ================= DATOS GRÁFICA ================= */
$filtroGrafica = "WHERE rl.usuario_id = '$usuario_id'";
if ($condicionFecha) $filtroGrafica .= " AND rl.fecha BETWEEN '$desde' AND '$hasta'";

$grafica = mysqli_query($con, "
    SELECT v.nombre, SUM(rl.litros) as total
    FROM registroleche rl
    INNER JOIN vacas v ON rl.vaca_id = v.id
    $filtroGrafica
    GROUP BY v.nombre
    ORDER BY total DESC
");

$gNombres = [];
$gLitros  = [];
while ($g = mysqli_fetch_assoc($grafica)) {
    $gNombres[] = $g['nombre'];
    $gLitros[]  = (float)$g['total'];
}
?>

<script>
(function(){
    var isDark = !document.documentElement.classList.contains('light');
    var colores = ['#5BAF59','#5190C8','#C4883A','#C9524F','#A0D49E','#94C0E8','#E8BC7A','#EFA0A0'];
    var textColor = isDark ? '#7A9474' : '#6A8E65';

    var ctx = document.getElementById('graficaLeche');
    if (!ctx) return;

    var chart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode($gNombres); ?>,
            datasets: [{
                data: <?php echo json_encode($gLitros); ?>,
                backgroundColor: colores.slice(0, <?php echo count($gNombres); ?>),
                borderWidth: 2,
                borderColor: isDark ? '#1A2119' : '#FFFFFF',
                hoverOffset: 6
            }]
        },
        options: {
            cutout: '68%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        color: textColor,
                        font: { family: "'Plus Jakarta Sans', sans-serif", size: 11, weight: '500' },
                        padding: 14,
                        boxWidth: 10,
                        boxHeight: 10,
                        borderRadius: 3,
                        useBorderRadius: true
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(ctx){ return ' ' + ctx.label + ': ' + ctx.parsed + ' L'; }
                    },
                    bodyFont: { family: "'Plus Jakarta Sans', sans-serif", size: 12 },
                    padding: 10,
                    cornerRadius: 8
                }
            }
        }
    });

    window._updateChartTheme = function(light){
        var tc = light ? '#6A8E65' : '#7A9474';
        var bc = light ? '#FFFFFF'  : '#1A2119';
        chart.data.datasets[0].borderColor = bc;
        chart.options.plugins.legend.labels.color = tc;
        chart.update();
    };
})();
</script>

<script>
/* ── MODAL REGISTRAR ── */
function abrirModal(){
    document.getElementById('modalOverlay').classList.add('activo');
}
function cerrarModal(){
    document.getElementById('modalOverlay').classList.remove('activo');
}
function cerrarModalFuera(e){
    if (e.target === document.getElementById('modalOverlay')) cerrarModal();
}

/* ── MODAL EDITAR ── */
function abrirModalEditar(id, vaca, fecha, litros) {
    /* Rellenar campos con los datos de la fila */
    document.getElementById('editId').value     = id;
    document.getElementById('editFecha').value  = fecha;
    document.getElementById('editLitros').value = litros;
    document.getElementById('editSubtitle').textContent = 'Editando registro de ' + vaca;

    /* Seleccionar la vaca correcta en el <select> */
    var sel = document.getElementById('editVacaId');
    /* Buscamos por texto porque no tenemos vaca_id directo en la fila visible */
    for (var i = 0; i < sel.options.length; i++) {
        if (sel.options[i].text.trim() === vaca.trim()) {
            sel.selectedIndex = i;
            break;
        }
    }

    document.getElementById('modalEditarOverlay').classList.add('activo');
}
function cerrarModalEditar(){
    document.getElementById('modalEditarOverlay').classList.remove('activo');
}
function cerrarModalEditarFuera(e){
    if (e.target === document.getElementById('modalEditarOverlay')) cerrarModalEditar();
}

/* ── TOAST ── */
function mostrarToast(msg){
    var t = document.getElementById('toast');
    if (msg) document.getElementById('toastMsg').textContent = msg;
    t.classList.add('show');
    setTimeout(function(){ t.classList.remove('show'); }, 3000);
}
<?php if ($toastData): ?>
mostrarToast(<?php echo json_encode($toastData['message']); ?>);
<?php endif; ?>

/* ── THEME TOGGLE ── */
function toggleTheme(){
    var html = document.documentElement;
    html.classList.toggle('light');
    var isLight = html.classList.contains('light');
    try { localStorage.setItem('acTheme', isLight ? 'light' : 'dark'); } catch(e){}
    if (window._updateChartTheme) window._updateChartTheme(isLight);
}
(function(){
    try {
        if (localStorage.getItem('acTheme') === 'light'){
            document.documentElement.classList.add('light');
        }
    } catch(e){}
})();
</script>

</body>
</html>
