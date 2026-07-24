<?php
define('AC_JSON_ENDPOINT', true);
require_once("../Config/conexion.php");
require_login();
$con = conexion();
$usuarioId = current_user_id();

try {
    $vacaId = input_int($_GET, 'vaca_id', 1);

    $vaca = db_one($con, "SELECT * FROM vacas WHERE id = ? AND usuario_id = ? LIMIT 1", "ii", [$vacaId, $usuarioId]);
    if (!$vaca) {
        json_response(['error' => 'Vaca no encontrada'], 404);
    }

    $ubicacion = db_one(
        $con,
        "SELECT a.manga_num, a.fecha_entrada, p.nombre AS potrero_nombre, p.id AS potrero_id
         FROM asignaciones a
         JOIN potreros p ON p.id = a.potrero_id
         WHERE a.vaca_id = ? AND a.fecha_salida IS NULL
         LIMIT 1",
        "i",
        [$vacaId]
    );

    $trayectoriaRes = db_result(
        $con,
        "SELECT a.fecha_entrada, a.fecha_salida, a.manga_num, a.usuario,
                p.nombre AS potrero_nombre,
                DATEDIFF(COALESCE(a.fecha_salida, NOW()), a.fecha_entrada) AS dias
         FROM asignaciones a
         JOIN potreros p ON p.id = a.potrero_id
         WHERE a.vaca_id = ?
         ORDER BY a.fecha_entrada ASC",
        "i",
        [$vacaId]
    );
    $trayectoria = [];
    while ($row = mysqli_fetch_assoc($trayectoriaRes)) {
        $trayectoria[] = $row;
    }

    $rotacionesRes = db_result(
        $con,
        "SELECT r.fecha_traslado, r.observacion,
                po.nombre AS potrero_origen,
                pd.nombre AS potrero_destino
         FROM rotaciones r
         JOIN potreros po ON po.id = r.potrero_origen_id
         JOIN potreros pd ON pd.id = r.potrero_destino_id
         WHERE r.vaca_id = ?
         ORDER BY r.fecha_traslado DESC
         LIMIT 20",
        "i",
        [$vacaId]
    );
    $rotaciones = [];
    while ($row = mysqli_fetch_assoc($rotacionesRes)) {
        $rotaciones[] = $row;
    }

    $partosCalculados = (int)(db_value($con, "SELECT COUNT(*) FROM rotaciones WHERE vaca_id = ? AND LOWER(observacion) LIKE '%parto%'", "i", [$vacaId]) ?? 0);
    $totalPartos = isset($vaca['partos']) && (int)$vaca['partos'] > 0 ? (int)$vaca['partos'] : $partosCalculados;

    $vacunacionesRes = db_result(
        $con,
        "SELECT v2.fecha_aplicada, v2.fecha_programada, v2.estado, v2.responsable,
                tv.nombre AS tipo_vacuna, v2.dosis_ml
         FROM vacunaciones v2
         JOIN tipos_vacuna tv ON tv.id = v2.tipo_vacuna_id
         WHERE v2.vaca_id = ?
         ORDER BY COALESCE(v2.fecha_aplicada, v2.fecha_programada) DESC
         LIMIT 10",
        "i",
        [$vacaId]
    );
    $vacunaciones = [];
    while ($row = mysqli_fetch_assoc($vacunacionesRes)) {
        $vacunaciones[] = $row;
    }

    json_response([
        'vaca' => $vaca,
        'ubicacion' => $ubicacion,
        'trayectoria' => $trayectoria,
        'rotaciones' => $rotaciones,
        'partos' => $totalPartos,
        'vacunaciones' => $vacunaciones,
    ]);
} catch (Throwable $e) {
    app_log($e->getMessage());
    json_response(['error' => 'No fue posible cargar la hoja de vida'], 500);
}
?>
