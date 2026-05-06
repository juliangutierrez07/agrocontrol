<?php
session_start();

if(!isset($_SESSION['id'])){
    header("Location: ../login.php");
    exit();
}

include("../Config/conexion.php");
$con = conexion();

date_default_timezone_set('America/Bogota');

$usuario_id = $_SESSION['id'];

// TOTAL VACAS
$sql   = "SELECT COUNT(*) AS total FROM vacas WHERE usuario_id='$usuario_id'";
$query = mysqli_query($con, $sql);
$row   = mysqli_fetch_assoc($query);
$totalVacas = $row['total'];

// PRODUCCIÓN HOY
$sqlProd = "SELECT SUM(litros) AS total_litros 
            FROM registroleche 
            WHERE DATE(fecha) = CURDATE() 
            AND usuario_id='$usuario_id'";
$queryProd  = mysqli_query($con, $sqlProd);
$rowProd    = mysqli_fetch_assoc($queryProd);
$produccionHoy = $rowProd['total_litros'] ?? 0;

// PREÑADAS
$sqlPrenadas = "SELECT COUNT(*) AS total 
                FROM vacas 
                WHERE estado='enrazada' 
                AND usuario_id='$usuario_id'";
$queryPrenadas = mysqli_query($con, $sqlPrenadas);
$rowPrenadas   = mysqli_fetch_assoc($queryPrenadas);
$vacasPrenadas = $rowPrenadas['total'];

// SECADO
$sqlSecado = "SELECT COUNT(*) AS total 
              FROM vacas 
              WHERE estado='secado' 
              AND usuario_id='$usuario_id'";
$querySecado = mysqli_query($con, $sqlSecado);
$rowSecado   = mysqli_fetch_assoc($querySecado);
$vacasSecado = $rowSecado['total'];

// PRODUCCIÓN RECIENTE
$sqlTabla = "SELECT rl.fecha, rl.litros, v.nombre
             FROM registroleche rl
             INNER JOIN vacas v ON rl.vaca_id = v.id
             WHERE rl.usuario_id='$usuario_id'
             ORDER BY rl.fecha DESC
             LIMIT 5";
$queryTabla = mysqli_query($con, $sqlTabla);

// GRÁFICO
$sqlGrafico = "SELECT v.nombre, SUM(rl.litros) as total
               FROM registroleche rl
               INNER JOIN vacas v ON rl.vaca_id = v.id
               WHERE rl.usuario_id='$usuario_id'
               GROUP BY rl.vaca_id";
$queryGrafico = mysqli_query($con, $sqlGrafico);

$nombres = [];
$litros  = [];

while ($row = mysqli_fetch_assoc($queryGrafico)) {
    $nombres[] = $row['nombre'];
    $litros[]  = (float)$row['total'];
}

// STATS EXTRAS
$totalProd = mysqli_fetch_row(mysqli_query($con, 
"SELECT COUNT(*) FROM vacas WHERE estado='produccion' AND usuario_id='$usuario_id'"))[0];

$litrosSemana = mysqli_fetch_row(mysqli_query($con, 
"SELECT COALESCE(SUM(litros),0) 
 FROM registroleche 
 WHERE fecha >= DATE_SUB(CURDATE(),INTERVAL 7 DAY) 
 AND usuario_id='$usuario_id'"))[0];

$sqlVacunas = "SELECT va.fecha_programada,
                      v.nombre AS nombre_vaca,
                      tv.nombre AS tipo_vacuna,
                      DATEDIFF(va.fecha_programada, CURDATE()) AS dias_restantes
               FROM vacunaciones va
               INNER JOIN vacas v ON va.vaca_id = v.id
               INNER JOIN tipos_vacuna tv ON va.tipo_vacuna_id = tv.id
               WHERE v.usuario_id = '$usuario_id'
               AND va.estado = 'pendiente'
               AND va.fecha_programada >= CURDATE()
               ORDER BY va.fecha_programada ASC
               LIMIT 5";
$queryVacunas = mysqli_query($con, $sqlVacunas);
$proximasVacunas = [];

if ($queryVacunas) {
    while ($row = mysqli_fetch_assoc($queryVacunas)) {
        $proximasVacunas[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>AgroControl | Dashboard</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet"/>
<link rel="stylesheet" href="../Css/Dashboard.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

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
                <a href="#" class="active">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/></svg>
                    Dashboard
                </a>
                <a href="Registro_Vacas.php">
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

<!-- MAIN -->
<div class="main">

    <!-- TOPBAR -->
    <div class="topbar">
        <div class="topbar-left">
            <div class="topbar-breadcrumb">Principal</div>
            <div class="topbar-title">Dashboard</div>
            <div class="topbar-sub">Bienvenido, <?php echo $_SESSION['nombre']; ?>  </div>
        </div>
        <div class="topbar-right">
            <div class="date-badge">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                <?php echo date('d/m/Y'); ?>
            </div>
            <button class="theme-toggle" onclick="toggleTheme()" title="Cambiar tema">
                <svg class="icon-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/></svg>
                <svg class="icon-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
            </button>
        </div>
    </div>

    <!-- STATS GRID -->
    <div class="stats-grid">

        <div class="stat-card">
            <div class="stat-top">
                <div class="stat-label">Total Vacas</div>
                <div class="stat-icon si-green">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><ellipse cx="12" cy="8" rx="7" ry="5"/><path d="M5 13c0 3.3 3.1 6 7 6s7-2.7 7-6"/></svg>
                </div>
            </div>
            <div class="stat-value"><?php echo $totalVacas; ?></div>
            <div class="stat-delta up">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="18,15 12,9 6,15"/></svg>
                <?php echo $totalProd; ?> en producción
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-top">
                <div class="stat-label">Producción Hoy</div>
                <div class="stat-icon si-blue">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 2C6 2 3 9 3 14a9 9 0 0018 0c0-5-3-12-9-12z"/></svg>
                </div>
            </div>
            <div class="stat-value accent"><?php echo number_format($produccionHoy, 0); ?><span class="stat-unit">L</span></div>
            <div class="stat-delta neutral">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="5" y1="12" x2="19" y2="12"/></svg>
                <?php echo number_format($litrosSemana, 0); ?>L esta semana
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-top">
                <div class="stat-label">Vacas Preñadas</div>
                <div class="stat-icon si-amber">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/></svg>
                </div>
            </div>
            <div class="stat-value amber"><?php echo $vacasPrenadas; ?></div>
            <div class="stat-delta neutral">
                <?php echo $totalVacas > 0 ? round($vacasPrenadas / $totalVacas * 100) : 0; ?>% del hato
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-top">
                <div class="stat-label">En Secado</div>
                <div class="stat-icon si-blue">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/></svg>
                </div>
            </div>
            <div class="stat-value"><?php echo $vacasSecado; ?></div>
            <div class="stat-delta neutral">
                <?php echo $totalVacas > 0 ? round($vacasSecado / $totalVacas * 100) : 0; ?>% del total
            </div>
        </div>

    </div>

    <!-- BOTTOM GRID -->
    <div class="bottom-grid">

        <!-- TABLA PRODUCCIÓN RECIENTE -->
        <div class="section-card">
            <div class="section-head">
                <span class="section-title">Producción Reciente</span>
                <a href="produccion_lechera.php" class="section-action">
                    Ver todo
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12,5 19,12 12,19"/></svg>
                </a>
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Vaca</th>
                        <th>Fecha</th>
                        <th>Litros</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($queryTabla)): ?>
                    <tr>
                        <td>
                            <div class="cow-cell">
                                <div class="cow-avatar"><?php echo strtoupper(substr($row['nombre'], 0, 1)); ?></div>
                                <span class="cow-name"><?php echo $row['nombre']; ?></span>
                            </div>
                        </td>
                        <td><span class="fecha-tag"><?php echo date('d/m/Y', strtotime($row['fecha'])); ?></span></td>
                        <td><span class="litros-val"><?php echo rtrim(rtrim($row['litros'], '0'), '.'); ?> <span class="litros-unit">L</span></span></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- GRÁFICO -->
        <div class="section-card">
            <div class="section-head">
                <span class="section-title">Producción por Vaca</span>
                <span class="section-badge">Total acumulado</span>
            </div>
            <div class="chart-wrap">
                <canvas id="graficoVacas"></canvas>
            </div>
        </div>

        <div class="section-card">
            <div class="section-head">
                <span class="section-title">Próximas Vacunas</span>
                <a href="vacunaciones.html" class="section-action">
                    Gestionar
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12,5 19,12 12,19"/></svg>
                </a>
            </div>
            <?php if (!empty($proximasVacunas)): ?>
            <div class="vaccines-list">
                <?php foreach ($proximasVacunas as $vacuna): ?>
                <?php
                    $diasRestantes = (int)$vacuna['dias_restantes'];
                    if ($diasRestantes === 0) {
                        $textoDias = 'Hoy';
                        $claseEstado = 'today';
                    } elseif ($diasRestantes === 1) {
                        $textoDias = 'Mañana';
                        $claseEstado = 'soon';
                    } else {
                        $textoDias = 'En ' . $diasRestantes . ' días';
                        $claseEstado = 'normal';
                    }
                ?>
                <div class="vaccine-item">
                    <div class="vaccine-main">
                        <div class="cow-cell">
                            <div class="cow-avatar"><?php echo strtoupper(substr($vacuna['nombre_vaca'], 0, 1)); ?></div>
                            <div class="vaccine-copy">
                                <span class="cow-name"><?php echo htmlspecialchars($vacuna['nombre_vaca']); ?></span>
                                <span class="vaccine-name"><?php echo htmlspecialchars($vacuna['tipo_vacuna']); ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="vaccine-meta">
                        <span class="fecha-tag"><?php echo date('d/m/Y', strtotime($vacuna['fecha_programada'])); ?></span>
                        <span class="status-pill <?php echo $claseEstado; ?>"><?php echo $textoDias; ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="empty-state">
                <p>No hay vacunas pendientes próximas.</p>
                <a href="vacunaciones.html" class="empty-link">Programar una vacuna</a>
            </div>
            <?php endif; ?>
        </div>

    </div>

</div><!-- fin .main -->

<script>
/* GRÁFICO */
(function(){
    var isDark = !document.documentElement.classList.contains('light');
    var ctx = document.getElementById('graficoVacas').getContext('2d');

    var accentColor  = isDark ? '#5BAF59' : '#3D9C3A';
    var accentFill   = isDark ? 'rgba(91,175,89,0.15)' : 'rgba(61,156,58,0.12)';
    var gridColor    = isDark ? 'rgba(255,255,255,0.04)' : 'rgba(0,0,0,0.05)';
    var tickColor    = isDark ? '#485644' : '#7A9474';
    var tooltipBg    = isDark ? '#1A2119' : '#FFFFFF';
    var tooltipColor = isDark ? '#DDE8D9' : '#1A2418';

    var chart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($nombres); ?>,
            datasets: [{
                label: 'Litros',
                data: <?php echo json_encode($litros); ?>,
                backgroundColor: accentFill,
                borderColor: accentColor,
                borderWidth: 1.5,
                borderRadius: 6,
                borderSkipped: false,
                hoverBackgroundColor: isDark ? 'rgba(91,175,89,0.28)' : 'rgba(61,156,58,0.22)'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: tooltipBg,
                    titleColor: tickColor,
                    bodyColor: tooltipColor,
                    borderColor: isDark ? 'rgba(255,255,255,0.08)' : 'rgba(0,0,0,0.08)',
                    borderWidth: 1,
                    padding: 10,
                    cornerRadius: 8,
                    callbacks: {
                        label: function(ctx){ return ' ' + ctx.parsed.y + ' L'; }
                    }
                }
            },
            scales: {
                x: {
                    ticks: { color: tickColor, font: { family: "'Plus Jakarta Sans'", size: 11 } },
                    grid: { display: false },
                    border: { color: 'transparent' }
                },
                y: {
                    beginAtZero: true,
                    ticks: { color: tickColor, font: { family: "'DM Mono'", size: 11 } },
                    grid: { color: gridColor },
                    border: { color: 'transparent', dash: [4,4] }
                }
            },
            animation: { duration: 800, easing: 'easeOutQuart' }
        }
    });

    window._updateDashChart = function(light) {
        var ac = light ? '#3D9C3A' : '#5BAF59';
        var af = light ? 'rgba(61,156,58,0.12)' : 'rgba(91,175,89,0.15)';
        var gc = light ? 'rgba(0,0,0,0.05)' : 'rgba(255,255,255,0.04)';
        var tc = light ? '#7A9474' : '#485644';
        chart.data.datasets[0].backgroundColor = af;
        chart.data.datasets[0].borderColor = ac;
        chart.options.scales.x.ticks.color = tc;
        chart.options.scales.y.ticks.color = tc;
        chart.options.scales.y.grid.color  = gc;
        chart.update();
    };
})();

/* THEME TOGGLE */
function toggleTheme(){
    var html = document.documentElement;
    html.classList.toggle('light');
    var isLight = html.classList.contains('light');
    try { localStorage.setItem('acTheme', isLight ? 'light' : 'dark'); } catch(e){}
    if (window._updateDashChart) window._updateDashChart(isLight);
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


