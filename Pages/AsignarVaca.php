<?php
/**
 * AsignarVaca.php
 * Registra una nueva asignación de vaca a potrero.
 */

include("../Config/conexion.php");
$con = conexion();

$vaca_id      = (int)$_POST['vaca_id'];
$potrero_id   = (int)$_POST['potrero_id'];
$manga_num    = !empty($_POST['manga_num']) ? (int)$_POST['manga_num'] : null;
$usuario      = !empty($_POST['usuario'])   ? mysqli_real_escape_string($con, trim($_POST['usuario'])) : null;
$fecha_entrada= mysqli_real_escape_string($con, $_POST['fecha_entrada']);

// Validación básica
if (!$vaca_id || !$potrero_id || !$fecha_entrada || !$usuario) {
    header("Location: potrero.php?error=datos_incompletos");
    exit;
}

// Verificar que la vaca no tenga asignación activa
$checkRes = mysqli_query($con, "SELECT id FROM asignaciones WHERE vaca_id = $vaca_id AND fecha_salida IS NULL LIMIT 1");
if (mysqli_num_rows($checkRes) > 0) {
    header("Location: potrero.php?error=vaca_ya_asignada");
    exit;
}

// Obtener datos completos del potrero
$potreroRes = mysqli_fetch_assoc(mysqli_query($con,
    "SELECT capacidad_max, tiene_mangas, num_mangas, tamaño_manga, tipo_pasto FROM potreros WHERE id = $potrero_id LIMIT 1"
));

if (!$potreroRes) {
    header("Location: potrero.php?error=potrero_no_encontrado");
    exit;
}

// m² necesarios por vaca según tipo de pasto (misma tabla que el frontend)
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

$tipoPasto  = $potreroRes['tipo_pasto'];
$m2         = $m2PorVaca[$tipoPasto] ?? 200;
$tamManga   = (float)$potreroRes['tamaño_manga'];
$numMangas  = (int)$potreroRes['num_mangas'] > 0 ? (int)$potreroRes['num_mangas'] : 1;
$capTotal   = (int)$potreroRes['capacidad_max'];

if ($potreroRes['tiene_mangas'] && $manga_num) {
    // ── VALIDACIÓN POR MANGA ──
    // Calcular capacidad de la manga igual que el frontend
    if ($tamManga > 0 && $m2 > 0) {
        $capPorManga = (int)floor($tamManga / $m2);
    } else {
        $capPorManga = (int)floor($capTotal / $numMangas);
    }
    // Si la fórmula da 0, usamos la capacidad total como fallback
    if ($capPorManga <= 0) {
        $capPorManga = $capTotal;
    }

    $vacasEnManga = (int)mysqli_fetch_row(mysqli_query($con,
        "SELECT COUNT(*) FROM asignaciones
         WHERE potrero_id  = $potrero_id
           AND manga_num   = $manga_num
           AND fecha_salida IS NULL"
    ))[0];

    if ($vacasEnManga >= $capPorManga) {
        header("Location: potrero.php?error=manga_llena");
        exit;
    }
} else {
    // ── VALIDACIÓN POR POTRERO COMPLETO (sin mangas) ──
    $vacasTotal = (int)mysqli_fetch_row(mysqli_query($con,
        "SELECT COUNT(*) FROM asignaciones
         WHERE potrero_id = $potrero_id AND fecha_salida IS NULL"
    ))[0];

    if ($vacasTotal >= $capTotal) {
        header("Location: potrero.php?error=potrero_lleno");
        exit;
    }
}

$mangaVal = $manga_num ? $manga_num : "NULL";
$sql = "INSERT INTO asignaciones (vaca_id, potrero_id, manga_num, usuario, fecha_entrada)
        VALUES ($vaca_id, $potrero_id, $mangaVal, '$usuario', '$fecha_entrada')";

if (mysqli_query($con, $sql)) {
    $vacaNombre    = mysqli_fetch_row(mysqli_query($con, "SELECT nombre FROM vacas WHERE id = $vaca_id"))[0] ?? '';
    $potreroNombre = mysqli_fetch_row(mysqli_query($con, "SELECT nombre FROM potreros WHERE id = $potrero_id"))[0] ?? '';
    $params = http_build_query([
        'ok'      => 'asignacion',
        'vaca'    => $vacaNombre,
        'potrero' => $potreroNombre,
        'manga'   => $manga_num ?? '',
        'fecha'   => $_POST['fecha_entrada'],
        'usuario' => $_POST['usuario'],
    ]);
    header("Location: potrero.php?" . $params);
} else {
    header("Location: potrero.php?error=db");
}
exit;