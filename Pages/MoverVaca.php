<?php
/**
 * MoverVaca.php
 * Cierra la asignación actual y crea una nueva.
 */

include("../Config/conexion.php");
$con = conexion();

$asignacion_id    = (int)$_POST['asignacion_id'];
$nuevo_potrero_id = (int)$_POST['nuevo_potrero_id'];
$manga_num        = !empty($_POST['manga_num']) ? (int)$_POST['manga_num'] : null;
$usuario          = !empty($_POST['usuario'])   ? mysqli_real_escape_string($con, trim($_POST['usuario'])) : null;
$fecha_salida     = mysqli_real_escape_string($con, $_POST['fecha_salida']);

if (!$asignacion_id || !$nuevo_potrero_id || !$fecha_salida || !$usuario) {
    header("Location: potrero.php?error=datos_incompletos");
    exit;
}

// Obtener la asignación actual
$asRes = mysqli_fetch_assoc(mysqli_query($con,
    "SELECT a.vaca_id, a.potrero_id, a.manga_num, v.nombre as vaca_nombre, p.nombre as potrero_nombre
     FROM asignaciones a
     JOIN vacas v ON v.id = a.vaca_id
     JOIN potreros p ON p.id = a.potrero_id
     WHERE a.id = $asignacion_id AND a.fecha_salida IS NULL LIMIT 1"
));
if (!$asRes) {
    header("Location: potrero.php?error=asignacion_no_encontrada");
    exit;
}
$vaca_id        = (int)$asRes['vaca_id'];
$potrero_actual = (int)$asRes['potrero_id'];
$manga_actual   = $asRes['manga_num'];

// Si es el mismo potrero Y la misma manga → no hay cambio
if ($potrero_actual === $nuevo_potrero_id && (string)$manga_actual === (string)$manga_num) {
    header("Location: potrero.php?error=sin_cambio");
    exit;
}

// Obtener datos del potrero destino
$potreroDestRes = mysqli_fetch_assoc(mysqli_query($con,
    "SELECT nombre, capacidad_max, tiene_mangas, num_mangas, tamaño_manga, tipo_pasto FROM potreros WHERE id = $nuevo_potrero_id LIMIT 1"
));
if (!$potreroDestRes) {
    header("Location: potrero.php?error=potrero_no_encontrado");
    exit;
}
$potrero_destino_nombre = $potreroDestRes['nombre'];

// m² necesarios por vaca según tipo de pasto
$m2PorVaca = [
    'brachiaria_humidicola'   => 250,
    'brachiaria_decumbens'    => 200,
    'brachiaria_dictyoneura'  => 230,
    'brachiaria_ruziziensis'  => 180,
    'pasto_elefante'          => 100,
    'pasto_para'              => 300,
    'pasto_angola'            => 250,
    'pasto_natural'           => 400,
];

$tipoPasto = $potreroDestRes['tipo_pasto'];
$m2        = $m2PorVaca[$tipoPasto] ?? 200;
$tamManga  = (float)$potreroDestRes['tamaño_manga'];
$numMangas = (int)$potreroDestRes['num_mangas'] > 0 ? (int)$potreroDestRes['num_mangas'] : 1;
$capTotal  = (int)$potreroDestRes['capacidad_max'];

// Verificar capacidad destino
if ($potreroDestRes['tiene_mangas'] && $manga_num) {
    // ── VALIDACIÓN POR MANGA ──
    if ($tamManga > 0 && $m2 > 0) {
        $capPorManga = (int)floor($tamManga / $m2);
    } else {
        $capPorManga = (int)floor($capTotal / $numMangas);
    }
    if ($capPorManga <= 0) {
        $capPorManga = $capTotal;
    }

    // Excluir la asignación actual al contar (la vaca se va a mover)
    $vacasEnManga = (int)mysqli_fetch_row(mysqli_query($con,
        "SELECT COUNT(*) FROM asignaciones
         WHERE potrero_id  = $nuevo_potrero_id
           AND manga_num   = $manga_num
           AND fecha_salida IS NULL
           AND id          != $asignacion_id"
    ))[0];

    if ($vacasEnManga >= $capPorManga) {
        header("Location: potrero.php?error=manga_llena");
        exit;
    }
} elseif ($potrero_actual !== $nuevo_potrero_id) {
    // ── VALIDACIÓN POR POTRERO COMPLETO (sin mangas) ──
    $vacasTotal = (int)mysqli_fetch_row(mysqli_query($con,
        "SELECT COUNT(*) FROM asignaciones
         WHERE potrero_id = $nuevo_potrero_id AND fecha_salida IS NULL"
    ))[0];

    if ($vacasTotal >= $capTotal) {
        header("Location: potrero.php?error=potrero_lleno");
        exit;
    }
}

mysqli_begin_transaction($con);
try {
    // 1. Cerrar asignación actual
    mysqli_query($con,
        "UPDATE asignaciones
         SET fecha_salida = '$fecha_salida', usuario = '$usuario'
         WHERE id = $asignacion_id"
    );

    // 2. Crear nueva asignación
    $mangaVal = $manga_num ? $manga_num : "NULL";
    mysqli_query($con,
        "INSERT INTO asignaciones (vaca_id, potrero_id, manga_num, usuario, fecha_entrada)
         VALUES ($vaca_id, $nuevo_potrero_id, $mangaVal, '$usuario', '$fecha_salida')"
    );

    mysqli_commit($con);

    $params = http_build_query([
        'ok'      => 'movimiento',
        'vaca'    => $asRes['vaca_nombre'],
        'potrero' => $potrero_destino_nombre,
        'manga'   => $manga_num ?? '',
        'fecha'   => $fecha_salida,
        'usuario' => $_POST['usuario'],
    ]);
    header("Location: potrero.php?" . $params);
} catch (Exception $e) {
    mysqli_rollback($con);
    header("Location: potrero.php?error=db");
}
exit;