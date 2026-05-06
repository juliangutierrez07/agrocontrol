<?php
session_start();

if(!isset($_SESSION['id'])){
    header("Location: ../login.php");
    exit();
}

include("../Config/conexion.php");
$con = conexion();

$usuario_id = $_SESSION['id'];

// ── PROCESAR EDICIÓN ──
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['editar_id'])) {
    $id           = $_POST['editar_id'];
    $nombre       = mysqli_real_escape_string($con, $_POST['nombre']);
    $raza         = mysqli_real_escape_string($con, $_POST['raza']);
    $edad         = $_POST['edad'];
    $estado       = mysqli_real_escape_string($con, $_POST['estado']);
    $descripcion  = mysqli_real_escape_string($con, $_POST['descripcion']);
    $vacunas_info = mysqli_real_escape_string($con, $_POST['vacunas_info']);
    $partos       = ($_POST['partos'] ?? '') !== '' ? (int) $_POST['partos'] : 0;
    $fotoUpdate   = '';
    $fotoAnterior = null;
    $fotoNuevaFisica = null;

    $fotoActualQuery = mysqli_query($con, "SELECT foto FROM vacas WHERE id = '$id' AND usuario_id='$usuario_id' LIMIT 1");
    if ($fotoActualQuery && mysqli_num_rows($fotoActualQuery) > 0) {
        $fotoActualRow = mysqli_fetch_assoc($fotoActualQuery);
        $fotoAnterior = $fotoActualRow['foto'] ?? null;
    }

    if (isset($_FILES['editar_foto']) && ($_FILES['editar_foto']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES['editar_foto']['error'] === UPLOAD_ERR_OK) {
            $permitidos = [
                'image/jpeg' => 'jpg',
                'image/png'  => 'png',
                'image/webp' => 'webp',
                'image/avif' => 'avif'
            ];
            $mime = mime_content_type($_FILES['editar_foto']['tmp_name']);

            if (isset($permitidos[$mime])) {
                $directorioFisico = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Assets' . DIRECTORY_SEPARATOR . 'Imagenes' . DIRECTORY_SEPARATOR . 'vacas';
                if (!is_dir($directorioFisico)) {
                    mkdir($directorioFisico, 0777, true);
                }

                $nombreArchivo = 'vaca_' . $usuario_id . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $permitidos[$mime];
                $rutaFisica = $directorioFisico . DIRECTORY_SEPARATOR . $nombreArchivo;

                if (move_uploaded_file($_FILES['editar_foto']['tmp_name'], $rutaFisica)) {
                    $fotoRuta = mysqli_real_escape_string($con, '../Assets/Imagenes/vacas/' . $nombreArchivo);
                    $fotoUpdate = ", foto='$fotoRuta'";
                    $fotoNuevaFisica = $rutaFisica;
                }
            }
        }
    }

    $sql_update = "UPDATE vacas 
                   SET nombre='$nombre', raza='$raza', edad='$edad', estado='$estado',
                       descripcion='$descripcion', vacunas_info='$vacunas_info', partos='$partos'
                       $fotoUpdate
                   WHERE id = '$id' AND usuario_id='$usuario_id'";

    $updateOk = mysqli_query($con, $sql_update);

    if ($updateOk && $fotoNuevaFisica && !empty($fotoAnterior)) {
        $baseDir = realpath(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Assets' . DIRECTORY_SEPARATOR . 'Imagenes' . DIRECTORY_SEPARATOR . 'vacas');
        $fotoAnteriorRel = str_replace(['../Assets/Imagenes/vacas/', '..\\Assets\\Imagenes\\vacas\\'], '', $fotoAnterior);
        $fotoAnteriorPath = $baseDir . DIRECTORY_SEPARATOR . basename($fotoAnteriorRel);
        $fotoAnteriorReal = file_exists($fotoAnteriorPath) ? realpath($fotoAnteriorPath) : false;

        if ($baseDir && $fotoAnteriorReal && strpos($fotoAnteriorReal, $baseDir) === 0 && is_file($fotoAnteriorReal)) {
            @unlink($fotoAnteriorReal);
        }
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
    <link rel="stylesheet" href="../Css/registro_vacas.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet"/>

</head>

<body>
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
                    <a href="#" class="active">
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><ellipse cx="12" cy="8" rx="7" ry="5"/><path d="M5 13c0 3.3 3.1 6 7 6s7-2.7 7-6"/></svg>
                        Gestión de Vacas
                    </a>
                    <a href="produccion_lechera.php">
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M3 9l9-7 9 7v11a1 1 0 01-1 1H4a1 1 0 01-1-1z"/><polyline points="9,22 9,12 15,12 15,22"/></svg>
                        Producción Lechera
                    </a>
                    <a href="potrero.php">
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M17 3a2.83 2.83 0 114 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg>
                        Potreros y Mangas
                    </a>
                    <a href="vacunaciones.html">
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
                <button type="button" class="btn-primary" onclick="abrirModal()">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Registrar vaca
                </button>
            </div>
        </div>
        <div class="stats-grid">
            <?php
$total = mysqli_fetch_row(mysqli_query($con, 
"SELECT COUNT(*) FROM vacas WHERE usuario_id='$usuario_id'"))[0];

$prod = mysqli_fetch_row(mysqli_query($con, 
"SELECT COUNT(*) FROM vacas WHERE estado='produccion' AND usuario_id='$usuario_id'"))[0];

$secado = mysqli_fetch_row(mysqli_query($con, 
"SELECT COUNT(*) FROM vacas WHERE estado='secado' AND usuario_id='$usuario_id'"))[0];

$enrazada = mysqli_fetch_row(mysqli_query($con, 
"SELECT COUNT(*) FROM vacas WHERE estado='enrazada' AND usuario_id='$usuario_id'"))[0];
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
            $condicion = "";

if (isset($_GET['busqueda']) && $_GET['busqueda'] !== "") {
    $busqueda  = $_GET['busqueda'];
    $condicion = "AND (nombre LIKE '%$busqueda%' OR codigo LIKE '%$busqueda%')";
}
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
                $sql = "SELECT * FROM vacas 
        WHERE usuario_id='$usuario_id' $condicion";
                $query = mysqli_query($con, $sql);
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
                    <?php while ($row = mysqli_fetch_array($query)): ?>
                    <tr>
                        <td><span class="code-tag">#<?php echo $row['codigo']; ?></span></td>
                        <td>
                            <div class="cow-cell">
                                <div class="cow-avatar av-<?php echo strtolower(substr($row['nombre'], 0, 1)); ?>">
                                    <?php echo strtoupper(substr($row['nombre'], 0, 1)); ?>
                                </div>
                                <span class="cow-name"><?php echo $row['nombre']; ?></span>
                            </div>
                        </td>
                        <td class="td-raza"><?php echo $row['raza']; ?></td>
                        <td class="td-edad"><?php echo $row['edad'] ? $row['edad'].' años' : '—'; ?></td>
                        <td>
                            <?php
                                $estado = $row['estado'];
                                $badgeClass = 'badge-default';
                                $badgeLabel = $estado;
                                if ($estado === 'produccion')  { $badgeClass = 's-prod'; $badgeLabel = 'Producción'; }
                                if ($estado === 'secado')      { $badgeClass = 's-sec';  $badgeLabel = 'Secado'; }
                                if ($estado === 'enrazada')    { $badgeClass = 's-enr';  $badgeLabel = 'Enrazada'; }
                            ?>
                            <span class="status-badge <?php echo $badgeClass; ?>"><?php echo $badgeLabel; ?></span>
                        </td>
                        <td>
                            <div class="actions-cell">
                                <a href="historial_leche.php?vaca_id=<?php echo $row['id']; ?>"
                                   class="btn-icon-view">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6-10-6-10-6z"/><circle cx="12" cy="12" r="3"/></svg>
                                    Historial
                                </a>
                                <a href="eliminar.php?id=<?php echo $row['id']; ?>"
                                   class="btn-icon-del"
                                   onclick="return confirm('¿Seguro que deseas eliminar a <?php echo $row['nombre']; ?>? Esta acción no se puede deshacer.')">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="3,6 5,6 21,6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>
                                    Eliminar
                                </a>
                                <!-- ── BOTÓN EDITAR → abre modal ── -->
                                <button type="button" class="btn-icon-edit"
                                    onclick="abrirModalEditar(
                                        '<?php echo $row['id']; ?>',
                                        '<?php echo addslashes($row['nombre']); ?>',
                                        '<?php echo addslashes($row['raza']); ?>',
                                        '<?php echo $row['edad']; ?>',
                                        '<?php echo $row['estado']; ?>',
                                        '<?php echo addslashes($row['descripcion'] ?? ''); ?>',
                                        '<?php echo addslashes($row['vacunas_info'] ?? ''); ?>',
                                        '<?php echo (int) ($row['partos'] ?? 0); ?>',
                                        '<?php echo addslashes($row['foto'] ?? ''); ?>'
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
                <div class="modal-body">
                    <div class="field-row-2">
                        <div class="field">
                            <label class="field-label">Código <span class="field-req">*</span></label>
                            <input type="text" name="codigo" placeholder="Ej. 06" required>
                        </div>
                        <div class="field">
                            <label class="field-label">Edad (años)</label>
                            <input type="number" name="edad" placeholder="Opcional" min="0" max="30">
                        </div>
                    </div>

                    <div class="field">
                        <label class="field-label">Nombre del animal <span class="field-req">*</span></label>
                        <input type="text" name="nombre" placeholder="Ej. Pepita, La Roja…" required>
                    </div>

                    <div class="field">
                        <label class="field-label">Raza <span class="field-req">*</span></label>
                        <input type="text" name="raza" placeholder="Holstein, Jersey, Gyrolanda…" required>
                    </div>

                    <div class="field-row-2">
                        <div class="field">
                            <label class="field-label">Foto de la vaca</label>
                            <input type="file" name="foto" accept=".jpg,.jpeg,.png,.webp,.avif,image/*">
                        </div>
                        <div class="field">
                            <label class="field-label">Partos</label>
                            <input type="number" name="partos" placeholder="Ej. 2" min="0" max="30" value="0">
                        </div>
                    </div>

                    <div class="field">
                        <label class="field-label">Descripción / procedencia</label>
                        <textarea name="descripcion" rows="3" placeholder="Ej. Viene de la finca El Rosario, ingresó en 2024, de buen comportamiento..."></textarea>
                    </div>

                    <div class="field">
                        <label class="field-label">Vacunas registradas</label>
                        <textarea name="vacunas_info" rows="3" placeholder="Ej. Brucelosis, aftosa, carbón sintomático..."></textarea>
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


