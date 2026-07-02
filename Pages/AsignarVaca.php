<?php
require_once("../Config/conexion.php");
require_login();
require_csrf();
$con = conexion();

try {
    $uid = current_user_id();
    $vacaId = input_int($_POST, 'vaca_id', 1);
    $potreroId = input_int($_POST, 'potrero_id', 1);
    $mangaNum = !empty($_POST['manga_num']) ? input_int($_POST, 'manga_num', 1, 100) : null;
    $usuario = $_SESSION['nombre'] ?? 'Sistema';
    $fechaEntrada = input_date($_POST, 'fecha_entrada');

    $vacaValida = db_value($con, "SELECT id FROM vacas WHERE id = ? AND usuario_id = ? LIMIT 1", "ii", [$vacaId, $uid]);
    if (!$vacaValida) {
        header("Location: potrero.php?error=vaca_no_encontrada");
        exit;
    }

    $activa = db_value($con, "SELECT id FROM asignaciones WHERE vaca_id = ? AND fecha_salida IS NULL LIMIT 1", "i", [$vacaId]);
    if ($activa) {
        header("Location: potrero.php?error=vaca_ya_asignada");
        exit;
    }

    $potrero = db_one($con, "SELECT capacidad_max, tiene_mangas, num_mangas, tamaño_manga, tipo_pasto FROM potreros WHERE id = ? AND usuario_id = ? LIMIT 1", "ii", [$potreroId, $uid]);
    if (!$potrero) {
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
    $m2 = $m2PorVaca[$potrero['tipo_pasto']] ?? 200;
    $tamManga = (float)$potrero['tamaño_manga'];
    $numMangas = (int)$potrero['num_mangas'] > 0 ? (int)$potrero['num_mangas'] : 1;
    $capTotal = (int)$potrero['capacidad_max'];

    if ((int)$potrero['tiene_mangas'] && $mangaNum) {
        $capPorManga = ($tamManga > 0 && $m2 > 0) ? (int)floor($tamManga / $m2) : (int)floor($capTotal / $numMangas);
        if ($capPorManga <= 0) {
            $capPorManga = $capTotal;
        }
        $vacasEnManga = (int)db_value($con, "SELECT COUNT(*) FROM asignaciones WHERE potrero_id = ? AND manga_num = ? AND fecha_salida IS NULL", "ii", [$potreroId, $mangaNum]);
        if ($vacasEnManga >= $capPorManga) {
            header("Location: potrero.php?error=manga_llena");
            exit;
        }
    } else {
        $vacasTotal = (int)db_value($con, "SELECT COUNT(*) FROM asignaciones WHERE potrero_id = ? AND fecha_salida IS NULL", "i", [$potreroId]);
        if ($vacasTotal >= $capTotal) {
            header("Location: potrero.php?error=potrero_lleno");
            exit;
        }
    }

    db_execute(
        $con,
        "INSERT INTO asignaciones (vaca_id, potrero_id, manga_num, usuario, fecha_entrada) VALUES (?, ?, ?, ?, ?)",
        "iiiss",
        [$vacaId, $potreroId, $mangaNum, $usuario, $fechaEntrada]
    );

    $vacaNombre = db_value($con, "SELECT nombre FROM vacas WHERE id = ? AND usuario_id = ? LIMIT 1", "ii", [$vacaId, $uid]) ?? '';
    $potreroNombre = db_value($con, "SELECT nombre FROM potreros WHERE id = ? AND usuario_id = ? LIMIT 1", "ii", [$potreroId, $uid]) ?? '';
    header("Location: potrero.php?" . http_build_query([
        'ok' => 'asignacion',
        'vaca' => $vacaNombre,
        'potrero' => $potreroNombre,
        'manga' => $mangaNum ?? '',
        'fecha' => $fechaEntrada,
        'usuario' => $usuario,
    ]));
    exit;
} catch (Throwable $e) {
    app_log($e->getMessage());
    header("Location: potrero.php?error=db");
    exit;
}
?>
