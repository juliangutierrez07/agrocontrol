<?php
require_once("../Config/conexion.php");
require_login();
require_csrf();
$con = conexion();

try {
    $uid          = current_user_id();
    $usuario      = $_SESSION['nombre'] ?? 'Sistema';
    $potreroId    = input_int($_POST, 'potrero_id', 1);
    $mangaNum     = !empty($_POST['manga_num']) ? input_int($_POST, 'manga_num', 1, 100) : null;
    $fechaEntrada = input_date($_POST, 'fecha_entrada');

    // ── IDs de vacas (selección múltiple): normalizar a enteros únicos ──
    $rawIds = $_POST['vacas_ids'] ?? [];
    if (!is_array($rawIds)) {
        $rawIds = [$rawIds];
    }
    $vacaIds = [];
    foreach ($rawIds as $rid) {
        $n = filter_var($rid, FILTER_VALIDATE_INT);
        if ($n !== false && $n > 0) {
            $vacaIds[$n] = true; // clave => dedupe
        }
    }
    $vacaIds = array_keys($vacaIds);
    if (empty($vacaIds)) {
        header("Location: potrero.php?error=sin_vacas");
        exit;
    }

    // ── Potrero válido y del usuario ──
    $potrero = db_one(
        $con,
        "SELECT capacidad_max, tiene_mangas, num_mangas, tamaño_manga, tipo_pasto
         FROM potreros WHERE id = ? AND usuario_id = ? LIMIT 1",
        "ii",
        [$potreroId, $uid]
    );
    if (!$potrero) {
        header("Location: potrero.php?error=potrero_no_encontrado");
        exit;
    }

    // ── Capacidad restante del destino (manga específica o potrero completo) ──
    $m2PorVaca = [
        'brachiaria_humidicola' => 250,
        'brachiaria_decumbens'  => 200,
        'brachiaria_dictyoneura' => 230,
        'brachiaria_ruziziensis' => 180,
        'pasto_elefante'        => 100,
        'pasto_para'            => 300,
        'pasto_angola'          => 250,
        'pasto_natural'         => 400,
    ];
    $m2        = $m2PorVaca[$potrero['tipo_pasto']] ?? 200;
    $tamManga  = (float)$potrero['tamaño_manga'];
    $numMangas = (int)$potrero['num_mangas'] > 0 ? (int)$potrero['num_mangas'] : 1;
    $capTotal  = (int)$potrero['capacidad_max'];

    if ((int)$potrero['tiene_mangas'] && $mangaNum) {
        $capDestino = ($tamManga > 0 && $m2 > 0) ? (int)floor($tamManga / $m2) : (int)floor($capTotal / $numMangas);
        if ($capDestino <= 0) {
            $capDestino = $capTotal;
        }
        $ocupadas = (int)db_value(
            $con,
            "SELECT COUNT(*) FROM asignaciones WHERE potrero_id = ? AND manga_num = ? AND fecha_salida IS NULL",
            "ii",
            [$potreroId, $mangaNum]
        );
    } else {
        $capDestino = $capTotal;
        $ocupadas   = (int)db_value(
            $con,
            "SELECT COUNT(*) FROM asignaciones WHERE potrero_id = ? AND fecha_salida IS NULL",
            "i",
            [$potreroId]
        );
    }
    $cupoRestante = max(0, $capDestino - $ocupadas);

    // ── Filtrar: solo vacas del usuario y que NO estén ya asignadas ──
    // (las que no cumplen se omiten, no rompen el resto — decisión de UX)
    $elegibles   = [];
    $yaAsignadas = 0;
    foreach ($vacaIds as $vid) {
        $esSuya = db_value($con, "SELECT id FROM vacas WHERE id = ? AND usuario_id = ? LIMIT 1", "ii", [$vid, $uid]);
        if (!$esSuya) {
            continue; // no pertenece al usuario / no existe → se omite en silencio
        }
        $activa = db_value($con, "SELECT id FROM asignaciones WHERE vaca_id = ? AND fecha_salida IS NULL LIMIT 1", "i", [$vid]);
        if ($activa) {
            $yaAsignadas++;
            continue; // ya asignada → se omite
        }
        $elegibles[] = (int)$vid;
    }

    // ── Aplicar cupo: se asignan las que quepan; el resto queda "sin cupo" ──
    $aAsignar = array_slice($elegibles, 0, $cupoRestante);
    $sinCupo  = count($elegibles) - count($aAsignar);

    if (empty($aAsignar)) {
        // Nada que asignar: distinguir el motivo para un mensaje claro
        if ($cupoRestante <= 0) {
            header("Location: potrero.php?error=" . ($mangaNum ? 'manga_llena' : 'potrero_lleno'));
        } else {
            header("Location: potrero.php?error=todas_ya_asignadas");
        }
        exit;
    }

    // ── Insertar TODAS las asignaciones dentro de una transacción ──
    // Si algún INSERT falla, se revierte todo (no quedan datos a medias).
    $asignadas = 0;
    mysqli_begin_transaction($con);
    try {
        foreach ($aAsignar as $vid) {
            db_execute(
                $con,
                "INSERT INTO asignaciones (vaca_id, potrero_id, manga_num, usuario, fecha_entrada)
                 VALUES (?, ?, ?, ?, ?)",
                "iiiss",
                [$vid, $potreroId, $mangaNum, $usuario, $fechaEntrada]
            );
            $asignadas++;
        }
        mysqli_commit($con);
    } catch (Throwable $e) {
        mysqli_rollback($con);
        throw $e; // → catch general → error=db
    }

    $potreroNombre = db_value($con, "SELECT nombre FROM potreros WHERE id = ? AND usuario_id = ? LIMIT 1", "ii", [$potreroId, $uid]) ?? '';
    header("Location: potrero.php?" . http_build_query([
        'ok'           => 'asignacion',
        'count'        => $asignadas,
        'ya_asignadas' => $yaAsignadas,
        'sin_cupo'     => $sinCupo,
        'potrero'      => $potreroNombre,
        'manga'        => $mangaNum ?? '',
        'fecha'        => $fechaEntrada,
        'usuario'      => $usuario,
    ]));
    exit;
} catch (Throwable $e) {
    app_log($e->getMessage());
    header("Location: potrero.php?error=db");
    exit;
}
?>
