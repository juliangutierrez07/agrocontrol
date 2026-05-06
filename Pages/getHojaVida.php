<?php
/**
 * getHojaVida.php
 * Devuelve JSON con toda la info de una vaca para la hoja de vida.
 * Llamado por fetch desde potrero.php
 * 
 * Parámetro GET: vaca_id (int)
 */

header('Content-Type: application/json; charset=utf-8');
include("../Config/conexion.php");
$con = conexion();

$vaca_id = (int)($_GET['vaca_id'] ?? 0);
if ($vaca_id <= 0) {
    echo json_encode(['error' => 'ID inválido']);
    exit;
}

/* ── 1. DATOS BÁSICOS DE LA VACA ── */
$sqlVaca = "SELECT * FROM vacas WHERE id = $vaca_id LIMIT 1";
$resVaca = mysqli_query($con, $sqlVaca);
if (!$resVaca || mysqli_num_rows($resVaca) === 0) {
    echo json_encode(['error' => 'Vaca no encontrada']);
    exit;
}
$vaca = mysqli_fetch_assoc($resVaca);

/* ── 2. UBICACIÓN ACTUAL ── */
$sqlUbic = "SELECT a.manga_num, a.fecha_entrada, p.nombre as potrero_nombre, p.id as potrero_id
            FROM asignaciones a
            JOIN potreros p ON p.id = a.potrero_id
            WHERE a.vaca_id = $vaca_id AND a.fecha_salida IS NULL
            LIMIT 1";
$resUbic = mysqli_query($con, $sqlUbic);
$ubicacion = $resUbic && mysqli_num_rows($resUbic) > 0
    ? mysqli_fetch_assoc($resUbic)
    : null;

/* ── 3. TRAYECTORIA — historial de asignaciones ── */
$sqlTray = "SELECT a.fecha_entrada, a.fecha_salida, a.manga_num, a.usuario,
                   p.nombre as potrero_nombre,
                   DATEDIFF(COALESCE(a.fecha_salida, NOW()), a.fecha_entrada) as dias
            FROM asignaciones a
            JOIN potreros p ON p.id = a.potrero_id
            WHERE a.vaca_id = $vaca_id
            ORDER BY a.fecha_entrada ASC";
$resTray = mysqli_query($con, $sqlTray);
$trayectoria = [];
while ($t = mysqli_fetch_assoc($resTray)) {
    $trayectoria[] = $t;
}

/* ── 4. ROTACIONES (tabla rotaciones) ── */
$sqlRot = "SELECT r.fecha_traslado, r.observacion,
                  po.nombre as potrero_origen,
                  pd.nombre as potrero_destino
           FROM rotaciones r
           JOIN potreros po ON po.id = r.potrero_origen_id
           JOIN potreros pd ON pd.id = r.potrero_destino_id
           WHERE r.vaca_id = $vaca_id
           ORDER BY r.fecha_traslado DESC
           LIMIT 20";
$resRot = mysqli_query($con, $sqlRot);
$rotaciones = [];
while ($r = mysqli_fetch_assoc($resRot)) {
    $rotaciones[] = $r;
}

/* ── 5. CONTEO DE PARTOS (desde rotaciones con observación que contenga 'parto') ── */
// Si tienes tabla de partos separada, cambia esta query
$sqlPartos = "SELECT COUNT(*) as total FROM rotaciones 
              WHERE vaca_id = $vaca_id 
              AND LOWER(observacion) LIKE '%parto%'";
$resPartos = mysqli_query($con, $sqlPartos);
$partosCalculados = $resPartos ? (int)mysqli_fetch_row($resPartos)[0] : 0;
$totalPartos = isset($vaca['partos']) && (int) $vaca['partos'] > 0
    ? (int) $vaca['partos']
    : $partosCalculados;

/* ── 6. VACUNACIONES ── */
$sqlVac = "SELECT v2.fecha_aplicada, v2.fecha_programada, v2.estado, v2.responsable,
                  tv.nombre as tipo_vacuna, v2.dosis_ml
           FROM vacunaciones v2
           JOIN tipos_vacuna tv ON tv.id = v2.tipo_vacuna_id
           WHERE v2.vaca_id = $vaca_id
           ORDER BY COALESCE(v2.fecha_aplicada, v2.fecha_programada) DESC
           LIMIT 10";
$resVac = mysqli_query($con, $sqlVac);
$vacunaciones = [];
if ($resVac) {
    while ($v2 = mysqli_fetch_assoc($resVac)) {
        $vacunaciones[] = $v2;
    }
}

/* ── RESPUESTA ── */
echo json_encode([
    'vaca'        => $vaca,
    'ubicacion'   => $ubicacion,
    'trayectoria' => $trayectoria,
    'rotaciones'  => $rotaciones,
    'partos'      => $totalPartos,
    'vacunaciones'=> $vacunaciones,
], JSON_UNESCAPED_UNICODE);
