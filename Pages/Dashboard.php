<?php
require_once("../Config/conexion.php");
require_login();
$con = conexion();

date_default_timezone_set('America/Bogota');
$usuario_id = current_user_id();

// ── STATS PRINCIPALES ──
$totalVacas    = (int)(db_value($con, "SELECT COUNT(*) FROM vacas WHERE usuario_id = ?", "i", [$usuario_id]) ?? 0);
$produccionHoy = (float)(db_value($con, "SELECT COALESCE(SUM(litros),0) FROM registroleche WHERE DATE(fecha)=CURDATE() AND usuario_id=?", "i", [$usuario_id]) ?? 0);
$vacasPrenadas = (int)(db_value($con, "SELECT COUNT(*) FROM vacas WHERE estado='enrazada' AND usuario_id=?", "i", [$usuario_id]) ?? 0);
$vacasSecado   = (int)(db_value($con, "SELECT COUNT(*) FROM vacas WHERE estado='secado' AND usuario_id=?", "i", [$usuario_id]) ?? 0);
$totalProd     = (int)(db_value($con, "SELECT COUNT(*) FROM vacas WHERE estado='produccion' AND usuario_id=?", "i", [$usuario_id]) ?? 0);
$litrosSemana  = (float)(db_value($con, "SELECT COALESCE(SUM(litros),0) FROM registroleche WHERE fecha>=DATE_SUB(CURDATE(),INTERVAL 7 DAY) AND usuario_id=?", "i", [$usuario_id]) ?? 0);
$litrosMes     = (float)(db_value($con, "SELECT COALESCE(SUM(litros),0) FROM registroleche WHERE MONTH(fecha)=MONTH(CURDATE()) AND YEAR(fecha)=YEAR(CURDATE()) AND usuario_id=?", "i", [$usuario_id]) ?? 0);

// ── TABLA PRODUCCIÓN RECIENTE ──
$queryTabla = db_result($con,
    "SELECT rl.fecha, rl.litros, v.nombre FROM registroleche rl
     INNER JOIN vacas v ON rl.vaca_id=v.id
     WHERE rl.usuario_id=? ORDER BY rl.fecha DESC LIMIT 6",
    "i", [$usuario_id]);

// ── GRÁFICO BARRAS ──
$queryGrafico = db_result($con,
    "SELECT v.nombre, SUM(rl.litros) as total FROM registroleche rl
     INNER JOIN vacas v ON rl.vaca_id=v.id
     WHERE rl.usuario_id=? GROUP BY rl.vaca_id, v.nombre",
    "i", [$usuario_id]);
$nombres = []; $litros = [];
while ($row = mysqli_fetch_assoc($queryGrafico)) {
    $nombres[] = $row['nombre'];
    $litros[]  = (float)$row['total'];
}

// ── GRÁFICO LÍNEA (últimos 30 días para tener datos suficientes) ──
$queryLinea = db_result($con,
    "SELECT DATE_FORMAT(fecha,'%d/%m') AS dia, COALESCE(SUM(litros),0) AS total
     FROM registroleche WHERE usuario_id=? AND fecha>=DATE_SUB(CURDATE(),INTERVAL 29 DAY)
     GROUP BY fecha ORDER BY fecha ASC",
    "i", [$usuario_id]);
$lineaDias = []; $lineaLitros = [];
while ($r = mysqli_fetch_assoc($queryLinea)) {
    $lineaDias[]   = $r['dia'];
    $lineaLitros[] = (float)$r['total'];
}

// ── SPARKLINE PRODUCCIÓN (últimos 7 días) ──
$querySparkProduccion = db_result($con,
    "SELECT COALESCE(SUM(litros),0) AS total
     FROM registroleche WHERE usuario_id=? AND fecha>=DATE_SUB(CURDATE(),INTERVAL 6 DAY)
     GROUP BY fecha ORDER BY fecha ASC",
    "i", [$usuario_id]);
$sparkProduccion = [];
while ($r = mysqli_fetch_assoc($querySparkProduccion)) {
    $sparkProduccion[] = (float)$r['total'];
}

// ── PRÓXIMAS VACUNAS ──
$queryVacunas = db_result($con,
    "SELECT va.fecha_programada, v.nombre AS nombre_vaca, tv.nombre AS tipo_vacuna,
            DATEDIFF(va.fecha_programada,CURDATE()) AS dias_restantes
     FROM vacunaciones va
     INNER JOIN vacas v ON va.vaca_id=v.id
     INNER JOIN tipos_vacuna tv ON va.tipo_vacuna_id=tv.id
     WHERE v.usuario_id=? AND va.estado='pendiente' AND va.fecha_programada>=CURDATE()
     ORDER BY va.fecha_programada ASC LIMIT 5",
    "i", [$usuario_id]);
$proximasVacunas = [];
while ($row = mysqli_fetch_assoc($queryVacunas)) { $proximasVacunas[] = $row; }

// ── ALERTAS DE PRODUCCIÓN (caída ≥10% del promedio diario reciente vs. base) ──
// Reciente = últimos 7 días. Base = los 30 días anteriores a esos 7 (ventanas sin solape).
// Solo se evalúan vacas en producción con al menos 3 registros en cada ventana,
// para no marcar caídas por falta de datos.
$queryAlertas = db_result($con,
    "SELECT v.nombre, r.avg_reciente, r.avg_base
     FROM vacas v
     INNER JOIN (
         SELECT vaca_id,
                AVG(CASE WHEN fecha >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) THEN litros END) AS avg_reciente,
                COUNT(CASE WHEN fecha >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) THEN 1 END) AS n_reciente,
                AVG(CASE WHEN fecha BETWEEN DATE_SUB(CURDATE(), INTERVAL 36 DAY) AND DATE_SUB(CURDATE(), INTERVAL 7 DAY) THEN litros END) AS avg_base,
                COUNT(CASE WHEN fecha BETWEEN DATE_SUB(CURDATE(), INTERVAL 36 DAY) AND DATE_SUB(CURDATE(), INTERVAL 7 DAY) THEN 1 END) AS n_base
         FROM registroleche
         WHERE usuario_id = ? AND fecha >= DATE_SUB(CURDATE(), INTERVAL 36 DAY)
         GROUP BY vaca_id
     ) r ON r.vaca_id = v.id
     WHERE v.usuario_id = ? AND v.estado = 'produccion'
       AND r.n_reciente >= 3 AND r.n_base >= 3 AND r.avg_base > 0
       AND (r.avg_base - r.avg_reciente) / r.avg_base >= 0.10
     ORDER BY (r.avg_base - r.avg_reciente) / r.avg_base DESC
     LIMIT 6",
    "ii", [$usuario_id, $usuario_id]);
$alertasProduccion = [];
while ($row = mysqli_fetch_assoc($queryAlertas)) {
    $row['avg_reciente'] = (float)$row['avg_reciente'];
    $row['avg_base']     = (float)$row['avg_base'];
    $row['caida_pct']    = (int)round((($row['avg_base'] - $row['avg_reciente']) / $row['avg_base']) * 100);
    $alertasProduccion[] = $row;
}

$userName = e($_SESSION['nombre'] ?? 'Usuario');
$userRole = e($_SESSION['rol'] ?? 'usuario');
$userInitial = strtoupper(mb_substr($_SESSION['nombre'] ?? 'U', 0, 1));
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>AgroControl | Dashboard</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../Css/layout-base.css">
<link rel="stylesheet" href="../Css/Dashboard.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<!-- ═══════════════════════ SIDEBAR ═══════════════════════ -->
<?php include 'sidebar.php'; ?>

<!-- ═══════════════════════ MAIN ═══════════════════════ -->
<div class="main" id="main">

    <!-- TOPBAR -->
    <header class="topbar">

        <!-- Izquierda: hamburguesa + título + breadcrumb -->
        <div class="topbar-left">
            <button class="menu-toggle" id="menuToggle" aria-label="Abrir menú">
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
            </button>

            <div class="topbar-text">
                <nav class="tb-breadcrumb" aria-label="Ruta de navegación">
                    <span class="tb-bc-item">Sistema</span>
                    <svg class="tb-bc-sep" viewBox="0 0 6 10" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"><polyline points="1,1 5,5 1,9"/></svg>
                    <span class="tb-bc-item tb-bc-active">Dashboard</span>
                </nav>
                <h1 class="tb-title">
                    Dashboard
                    <span class="tb-title-sub">Bienvenido, <?php echo $userName; ?></span>
                </h1>
            </div>
        </div>

        <!-- Derecha: buscador + acciones + avatar -->
        <div class="topbar-right">

            <!-- Buscador -->
            <div class="tb-search-wrap">
                <label class="tb-search" aria-label="Buscar en el sistema">
                    <svg viewBox="0 0 18 18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
                        <circle cx="7.5" cy="7.5" r="5"/>
                        <line x1="11.5" y1="11.5" x2="16" y2="16"/>
                    </svg>
                    <input type="text" id="tbSearchInput" placeholder="Buscar..." autocomplete="off"
                           role="combobox" aria-expanded="false" aria-controls="tbSearchResults" aria-autocomplete="list">
                </label>
                <div class="tb-search-results" id="tbSearchResults" role="listbox" hidden></div>
            </div>

            <!-- Pill estado finca activa -->
            <div class="tb-status-pill" aria-label="Estado del sistema">
                <span class="tb-status-dot" aria-hidden="true"></span>
                <?php echo e($userName); ?> · Activo <?php echo date('d/m · H:i'); ?>
            </div>
            <!-- TODO: implementar usuarios.ultimo_acceso para mostrar "Sincronizado hace X min" -->

            <!-- Separador visual -->
            <div class="tb-sep" aria-hidden="true"></div>

            <!-- Tema -->
            <button class="tb-icon-btn" onclick="toggleTheme()" title="Cambiar tema" id="themeToggle" aria-label="Cambiar modo de color">
                <svg class="icon-moon" viewBox="0 0 18 18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
                    <path d="M15.5 9.9A6.5 6.5 0 118.1 2.5 5 5 0 0015.5 9.9z"/>
                </svg>
                <svg class="icon-sun" viewBox="0 0 18 18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
                    <circle cx="9" cy="9" r="3.5"/>
                    <line x1="9" y1="1" x2="9" y2="2.5"/><line x1="9" y1="15.5" x2="9" y2="17"/>
                    <line x1="3" y1="3" x2="4.1" y2="4.1"/><line x1="13.9" y1="13.9" x2="15" y2="15"/>
                    <line x1="1" y1="9" x2="2.5" y2="9"/><line x1="15.5" y1="9" x2="17" y2="9"/>
                    <line x1="3" y1="15" x2="4.1" y2="13.9"/><line x1="13.9" y1="4.1" x2="15" y2="3"/>
                </svg>
            </button>

            <!-- Notificaciones -->
            <?php $notifTotal = count($proximasVacunas) + count($alertasProduccion); ?>
            <div class="tb-notify-wrap">
                <button class="tb-icon-btn tb-notify" type="button" id="notifToggle"
                        aria-label="Notificaciones" aria-haspopup="true" aria-expanded="false">
                    <svg viewBox="0 0 18 18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
                        <path d="M9 2a2.7 2.7 0 00-2.7 2.7v1A4 4 0 014 9.3v1.2l-1.2 1.5h12.4L14 10.5V9.3a4 4 0 01-2.3-3.6V4.7A2.7 2.7 0 009 2z"/>
                        <path d="M7 14a2 2 0 004 0"/>
                    </svg>
                    <?php if ($notifTotal > 0): ?>
                    <span class="tb-notify-badge" aria-hidden="true"><?php echo $notifTotal > 9 ? '9+' : $notifTotal; ?></span>
                    <?php endif; ?>
                </button>

                <div class="tb-notify-panel" id="notifPanel" role="menu" aria-label="Notificaciones" hidden>
                    <div class="tb-notify-head">
                        <span class="tb-notify-title">Notificaciones</span>
                        <span class="tb-notify-count"><?php echo $notifTotal; ?></span>
                    </div>
                    <div class="tb-notify-body">
                        <?php if ($notifTotal === 0): ?>
                            <div class="tb-notify-empty">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                                    <path d="M13.7 21a2 2 0 01-3.4 0"/>
                                </svg>
                                <span>Todo al día. Sin alertas.</span>
                            </div>
                        <?php else: ?>
                            <?php foreach ($alertasProduccion as $a): ?>
                            <a class="tb-notify-item" href="produccion_lechera.php" role="menuitem">
                                <span class="tb-ni-icon tb-ni-warn" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M10.3 3.9L1.8 18a1.5 1.5 0 001.3 2.3h17.8A1.5 1.5 0 0022.2 18L13.7 3.9a1.5 1.5 0 00-2.6 0z"/>
                                        <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
                                    </svg>
                                </span>
                                <span class="tb-ni-copy">
                                    <span class="tb-ni-title">Caída de producción · <?php echo e($a['nombre']); ?></span>
                                    <span class="tb-ni-sub">Bajó <?php echo $a['caida_pct']; ?>% respecto a su promedio</span>
                                </span>
                                <span class="tb-ni-tag tb-ni-tag-warn">-<?php echo $a['caida_pct']; ?>%</span>
                            </a>
                            <?php endforeach; ?>
                            <?php foreach ($proximasVacunas as $v):
                                $d = (int)$v['dias_restantes'];
                                if ($d === 0)     { $txt = 'Hoy';    $tagcls = 'tb-ni-tag-red'; }
                                elseif ($d === 1) { $txt = 'Mañana'; $tagcls = 'tb-ni-tag-amber'; }
                                else              { $txt = "{$d}d";  $tagcls = 'tb-ni-tag-green'; }
                            ?>
                            <a class="tb-notify-item" href="vacunaciones.html" role="menuitem">
                                <span class="tb-ni-icon tb-ni-vac" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M19 3l2 2-7 7"/><path d="M17 5l2 2"/><path d="M3 21l9-9"/><path d="M14.5 5.5l-11 11 4 4 11-11z"/>
                                    </svg>
                                </span>
                                <span class="tb-ni-copy">
                                    <span class="tb-ni-title">Vacuna · <?php echo e($v['nombre_vaca']); ?></span>
                                    <span class="tb-ni-sub"><?php echo e($v['tipo_vacuna']); ?> · <?php echo date('d/m/Y', strtotime($v['fecha_programada'])); ?></span>
                                </span>
                                <span class="tb-ni-tag <?php echo $tagcls; ?>"><?php echo $txt; ?></span>
                            </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <a class="tb-notify-foot" href="vacunaciones.html">Ver todas las vacunaciones →</a>
                </div>
            </div>

            <!-- Avatar -->
            <button class="tb-avatar" type="button" aria-label="Perfil de usuario" title="<?php echo $userName; ?> · <?php echo ucfirst($userRole); ?>">
                <?php echo $userInitial; ?>
                <span class="tb-avatar-status" aria-hidden="true"></span>
            </button>

        </div>
    </header>

    <!-- CONTENT -->
    <div class="content">

        <!-- ─── STATS GRID ─── -->
        <section class="stats-grid">

            <!-- Total Vacas -->
            <!-- KPI: Total Vacas -->
            <div class="stat-card">
                <div class="sc-head">
                    <span class="sc-label">Total Vacas</span>
                    <div class="sc-icon">
                        <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
                            <ellipse cx="10" cy="7" rx="6" ry="4"/>
                            <path d="M4 11c0 2.8 2.7 5 6 5s6-2.2 6-5"/>
                        </svg>
                    </div>
                </div>
                <?php if ($totalVacas > 0): ?>
                <div class="sc-value" data-count="<?php echo $totalVacas; ?>">0</div>
                <div class="sc-delta">
                    <svg viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="2,8 6,4 10,8"/></svg>
                    <?php echo $totalProd; ?> en producción
                </div>
                <?php else: ?>
                <div class="sc-empty">
                    <span class="sc-empty-msg">Aún no hay animales registrados</span>
                    <a href="Registro_Vacas.php" class="sc-empty-cta">Registrar ahora →</a>
                </div>
                <?php endif; ?>
            </div>

            <!-- KPI: Producción Hoy -->
            <div class="stat-card">
                <div class="sc-head">
                    <span class="sc-label">Producción Hoy</span>
                    <div class="sc-icon">
                        <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
                            <path d="M10 2C5 2 3 7.5 3 11.5A7 7 0 0017 11.5C17 7.5 15 2 10 2z"/>
                        </svg>
                    </div>
                </div>
                <?php if ($produccionHoy > 0): ?>
                <div class="sc-value" data-count="<?php echo (int)$produccionHoy; ?>">0<span class="sc-unit">L</span></div>
                <div class="sc-delta"><?php echo number_format($litrosSemana,0); ?>L esta semana</div>
                <canvas class="sc-spark" id="spark2"></canvas>
                <?php else: ?>
                <div class="sc-empty">
                    <span class="sc-empty-msg">Sin registros de ordeño hoy</span>
                    <a href="produccion_lechera.php" class="sc-empty-cta">Registrar ordeño →</a>
                </div>
                <?php endif; ?>
            </div>

            <!-- KPI: Vacas Preñadas -->
            <div class="stat-card">
                <div class="sc-head">
                    <span class="sc-label">Vacas Preñadas</span>
                    <div class="sc-icon">
                        <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
                            <circle cx="10" cy="10" r="8"/>
                            <path d="M7 12s1.2 2 3 2 3-2 3-2"/>
                            <line x1="8" y1="8" x2="8.01" y2="8"/><line x1="12" y1="8" x2="12.01" y2="8"/>
                        </svg>
                    </div>
                </div>
                <?php if ($vacasPrenadas > 0): ?>
                <div class="sc-value" data-count="<?php echo $vacasPrenadas; ?>">0</div>
                <div class="sc-delta"><?php echo $totalVacas > 0 ? round($vacasPrenadas/$totalVacas*100) : 0; ?>% del hato</div>
                <?php else: ?>
                <div class="sc-empty">
                    <span class="sc-empty-msg">Ninguna enrazada registrada</span>
                    <a href="Registro_Vacas.php" class="sc-empty-cta">Actualizar estado →</a>
                </div>
                <?php endif; ?>
            </div>

            <!-- KPI: En Secado -->
            <div class="stat-card">
                <div class="sc-head">
                    <span class="sc-label">En Secado</span>
                    <div class="sc-icon">
                        <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
                            <circle cx="10" cy="10" r="8"/>
                            <path d="M10 6v4l2.5 2.5"/>
                        </svg>
                    </div>
                </div>
                <?php if ($vacasSecado > 0): ?>
                <div class="sc-value" data-count="<?php echo $vacasSecado; ?>">0</div>
                <div class="sc-delta"><?php echo $totalVacas > 0 ? round($vacasSecado/$totalVacas*100) : 0; ?>% del total</div>
                <?php else: ?>
                <div class="sc-empty">
                    <span class="sc-empty-msg">Ninguna en secado actualmente</span>
                    <a href="Registro_Vacas.php" class="sc-empty-cta">Actualizar estado →</a>
                </div>
                <?php endif; ?>
            </div>

        </section>

        <!-- ─── ROW 2: Gráfico línea + Tabla ─── -->
        <div class="row-grid">

            <!-- Gráfico de producción semanal -->
            <div class="panel panel-chart">
                <div class="panel-head">
                    <div class="panel-head-left">
                        <span class="panel-title">Producción Mensual</span>
                        <span class="panel-badge">Últimos 30 días</span>
                    </div>
                    <a href="produccion_lechera.php" class="panel-action">
                        Ver detalle
                        <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                            <line x1="3" y1="8" x2="13" y2="8"/><polyline points="9,4 13,8 9,12"/>
                        </svg>
                    </a>
                </div>
                <div class="chart-area">
                    <?php if (empty($lineaDias)): ?>
                    <div class="chart-placeholder">
                        <svg class="chart-placeholder-icon" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="1.5" stroke-linecap="round">
                            <polyline points="3,17 8,12 13,15 21,7"/>
                            <line x1="3" y1="20" x2="21" y2="20"/>
                        </svg>
                        <span class="chart-placeholder-msg">Aún no hay registros de producción</span>
                        <a href="produccion_lechera.php" class="chart-placeholder-cta">Registrar primer ordeño →</a>
                    </div>
                    <?php else: ?>
                    <canvas id="chartLine"></canvas>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Tabla producción reciente -->
            <div class="panel panel-table">
                <div class="panel-head">
                    <div class="panel-head-left">
                        <span class="panel-title">Producción Reciente</span>
                    </div>
                    <a href="produccion_lechera.php" class="panel-action">
                        Ver todo
                        <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                            <line x1="3" y1="8" x2="13" y2="8"/><polyline points="9,4 13,8 9,12"/>
                        </svg>
                    </a>
                </div>
                <div class="table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Animal</th>
                                <th>Fecha</th>
                                <th>Litros</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($queryTabla)): ?>
                            <tr>
                                <td>
                                    <div class="cow-cell">
                                        <div class="cow-avatar"><?php echo strtoupper(substr($row['nombre'],0,1)); ?></div>
                                        <span class="cow-name"><?php echo e($row['nombre']); ?></span>
                                    </div>
                                </td>
                                <td><span class="tag-date"><?php echo date('d/m/Y',strtotime($row['fecha'])); ?></span></td>
                                <td>
                                    <span class="tag-litros">
                                        <?php echo rtrim(rtrim($row['litros'],'0'),'.'); ?>
                                        <span class="tag-unit">L</span>
                                    </span>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div><!-- /row-grid -->

        <!-- ─── ROW 3: Barras por vaca + Vacunas ─── -->
        <div class="row-grid">

            <!-- Gráfico barras por vaca -->
            <div class="panel panel-chart">
                <div class="panel-head">
                    <div class="panel-head-left">
                        <span class="panel-title">Producción por Vaca</span>
                        <span class="panel-badge">Total acumulado</span>
                    </div>
                </div>
                <div class="chart-area">
                    <canvas id="chartBar"></canvas>
                </div>
            </div>

            <!-- Próximas vacunas -->
            <div class="panel panel-vaccines">
                <div class="panel-head">
                    <div class="panel-head-left">
                        <span class="panel-title">Próximas Vacunas</span>
                    </div>
                    <a href="vacunaciones.html" class="panel-action">
                        Gestionar
                        <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                            <line x1="3" y1="8" x2="13" y2="8"/><polyline points="9,4 13,8 9,12"/>
                        </svg>
                    </a>
                </div>

                <?php if (!empty($proximasVacunas)): ?>
                <div class="vaccine-list">
                    <?php foreach ($proximasVacunas as $v):
                        $d = (int)$v['dias_restantes'];
                        if ($d === 0)     { $txt = 'Hoy';            $cls = 'pill-red'; }
                        elseif ($d === 1) { $txt = 'Mañana';         $cls = 'pill-amber'; }
                        else              { $txt = "En {$d} días";   $cls = 'pill-green'; }
                    ?>
                    <div class="vac-row">
                        <div class="cow-cell">
                            <div class="cow-avatar"><?php echo strtoupper(substr($v['nombre_vaca'],0,1)); ?></div>
                            <div class="vac-copy">
                                <span class="cow-name"><?php echo e($v['nombre_vaca']); ?></span>
                                <span class="vac-type"><?php echo e($v['tipo_vacuna']); ?></span>
                            </div>
                        </div>
                        <div class="vac-meta">
                            <span class="tag-date"><?php echo date('d/m/Y',strtotime($v['fecha_programada'])); ?></span>
                            <span class="pill <?php echo $cls; ?>"><?php echo $txt; ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="empty-block">
                    <svg viewBox="0 0 40 40" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" class="empty-icon">
                        <circle cx="20" cy="20" r="16"/><path d="M20 12v8l5 5"/>
                    </svg>
                    <p>Sin vacunas pendientes próximas</p>
                    <a href="vacunaciones.html" class="empty-link">Programar vacuna →</a>
                </div>
                <?php endif; ?>
            </div>

        </div><!-- /row-grid -->

        <!-- ─── ROW 4: Alertas de Producción ─── -->
        <div class="panel panel-vaccines">
            <div class="panel-head">
                <div class="panel-head-left">
                    <span class="panel-title">Alertas de Producción</span>
                    <span class="panel-badge">Caída ≥10% vs. promedio</span>
                </div>
                <a href="produccion_lechera.php" class="panel-action">
                    Ver detalle
                    <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                        <line x1="3" y1="8" x2="13" y2="8"/><polyline points="9,4 13,8 9,12"/>
                    </svg>
                </a>
            </div>

            <?php if (!empty($alertasProduccion)): ?>
            <div class="vaccine-list">
                <?php foreach ($alertasProduccion as $a): ?>
                <div class="vac-row vac-warning">
                    <div class="cow-cell">
                        <div class="cow-avatar"><?php echo strtoupper(substr($a['nombre'],0,1)); ?></div>
                        <div class="vac-copy">
                            <span class="cow-name"><?php echo e($a['nombre']); ?></span>
                            <span class="vac-type"><?php echo number_format($a['avg_base'],1); ?>L → <?php echo number_format($a['avg_reciente'],1); ?>L prom./día</span>
                        </div>
                    </div>
                    <div class="vac-meta">
                        <span class="pill pill-amber">-<?php echo $a['caida_pct']; ?>%</span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="empty-block">
                <svg viewBox="0 0 40 40" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" class="empty-icon">
                    <polyline points="8,28 16,20 22,25 32,12"/>
                </svg>
                <p>Sin caídas de producción relevantes</p>
            </div>
            <?php endif; ?>
        </div>

    </div><!-- /content -->
</div><!-- /main -->

<!-- overlay sidebar móvil -->
<div class="sb-overlay" id="sbOverlay"></div>

<script>
/* ══════════════════════════════════════════
   CONTADORES ANIMADOS
   ══════════════════════════════════════════ */
function animCount(el) {
    const target = parseInt(el.dataset.count, 10) || 0;
    const unit   = el.querySelector('.sc-unit');
    const dur    = 1400, step = 14;
    const steps  = Math.ceil(dur / step);
    let   i = 0;
    const t = setInterval(() => {
        i++;
        const v = Math.round(target * (i / steps));
        el.childNodes[0].textContent = v;
        if (i >= steps) { el.childNodes[0].textContent = target; clearInterval(t); }
    }, step);
}

/* ══════════════════════════════════════════
   SPARKLINES
   ══════════════════════════════════════════ */
function drawSpark(id, color, data) {
    const canvas = document.getElementById(id);
    if (!canvas) return;
    new Chart(canvas, {
        type: 'line',
        data: {
            labels: data.map((_,i) => i),
            datasets: [{ data, borderColor: color, borderWidth: 1.8,
                backgroundColor: color.replace('1)', '.12)'),
                fill: true, tension: 0.4, pointRadius: 0 }]
        },
        options: { responsive: false, animation: false,
            plugins: { legend: { display: false }, tooltip: { enabled: false } },
            scales: { x: { display: false }, y: { display: false } } }
    });
}

/* ══════════════════════════════════════════
   GRÁFICO LÍNEA — 7 días
   ══════════════════════════════════════════ */
(function(){
    const labels = <?php echo json_encode($lineaDias ?: ['L','M','X','J','V','S','D']); ?>;
    const data   = <?php echo json_encode($lineaLitros ?: [0,0,0,0,0,0,0]); ?>;
    const isDark = !document.documentElement.classList.contains('light');
    const gc = isDark ? 'rgba(255,255,255,0.04)' : 'rgba(0,0,0,0.05)';
    const tc = isDark ? '#4B5563' : '#9CA3AF';

    const gradient = document.getElementById('chartLine').getContext('2d').createLinearGradient(0,0,0,220);
    gradient.addColorStop(0, 'rgba(34,197,94,0.22)');
    gradient.addColorStop(1, 'rgba(34,197,94,0)');

    window._lineChart = new Chart(document.getElementById('chartLine'), {
        type: 'line',
        data: {
            labels,
            datasets: [{
                label: 'Litros', data,
                borderColor: '#22C55E', borderWidth: 2.2,
                backgroundColor: gradient, fill: true,
                tension: 0.45, pointRadius: 4,
                pointBackgroundColor: '#22C55E',
                pointBorderColor: isDark ? '#101511' : '#fff',
                pointBorderWidth: 2,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: isDark ? '#1C2720' : '#fff',
                    titleColor: tc, bodyColor: isDark ? '#D1FAE5' : '#111',
                    borderColor: isDark ? 'rgba(255,255,255,0.08)' : 'rgba(0,0,0,0.08)',
                    borderWidth: 1, padding: 10, cornerRadius: 8,
                    callbacks: { label: ctx => ' ' + ctx.parsed.y + ' L' }
                }
            },
            scales: {
                x: { ticks: { color: tc, font: { family: 'Inter', size: 11 } }, grid: { display: false }, border: { color: 'transparent' } },
                y: { beginAtZero: true, ticks: { color: tc, font: { family: 'JetBrains Mono', size: 10 } }, grid: { color: gc }, border: { color: 'transparent', dash: [4,4] } }
            },
            animation: { duration: 900, easing: 'easeOutQuart' }
        }
    });
})();

/* ══════════════════════════════════════════
   GRÁFICO BARRAS — por vaca
   ══════════════════════════════════════════ */
(function(){
    const labels = <?php echo json_encode($nombres); ?>;
    const data   = <?php echo json_encode($litros); ?>;
    const isDark = !document.documentElement.classList.contains('light');
    const tc = isDark ? '#4B5563' : '#9CA3AF';
    const gc = isDark ? 'rgba(255,255,255,0.04)' : 'rgba(0,0,0,0.05)';

    window._barChart = new Chart(document.getElementById('chartBar'), {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: 'Litros', data,
                backgroundColor: 'rgba(34,197,94,0.18)',
                borderColor: '#22C55E', borderWidth: 1.5,
                borderRadius: 7, borderSkipped: false,
                hoverBackgroundColor: 'rgba(34,197,94,0.32)'
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: isDark ? '#1C2720' : '#fff',
                    titleColor: tc, bodyColor: isDark ? '#D1FAE5' : '#111',
                    borderColor: isDark ? 'rgba(255,255,255,0.08)' : 'rgba(0,0,0,0.08)',
                    borderWidth: 1, padding: 10, cornerRadius: 8,
                    callbacks: { label: ctx => ' ' + ctx.parsed.y + ' L' }
                }
            },
            scales: {
                x: { ticks: { color: tc, font: { family: 'Inter', size: 11 } }, grid: { display: false }, border: { color: 'transparent' } },
                y: { beginAtZero: true, ticks: { color: tc, font: { family: 'JetBrains Mono', size: 10 } }, grid: { color: gc }, border: { color: 'transparent', dash: [4,4] } }
            },
            animation: { duration: 900, easing: 'easeOutQuart' }
        }
    });
})();

/* ══════════════════════════════════════════
   INIT al cargar
   ══════════════════════════════════════════ */
window.addEventListener('DOMContentLoaded', () => {
    // Tema guardado
    try { if (localStorage.getItem('acTheme') === 'light') document.documentElement.classList.add('light'); } catch(e){}

    // Contadores
    setTimeout(() => {
        document.querySelectorAll('.sc-value[data-count]').forEach(animCount);
    }, 200);

    // Sparklines (solo Producción Hoy tiene datos históricos reales)
    const sparkData = <?php echo json_encode($sparkProduccion ?: [0,0,0,0,0,0,0]); ?>;
    drawSpark('spark2', 'rgba(99,179,237,1)', sparkData);
});

/* ══════════════════════════════════════════
   THEME TOGGLE
   ══════════════════════════════════════════ */
function toggleTheme() {
    document.documentElement.classList.toggle('light');
    const isLight = document.documentElement.classList.contains('light');
    try { localStorage.setItem('acTheme', isLight ? 'light' : 'dark'); } catch(e){}
    if (window._lineChart) {
        const tc = isLight ? '#9CA3AF' : '#4B5563';
        const gc = isLight ? 'rgba(0,0,0,0.05)' : 'rgba(255,255,255,0.04)';
        ['_lineChart','_barChart'].forEach(k => {
            if (!window[k]) return;
            window[k].options.scales.x.ticks.color = tc;
            window[k].options.scales.y.ticks.color = tc;
            window[k].options.scales.y.grid.color  = gc;
            window[k].update();
        });
    }
}

/* ══════════════════════════════════════════
   MENÚ HAMBURGUESA
   Desktop (>860px): colapsa/expande el sidebar.
   Móvil (≤860px):   abre/cierra el drawer + overlay.
   ══════════════════════════════════════════ */
const toggle  = document.getElementById('menuToggle');
const sidebar = document.getElementById('sidebar');
const overlay = document.getElementById('sbOverlay');
const isMobile = () => window.matchMedia('(max-width: 860px)').matches;

function closeMobileSidebar() {
    sidebar.classList.remove('sb-open');
    overlay.classList.remove('sb-overlay-show');
}

if (toggle) {
    // Restaurar estado colapsado (solo desktop)
    try {
        if (localStorage.getItem('acSidebar') === 'collapsed' && !isMobile()) {
            document.body.classList.add('sb-collapsed');
        }
    } catch (e) {}

    toggle.addEventListener('click', () => {
        if (isMobile()) {
            sidebar.classList.toggle('sb-open');
            overlay.classList.toggle('sb-overlay-show');
        } else {
            const collapsed = document.body.classList.toggle('sb-collapsed');
            try { localStorage.setItem('acSidebar', collapsed ? 'collapsed' : 'expanded'); } catch (e) {}
        }
    });
    overlay.addEventListener('click', closeMobileSidebar);
    // Al pasar a desktop, cerrar cualquier drawer abierto
    window.addEventListener('resize', () => { if (!isMobile()) closeMobileSidebar(); });
}

/* ══════════════════════════════════════════
   BUSCADOR — navegación rápida
   ══════════════════════════════════════════ */
(function(){
    const input   = document.getElementById('tbSearchInput');
    const results = document.getElementById('tbSearchResults');
    if (!input || !results) return;

    // Destinos disponibles (respeta rol de administrador)
    const DEST = [
        { t: 'Dashboard',          d: 'Resumen general y métricas',      u: 'Dashboard.php',          k: 'inicio panel metricas resumen' },
        { t: 'Gestión de vacas',   d: 'Registro y estado del hato',      u: 'Registro_Vacas.php',     k: 'animales ganado hato registro vacas' },
        { t: 'Producción lechera', d: 'Ordeños y litros por vaca',       u: 'produccion_lechera.php', k: 'leche litros ordeño produccion' },
        { t: 'Potreros y mangas',  d: 'Distribución de potreros',        u: 'potrero.php',            k: 'potreros mangas pastura terreno' },
        { t: 'Vacunaciones',       d: 'Calendario de vacunas',           u: 'vacunaciones.html',      k: 'vacunas salud sanidad calendario' }
        <?php if (current_user_role() === 'administrador'): ?>
        ,{ t: 'Gestión de usuarios', d: 'Administrar cuentas del sistema', u: 'usuarios.php',         k: 'usuarios cuentas admin permisos roles' }
        <?php endif; ?>
    ];

    let active = -1;
    let shown  = [];

    const norm = s => s.toLowerCase().normalize('NFD').replace(/\p{Diacritic}/gu, '');

    function render(list) {
        shown = list;
        active = -1;
        if (!list.length) {
            results.innerHTML = '<div class="tb-sr-empty">Sin resultados</div>';
        } else {
            results.innerHTML = list.map((r,i) =>
                `<a class="tb-sr-item" href="${r.u}" role="option" data-i="${i}">
                    <span class="tb-sr-title">${r.t}</span>
                    <span class="tb-sr-sub">${r.d}</span>
                 </a>`).join('');
        }
        results.hidden = false;
        input.setAttribute('aria-expanded', 'true');
    }

    function close() {
        results.hidden = true;
        active = -1;
        input.setAttribute('aria-expanded', 'false');
    }

    function highlight() {
        [...results.querySelectorAll('.tb-sr-item')].forEach((el,i) =>
            el.classList.toggle('tb-sr-active', i === active));
    }

    input.addEventListener('input', () => {
        const q = norm(input.value.trim());
        if (!q) { close(); return; }
        render(DEST.filter(r => norm(r.t + ' ' + r.k).includes(q)));
    });

    input.addEventListener('keydown', (e) => {
        if (results.hidden) return;
        if (e.key === 'ArrowDown')      { e.preventDefault(); active = Math.min(active+1, shown.length-1); highlight(); }
        else if (e.key === 'ArrowUp')   { e.preventDefault(); active = Math.max(active-1, 0); highlight(); }
        else if (e.key === 'Enter')     { e.preventDefault(); const r = shown[active] || shown[0]; if (r) location.href = r.u; }
        else if (e.key === 'Escape')    { close(); input.blur(); }
    });

    input.addEventListener('focus', () => { if (input.value.trim()) input.dispatchEvent(new Event('input')); });
    document.addEventListener('click', (e) => { if (!e.target.closest('.tb-search-wrap')) close(); });
})();

/* ══════════════════════════════════════════
   NOTIFICACIONES — panel desplegable
   ══════════════════════════════════════════ */
(function(){
    const btn   = document.getElementById('notifToggle');
    const panel = document.getElementById('notifPanel');
    if (!btn || !panel) return;

    function toggleP(open) {
        const show = open ?? panel.hidden;
        panel.hidden = !show;
        btn.setAttribute('aria-expanded', String(show));
    }

    btn.addEventListener('click', (e) => { e.stopPropagation(); toggleP(); });
    document.addEventListener('click', (e) => {
        if (!e.target.closest('.tb-notify-wrap')) toggleP(false);
    });
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') toggleP(false); });
})();
</script>
</body>
</html>
