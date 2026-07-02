<?php
require_once("../Config/conexion.php");
require_login();
$con = conexion();

date_default_timezone_set('America/Bogota');

$usuario_id = current_user_id();
$precioLitro = isset($_GET['precio_litro']) ? input_float($_GET, 'precio_litro', 0, 1000000, false) : 0;
$precioLitro = $precioLitro > 0 ? $precioLitro : 0;

$registros = [];
$registrosQuery = db_result($con, "
    SELECT fecha, litros
    FROM registroleche
    WHERE usuario_id = ?
    ORDER BY fecha ASC, id ASC
", "i", [$usuario_id]);

while ($row = mysqli_fetch_assoc($registrosQuery)) {
    $registros[] = [
        'fecha' => $row['fecha'],
        'litros' => (float) $row['litros']
    ];
}

$quincenas = [];
$fechaInicioControl = null;
$fechaUltimoRegistro = null;
$quincenaActual = null;
$litrosAcumulados = 0;

if (!empty($registros)) {
    $fechaInicioControl = new DateTime($registros[0]['fecha']);
    $fechaUltimoRegistro = new DateTime($registros[count($registros) - 1]['fecha']);
    $hoy = new DateTime(date('Y-m-d'));
    $fechaLimite = $fechaUltimoRegistro > $hoy ? clone $fechaUltimoRegistro : clone $hoy;

    $indice = 0;
    $cursor = clone $fechaInicioControl;

    while ($cursor <= $fechaLimite) {
        $inicioPeriodo = clone $cursor;
        $finPeriodo = clone $cursor;
        $finPeriodo->modify('+14 days');

        $quincenas[$indice] = [
            'numero' => $indice + 1,
            'inicio' => $inicioPeriodo->format('Y-m-d'),
            'fin' => $finPeriodo->format('Y-m-d'),
            'litros' => 0,
            'registros' => 0,
            'estado' => ($hoy >= $inicioPeriodo && $hoy <= $finPeriodo) ? 'actual' : (($hoy > $finPeriodo) ? 'cerrada' : 'proxima')
        ];

        if ($hoy >= $inicioPeriodo && $hoy <= $finPeriodo) {
            $quincenaActual = $indice;
        }

        $cursor->modify('+15 days');
        $indice++;
    }

    foreach ($registros as $registro) {
        $fechaRegistro = new DateTime($registro['fecha']);
        $dias = (int) $fechaInicioControl->diff($fechaRegistro)->format('%a');
        $indicePeriodo = (int) floor($dias / 15);

        if (!isset($quincenas[$indicePeriodo])) {
            continue;
        }

        $quincenas[$indicePeriodo]['litros'] += $registro['litros'];
        $quincenas[$indicePeriodo]['registros']++;
        $litrosAcumulados += $registro['litros'];
    }

    foreach ($quincenas as &$quincena) {
        $quincena['pago_estimado'] = $precioLitro > 0 ? $quincena['litros'] * $precioLitro : 0;
    }
    unset($quincena);
}

$totalQuincenas = count($quincenas);
$resumenActual = ($quincenaActual !== null && isset($quincenas[$quincenaActual])) ? $quincenas[$quincenaActual] : null;
$ejemploSiguienteInicio = null;
$ejemploSiguienteFin = null;

if ($fechaInicioControl) {
    $ejemploSiguienteInicio = clone $fechaInicioControl;
    $ejemploSiguienteInicio->modify('+15 days');
    $ejemploSiguienteFin = clone $ejemploSiguienteInicio;
    $ejemploSiguienteFin->modify('+14 days');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgroControl | Historial Quincenal de Leche</title>
    <link rel="stylesheet" href="../Css/layout-base.css">
    <link rel="stylesheet" href="../Css/Dashboard.css">
    <link rel="stylesheet" href="../Css/historial_quincenas_leche.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet"/>
</head>
<body>
<?php include 'sidebar.php'; ?>

<div class="main">
    <div class="topbar">
        <div class="topbar-left">
            <div class="topbar-breadcrumb">Principal / Produccion / Historial Quincenas</div>
            <div class="topbar-title">Historial para quincenas de pago de leche</div>
        </div>
        <div class="topbar-right">
            <form method="GET" class="filtro-form">
                <div class="filtro-wrap">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 1v22"/><path d="M17 5H9.5a3.5 3.5 0 000 7H14.5a3.5 3.5 0 010 7H6"/></svg>
                    <input type="number" name="precio_litro" value="<?php echo $precioLitro > 0 ? htmlspecialchars((string) $precioLitro) : ''; ?>" step="0.01" min="0" placeholder="Valor litro">
                    <button type="submit" class="btn-filtrar">Calcular</button>
                    <a href="historial_quincenas_leche.php" class="btn-reset">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </a>
                </div>
            </form>
            <a href="produccion_lechera.php" class="back-link">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M15 18l-6-6 6-6"/><path d="M21 12H9"/></svg>
                Volver a produccion
            </a>
        </div>
    </div>

    <div class="page-shell">
        <?php if (!empty($registros)): ?>
        <div class="intro-card">
            <div class="intro-kicker">Control general</div>
            <h1>Quincenas calculadas cada 15 dias desde tu primer registro</h1>
            <p>
                El sistema toma la primera fecha registrada de produccion, arma periodos de 15 dias corridos
                y te muestra cuanto leche sacaste en cada quincena para que revises lo que te deben pagar.
            </p>
            <div class="intro-meta">
                <span class="meta-pill">Inicio: <?php echo $fechaInicioControl ? $fechaInicioControl->format('d/m/Y') : '-'; ?></span>
                <span class="meta-pill">Ultimo registro: <?php echo $fechaUltimoRegistro ? $fechaUltimoRegistro->format('d/m/Y') : '-'; ?></span>
                <span class="meta-pill">Total quincenas: <?php echo $totalQuincenas; ?></span>
            </div>
            <div class="cut-explanation">
                <strong>Corte exacto:</strong>
                La quincena 1 va del <?php echo $fechaInicioControl ? $fechaInicioControl->format('d/m/Y') : '-'; ?>
                al <?php echo $fechaInicioControl ? (clone $fechaInicioControl)->modify('+14 days')->format('d/m/Y') : '-'; ?>.
                La quincena 2 va del <?php echo $ejemploSiguienteInicio ? $ejemploSiguienteInicio->format('d/m/Y') : '-'; ?>
                al <?php echo $ejemploSiguienteFin ? $ejemploSiguienteFin->format('d/m/Y') : '-'; ?>.
                Asi sigue siempre en bloques exactos de 15 dias.
            </div>
        </div>

        <div class="current-pay-banner">
            <div class="current-pay-label">Hoy vas acumulado en esta quincena</div>
            <div class="current-pay-value">
                <?php echo $precioLitro > 0 && $resumenActual ? '$' . number_format($resumenActual['pago_estimado'], 0, ',', '.') : '--'; ?>
            </div>
            <div class="current-pay-meta">
                <?php if ($resumenActual): ?>
                    <?php echo number_format($resumenActual['litros'], 1); ?> litros del <?php echo date('d/m/Y', strtotime($resumenActual['inicio'])); ?> al <?php echo date('d/m/Y', strtotime($resumenActual['fin'])); ?>
                <?php else: ?>
                    No hay una quincena activa en este momento.
                <?php endif; ?>
                <?php if ($precioLitro <= 0): ?>
                    Ingresa el valor del litro para ver el pago estimado.
                <?php endif; ?>
            </div>
        </div>

        <div class="stats-grid stats-grid-quincena">
            <div class="stat-card">
                <div class="stat-top">
                    <div class="stat-label">Inicio del control</div>
                    <div class="stat-icon si-blue">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/></svg>
                    </div>
                </div>
                <div class="stat-value stat-value-date"><?php echo $fechaInicioControl->format('d/m'); ?></div>
                <div class="stat-meta"><?php echo $fechaInicioControl->format('Y'); ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-top">
                    <div class="stat-label">Periodo actual</div>
                    <div class="stat-icon si-green">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 2C6 2 3 9 3 14a9 9 0 0018 0c0-5-3-12-9-12z"/></svg>
                    </div>
                </div>
                <div class="stat-value"><?php echo $resumenActual ? number_format($resumenActual['litros'], 1) : '0.0'; ?></div>
                <div class="stat-meta"><?php echo $resumenActual ? 'del ' . date('d/m', strtotime($resumenActual['inicio'])) . ' al ' . date('d/m', strtotime($resumenActual['fin'])) : 'sin periodo activo'; ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-top">
                    <div class="stat-label">Pago estimado actual</div>
                    <div class="stat-icon si-amber">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 1v22"/><path d="M17 5H9.5a3.5 3.5 0 000 7H14.5a3.5 3.5 0 010 7H6"/></svg>
                    </div>
                </div>
                <div class="stat-value stat-value-payment"><?php echo $precioLitro > 0 && $resumenActual ? '$' . number_format($resumenActual['pago_estimado'], 0, ',', '.') : '--'; ?></div>
                <div class="stat-meta"><?php echo $precioLitro > 0 ? 'segun valor por litro' : 'ingresa el valor del litro'; ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-top">
                    <div class="stat-label">Litros acumulados</div>
                    <div class="stat-icon si-green">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 2C6 2 3 9 3 14a9 9 0 0018 0c0-5-3-12-9-12z"/></svg>
                    </div>
                </div>
                <div class="stat-value"><?php echo number_format($litrosAcumulados, 1); ?></div>
                <div class="stat-meta">desde el primer registro</div>
            </div>
        </div>

        <div class="history-table-card payment-history-card">
            <div class="toolbar-inline">
                <span class="table-heading">Historial para quincenas de pago de leche</span>
                <span class="search-badge">Bloques de 15 dias desde <?php echo $fechaInicioControl->format('d/m/Y'); ?></span>
            </div>
            <div class="table-scroll">
                <table>
                    <thead>
                        <tr>
                            <th>Quincena</th>
                            <th>Desde</th>
                            <th>Hasta</th>
                            <th>Dias del corte</th>
                            <th>Estado</th>
                            <th>Litros</th>
                            <th>Registros</th>
                            <th>Pago estimado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_reverse($quincenas) as $quincena): ?>
                        <tr class="<?php echo $quincena['estado'] === 'actual' ? 'row-actual' : ''; ?>">
                            <td><span class="chip-date">Q<?php echo (int) $quincena['numero']; ?></span></td>
                            <td><?php echo date('d/m/Y', strtotime($quincena['inicio'])); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($quincena['fin'])); ?></td>
                            <td>15 dias exactos</td>
                            <td>
                                <span class="status-pill status-<?php echo htmlspecialchars($quincena['estado']); ?>">
                                    <?php echo $quincena['estado'] === 'actual' ? 'En curso' : ($quincena['estado'] === 'cerrada' ? 'Cerrada' : 'Proxima'); ?>
                                </span>
                            </td>
                            <td><span class="litros-strong"><?php echo rtrim(rtrim(number_format($quincena['litros'], 1, '.', ''), '0'), '.'); ?> <span>L</span></span></td>
                            <td><?php echo (int) $quincena['registros']; ?></td>
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
        </div>
        <?php else: ?>
        <div class="empty-history">
            <h3>Aun no hay produccion registrada</h3>
            <p>Cuando empieces a guardar litros, aqui apareceran las quincenas calculadas automaticamente cada 15 dias.</p>
            <a href="produccion_lechera.php" class="btn-primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Ir a produccion
            </a>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
function toggleTheme() {
    document.documentElement.classList.toggle('light');
    localStorage.setItem('acTheme', document.documentElement.classList.contains('light') ? 'light' : 'dark');
}

(function() {
    if (localStorage.getItem('acTheme') === 'light') {
        document.documentElement.classList.add('light');
    }
})();
</script>
</body>
</html>
