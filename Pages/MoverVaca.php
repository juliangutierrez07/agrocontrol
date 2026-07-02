<?php
require_once("../Config/conexion.php");
require_login();
require_csrf();
$con = conexion();

try {
    $uid = current_user_id();
    $asignacionId = input_int($_POST, 'asignacion_id', 1);
    $nuevoPotreroId = input_int($_POST, 'nuevo_potrero_id', 1);
    $mangaNum = !empty($_POST['manga_num']) ? input_int($_POST, 'manga_num', 1, 100) : null;
    $usuario = $_SESSION['nombre'] ?? 'Sistema';
    $fechaSalida = input_date($_POST, 'fecha_salida');

    $actual = db_one(
        $con,
        "SELECT a.vaca_id, a.potrero_id, a.manga_num, v.nombre AS vaca_nombre, p.nombre AS potrero_nombre
         FROM asignaciones a
         JOIN vacas v ON v.id = a.vaca_id
         JOIN potreros p ON p.id = a.potrero_id
         WHERE a.id = ? AND a.fecha_salida IS NULL AND v.usuario_id = ? LIMIT 1",
        "ii",
        [$asignacionId, $uid]
    );
    if (!$actual) {
        header("Location: potrero.php?error=asignacion_no_encontrada");
        exit;
    }

    $vacaId = (int)$actual['vaca_id'];
    $potreroActual = (int)$actual['potrero_id'];
    if ($potreroActual === $nuevoPotreroId && (string)$actual['manga_num'] === (string)$mangaNum) {
        header("Location: potrero.php?error=sin_cambio");
        exit;
    }

    $destino = db_one($con, "SELECT nombre, capacidad_max, tiene_mangas, num_mangas, tamaño_manga, tipo_pasto FROM potreros WHERE id = ? AND usuario_id = ? LIMIT 1", "ii", [$nuevoPotreroId, $uid]);
    if (!$destino) {
        header("Location: potrero.php?error=potrero_no_encontrado");
        exit;
    }

    $m2PorVaca = [
        'brachiaria_humidicola' => 250,
        'brachiaria_decumbens' => 200,
        'brachiaria_dictyoneura' => 230,
        'brachiaria_ruziziensis' => 180,
        'pasto_elefante' => 100,
        'pasto_para' => 300,
        'pasto_angola' => 250,
        'pasto_natural' => 400,
    ];
    $m2 = $m2PorVaca[$destino['tipo_pasto']] ?? 200;
    $tamManga = (float)$destino['tamaño_manga'];
    $numMangas = (int)$destino['num_mangas'] > 0 ? (int)$destino['num_mangas'] : 1;
    $capTotal = (int)$destino['capacidad_max'];

    if ((int)$destino['tiene_mangas'] && $mangaNum) {
        $capPorManga = ($tamManga > 0 && $m2 > 0) ? (int)floor($tamManga / $m2) : (int)floor($capTotal / $numMangas);
        if ($capPorManga <= 0) {
            $capPorManga = $capTotal;
        }
        $vacasEnManga = (int)db_value(
            $con,
            "SELECT COUNT(*) FROM asignaciones WHERE potrero_id = ? AND manga_num = ? AND fecha_salida IS NULL AND id != ?",
            "iii",
            [$nuevoPotreroId, $mangaNum, $asignacionId]
        );
        if ($vacasEnManga >= $capPorManga) {
            header("Location: potrero.php?error=manga_llena");
            exit;
        }
    } elseif ($potreroActual !== $nuevoPotreroId) {
        $vacasTotal = (int)db_value($con, "SELECT COUNT(*) FROM asignaciones WHERE potrero_id = ? AND fecha_salida IS NULL", "i", [$nuevoPotreroId]);
        if ($vacasTotal >= $capTotal) {
            header("Location: potrero.php?error=potrero_lleno");
            exit;
        }
    }

    mysqli_begin_transaction($con);
    db_execute($con, "UPDATE asignaciones SET fecha_salida = ?, usuario = ? WHERE id = ?", "ssi", [$fechaSalida, $usuario, $asignacionId]);
    db_execute(
        $con,
        "INSERT INTO asignaciones (vaca_id, potrero_id, manga_num, usuario, fecha_entrada) VALUES (?, ?, ?, ?, ?)",
        "iiiss",
        [$vacaId, $nuevoPotreroId, $mangaNum, $usuario, $fechaSalida]
    );
    mysqli_commit($con);

    header("Location: potrero.php?" . http_build_query([
        'ok' => 'movimiento',
        'vaca' => $actual['vaca_nombre'],
        'potrero' => $destino['nombre'],
        'manga' => $mangaNum ?? '',
        'fecha' => $fechaSalida,
        'usuario' => $usuario,
    ]));
    exit;
} catch (Throwable $e) {
    if (isset($con) && $con instanceof mysqli) {
        mysqli_rollback($con);
    }
    app_log($e->getMessage());
    header("Location: potrero.php?error=db");
    exit;
}
?>
