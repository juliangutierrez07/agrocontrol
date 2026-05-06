<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: ../Login/iniciar_sesion.php");
    exit();
}

include("../Config/conexion.php");
$con = conexion();

$usuario_id = (int) $_SESSION['id'];
$vaca_id = isset($_GET['vaca_id']) ? (int) $_GET['vaca_id'] : 0;

if ($vaca_id <= 0) {
    header("Location: produccion_lechera.php");
    exit();
}

$vacaQuery = mysqli_query($con, "
    SELECT id, codigo, nombre, raza, estado, foto, descripcion, vacunas_info, partos
    FROM vacas
    WHERE id = '$vaca_id' AND usuario_id = '$usuario_id'
    LIMIT 1
");
$vaca = mysqli_fetch_assoc($vacaQuery);

if (!$vaca) {
    header("Location: produccion_lechera.php");
    exit();
}

$desde = isset($_GET['desde']) ? trim($_GET['desde']) : '';
$hasta = isset($_GET['hasta']) ? trim($_GET['hasta']) : '';
$precioLitro = isset($_GET['precio_litro']) ? (float) $_GET['precio_litro'] : 0;
$filtroFecha = '';

if ($desde !== '' && $hasta !== '') {
    $desdeSafe = mysqli_real_escape_string($con, $desde);
    $hastaSafe = mysqli_real_escape_string($con, $hasta);
    $filtroFecha = " AND rl.fecha BETWEEN '$desdeSafe' AND '$hastaSafe'";
}

$precioLitro = $precioLitro > 0 ? $precioLitro : 0;

$statsQuery = mysqli_query($con, "
    SELECT
        COUNT(*) AS total_registros,
        COALESCE(SUM(rl.litros), 0) AS total_litros,
        COALESCE(AVG(rl.litros), 0) AS promedio_litros,
        MAX(rl.fecha) AS ultimo_registro
    FROM registroleche rl
    WHERE rl.usuario_id = '$usuario_id'
      AND rl.vaca_id = '$vaca_id'
      $filtroFecha
");
$stats = mysqli_fetch_assoc($statsQuery);

$historialQuery = mysqli_query($con, "
    SELECT rl.id, rl.fecha, rl.litros
    FROM registroleche rl
    WHERE rl.usuario_id = '$usuario_id'
      AND rl.vaca_id = '$vaca_id'
      $filtroFecha
    ORDER BY rl.fecha DESC, rl.id DESC
");

$chartQuery = mysqli_query($con, "
    SELECT rl.fecha, SUM(rl.litros) AS litros_dia
    FROM registroleche rl
    WHERE rl.usuario_id = '$usuario_id'
      AND rl.vaca_id = '$vaca_id'
      $filtroFecha
    GROUP BY rl.fecha
    ORDER BY rl.fecha ASC
");

$chartLabels = [];
$chartValues = [];
while ($point = mysqli_fetch_assoc($chartQuery)) {
    $chartLabels[] = date('d/m/Y', strtotime($point['fecha']));
    $chartValues[] = (float) $point['litros_dia'];
}

$quincenas = [];
$quincenaQuery = mysqli_query($con, "
    SELECT
        YEAR(rl.fecha) AS anio,
        MONTH(rl.fecha) AS mes,
        CASE WHEN DAY(rl.fecha) <= 15 THEN 1 ELSE 2 END AS numero_quincena,
        MIN(rl.fecha) AS fecha_inicio,
        MAX(rl.fecha) AS fecha_fin,
        COUNT(*) AS total_registros,
        COALESCE(SUM(rl.litros), 0) AS total_litros
    FROM registroleche rl
    WHERE rl.usuario_id = '$usuario_id'
      AND rl.vaca_id = '$vaca_id'
      $filtroFecha
    GROUP BY YEAR(rl.fecha), MONTH(rl.fecha), CASE WHEN DAY(rl.fecha) <= 15 THEN 1 ELSE 2 END
    ORDER BY YEAR(rl.fecha) DESC, MONTH(rl.fecha) DESC, numero_quincena DESC
");

while ($quincena = mysqli_fetch_assoc($quincenaQuery)) {
    $quincena['total_litros'] = (float) $quincena['total_litros'];
    $quincena['pago_estimado'] = $precioLitro > 0 ? $quincena['total_litros'] * $precioLitro : 0;
    $quincenas[] = $quincena;
}

$ultimaQuincena = $quincenas[0] ?? null;

$estadoLabel = $vaca['estado'];
if ($vaca['estado'] === 'produccion') {
    $estadoLabel = 'Producción';
} elseif ($vaca['estado'] === 'secado') {
    $estadoLabel = 'Secado';
} elseif ($vaca['estado'] === 'enrazada') {
    $estadoLabel = 'Preñada';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgroControl | Historial de Leche</title>
    <link rel="stylesheet" href="../Css/historial_leche.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet"/>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
            <div class="topbar-breadcrumb">Principal / Producción / Historial</div>
            <div class="topbar-title">Historial de leche por vaca</div>
        </div>
        <div class="topbar-right">
            <a href="historial_quincenas_leche.php" class="btn-pay-history">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 1v22"/><path d="M17 5H9.5a3.5 3.5 0 000 7H14.5a3.5 3.5 0 010 7H6"/><path d="M12 1v3"/><path d="M12 20v3"/></svg>
                Quincenas generales
            </a>
            <a href="produccion_lechera.php" class="back-link">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M15 18l-6-6 6-6"/><path d="M21 12H9"/></svg>
                Volver a producción
            </a>
            <button class="theme-toggle" onclick="toggleTheme()" title="Cambiar tema">
                <svg class="icon-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/></svg>
                <svg class="icon-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
            </button>
        </div>
    </div>

    <div class="history-layout">
        <div class="history-side">
            <div class="hero-card">
                <div class="hero-eyebrow">Seguimiento individual</div>
                <div class="hero-title">
                    <div class="hero-media">
                        <?php if (!empty($vaca['foto'])): ?>
                            <button
                                type="button"
                                class="hero-photo-trigger"
                                data-photo-trigger
                                aria-label="Ver imagen ampliada de <?php echo htmlspecialchars($vaca['nombre']); ?>"
                            >
                                <img src="<?php echo htmlspecialchars($vaca['foto']); ?>" alt="Foto de <?php echo htmlspecialchars($vaca['nombre']); ?>" class="hero-photo">
                            </button>
                        <?php else: ?>
                            <div class="hero-avatar"><?php echo strtoupper(substr($vaca['nombre'], 0, 1)); ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="hero-copy">
                        <div class="hero-name"><?php echo htmlspecialchars($vaca['nombre']); ?></div>
                        <div class="hero-subcopy">Ficha visual y resumen del animal</div>
                    </div>
                </div>

                <div class="hero-meta">
                    <span class="meta-pill">Código #<?php echo htmlspecialchars($vaca['codigo']); ?></span>
                    <span class="meta-pill"><?php echo htmlspecialchars($vaca['raza']); ?></span>
                    <span class="meta-pill"><?php echo htmlspecialchars($estadoLabel); ?></span>
                </div>

                <p class="hero-text">
                    Aquí puedes revisar los litros registrados para esta vaca. Para pagos, usa el historial general de quincenas.
                </p>

                <div class="hero-detail-block">
                    <div class="hero-detail-title">Ficha rápida</div>
                    <div class="hero-detail-item"><span>Partos</span><strong><?php echo (int) ($vaca['partos'] ?? 0); ?></strong></div>
                    <div class="hero-detail-item hero-detail-stack">
                        <span>Procedencia</span>
                        <p><?php echo !empty($vaca['descripcion']) ? nl2br(htmlspecialchars($vaca['descripcion'])) : 'Sin descripción registrada'; ?></p>
                    </div>
                    <div class="hero-detail-item hero-detail-stack">
                        <span>Vacunas registradas</span>
                        <p><?php echo !empty($vaca['vacunas_info']) ? nl2br(htmlspecialchars($vaca['vacunas_info'])) : 'Sin vacunas registradas en la ficha'; ?></p>
                    </div>
                </div>

                <div class="hero-actions">
                    <a href="Registro_Vacas.php" class="btn-icon-edit">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M15 18l-6-6 6-6"/><path d="M21 12H9"/></svg>
                        Ver vacas
                    </a>
                    <a href="produccion_lechera.php" class="btn-icon-view">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M3 12h18"/><path d="M12 3v18"/></svg>
                        Registrar más litros
                    </a>
                </div>
            </div>
        </div>

        <div class="history-main">
            <div class="history-summary">
                <div class="summary-card">
                    <div class="summary-label">Total litros</div>
                    <div class="summary-value"><?php echo number_format((float) $stats['total_litros'], 1); ?></div>
                    <div class="summary-meta">acumulados en el historial</div>
                </div>
                <div class="summary-card">
                    <div class="summary-label">Registros</div>
                    <div class="summary-value"><?php echo (int) $stats['total_registros']; ?></div>
                    <div class="summary-meta">entradas encontradas</div>
                </div>
                <div class="summary-card">
                    <div class="summary-label">Promedio</div>
                    <div class="summary-value"><?php echo number_format((float) $stats['promedio_litros'], 1); ?></div>
                    <div class="summary-meta">litros por registro</div>
                </div>
                <div class="summary-card">
                    <div class="summary-label">Último registro</div>
                    <div class="summary-value">
                        <?php echo $stats['ultimo_registro'] ? date('d/m', strtotime($stats['ultimo_registro'])) : 'Sin datos'; ?>
                    </div>
                    <div class="summary-meta">
                        <?php echo $stats['ultimo_registro'] ? date('Y', strtotime($stats['ultimo_registro'])) : 'todavía'; ?>
                    </div>
                </div>
            </div>

            <div class="summary-card summary-card-highlight">
                <div class="summary-label">Historial de pagos quincenales</div>
                <div class="summary-helper">Aquí ves el corte que más te sirve para revisar cuánto te corresponde por quincena.</div>
                <div class="summary-value">
                    <?php echo $ultimaQuincena ? number_format((float) $ultimaQuincena['total_litros'], 1) : '0.0'; ?>
                </div>
                <div class="summary-meta">
                    <?php if ($ultimaQuincena): ?>
                        Q<?php echo (int) $ultimaQuincena['numero_quincena']; ?> de <?php echo date('m/Y', strtotime($ultimaQuincena['fecha_inicio'])); ?>
                    <?php else: ?>
                        sin cortes quincenales aún
                    <?php endif; ?>
                </div>
            </div>

            <div class="chart-card">
                <div class="toolbar-inline">
                    <span class="table-heading">Evolución de producción</span>
                    <form method="GET" class="filtro-form">
                        <input type="hidden" name="vaca_id" value="<?php echo $vaca_id; ?>">
                        <div class="filtro-wrap">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                            <input type="date" name="desde" value="<?php echo htmlspecialchars($desde); ?>" title="Desde">
                            <span class="filtro-sep">-</span>
                            <input type="date" name="hasta" value="<?php echo htmlspecialchars($hasta); ?>" title="Hasta">
                            <input type="number" name="precio_litro" value="<?php echo $precioLitro > 0 ? htmlspecialchars((string) $precioLitro) : ''; ?>" step="0.01" min="0" placeholder="Valor litro">
                            <button type="submit" class="btn-filtrar">Filtrar</button>
                            <a href="historial_leche.php?vaca_id=<?php echo $vaca_id; ?>" class="btn-reset">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                            </a>
                        </div>
                    </form>
                </div>
                <div class="chart-wrap">
                    <canvas id="historialChart"></canvas>
                </div>
            </div>

            <div class="history-table-card payment-history-card" id="historial-pagos">
                <div class="toolbar-inline">
                    <span class="table-heading">Historial de pagos por quincena</span>
                    <?php if ($desde !== '' && $hasta !== ''): ?>
                        <span class="search-badge"><?php echo htmlspecialchars($desde); ?> - <?php echo htmlspecialchars($hasta); ?></span>
                    <?php endif; ?>
                </div>

                <?php if ((int) $stats['total_registros'] > 0): ?>
                    <div class="table-scroll">
                        <table>
                            <thead>
                                <tr>
                                    <th>Periodo</th>
                                    <th>Rango</th>
                                    <th>Litros</th>
                                    <th>Registros</th>
                                    <th>Pago estimado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($quincenas as $quincena): ?>
                                <tr>
                                    <td>
                                        <span class="chip-date">Q<?php echo (int) $quincena['numero_quincena']; ?> · <?php echo date('m/Y', strtotime($quincena['fecha_inicio'])); ?></span>
                                    </td>
                                    <td>
                                        <?php echo date('d/m/Y', strtotime($quincena['fecha_inicio'])); ?> - <?php echo date('d/m/Y', strtotime($quincena['fecha_fin'])); ?>
                                    </td>
                                    <td>
                                        <span class="litros-strong">
                                            <?php echo rtrim(rtrim(number_format($quincena['total_litros'], 1, '.', ''), '0'), '.'); ?>
                                            <span>L</span>
                                        </span>
                                    </td>
                                    <td><?php echo (int) $quincena['total_registros']; ?> movimientos</td>
                                    <td>
                                        <?php if ($precioLitro > 0): ?>
                                            <span class="payment-strong">$<?php echo number_format($quincena['pago_estimado'], 0, ',', '.'); ?></span>
                                        <?php else: ?>
                                            <span class="payment-muted">Ingresa valor litro</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-history">
                        <h3>Esta vaca todavía no tiene litros registrados</h3>
                        <p>Puedes volver al módulo de producción y crear el primer registro para empezar a ver el historial.</p>
                        <a href="produccion_lechera.php" class="btn-primary">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            Registrar litros
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <?php if ((int) $stats['total_registros'] > 0): ?>
            <div class="history-table-card">
                <div class="toolbar-inline">
                    <span class="table-heading">Historial detallado</span>
                </div>
                <div class="table-scroll">
                    <table>
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Litros</th>
                                <th>Detalle</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($registro = mysqli_fetch_assoc($historialQuery)): ?>
                            <tr>
                                <td>
                                    <span class="chip-date"><?php echo date('d/m/Y', strtotime($registro['fecha'])); ?></span>
                                </td>
                                <td>
                                    <span class="litros-strong">
                                        <?php echo rtrim(rtrim($registro['litros'], '0'), '.'); ?>
                                        <span>L</span>
                                    </span>
                                </td>
                                <td>Registro #<?php echo (int) $registro['id']; ?> de <?php echo htmlspecialchars($vaca['nombre']); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if (!empty($vaca['foto'])): ?>
<div class="photo-modal" data-photo-modal hidden>
    <div class="photo-modal-backdrop"></div>
    <div class="photo-modal-dialog" role="dialog" aria-modal="true" aria-label="Imagen ampliada de <?php echo htmlspecialchars($vaca['nombre']); ?>">
        <button type="button" class="photo-modal-close" data-photo-close aria-label="Cerrar imagen ampliada">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round">
                <line x1="18" y1="6" x2="6" y2="18"/>
                <line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
        </button>
        <img
            src="<?php echo htmlspecialchars($vaca['foto']); ?>"
            alt="Imagen ampliada de <?php echo htmlspecialchars($vaca['nombre']); ?>"
            class="photo-modal-image"
        >
    </div>
</div>
<?php endif; ?>

<script>
function toggleTheme() {
    document.documentElement.classList.toggle('light');
    localStorage.setItem('theme', document.documentElement.classList.contains('light') ? 'light' : 'dark');
}

(function() {
    if (localStorage.getItem('theme') === 'light') {
        document.documentElement.classList.add('light');
    }
})();

(function() {
    const modal = document.querySelector('[data-photo-modal]');
    const trigger = document.querySelector('[data-photo-trigger]');
    const closeButton = document.querySelector('.photo-modal-close');
    if (!modal || !trigger) return;

    function openModal() {
        modal.hidden = false;
        document.body.classList.add('modal-open');
    }

    function closeModal() {
        modal.hidden = true;
        document.body.classList.remove('modal-open');
    }

    trigger.addEventListener('click', openModal);
    if (closeButton) {
        closeButton.addEventListener('click', function(event) {
            event.preventDefault();
            event.stopPropagation();
            closeModal();
        });
    }

    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape' && !modal.hidden) {
            closeModal();
        }
    });
})();

(function() {
    const ctx = document.getElementById('historialChart');
    if (!ctx) return;

    const labels = <?php echo json_encode($chartLabels); ?>;
    const data = <?php echo json_encode($chartValues); ?>;
    const isDark = !document.documentElement.classList.contains('light');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Litros',
                data: data,
                fill: true,
                tension: 0.3,
                borderWidth: 2,
                borderColor: '#5BAF59',
                backgroundColor: isDark ? 'rgba(91,175,89,0.14)' : 'rgba(61,156,58,0.12)',
                pointBackgroundColor: '#74C472',
                pointBorderWidth: 0,
                pointRadius: 3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                x: {
                    ticks: {
                        color: isDark ? '#7A9474' : '#6A8E65'
                    },
                    grid: {
                        color: isDark ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.06)'
                    }
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        color: isDark ? '#7A9474' : '#6A8E65'
                    },
                    grid: {
                        color: isDark ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.06)'
                    }
                }
            }
        }
    });
})();
</script>
</body>
</html>
