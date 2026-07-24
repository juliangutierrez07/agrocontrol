<?php
define('AC_JSON_ENDPOINT', true);
require_once("../Config/conexion.php");
require_login();
$conn = conexion();
$usuarioId = current_user_id();
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    if ($action === 'csrf') {
        json_response(['csrf_token' => csrf_token()]);
    }

    if ($method !== 'GET') {
        require_csrf();
    }

    db_execute(
        $conn,
        "UPDATE vacunaciones
         SET estado = 'vencida'
         WHERE estado = 'pendiente'
           AND fecha_programada < CURDATE()
           AND vaca_id IN (SELECT id FROM vacas WHERE usuario_id = ?)",
        "i",
        [$usuarioId]
    );

    if ($method === 'GET') {
        if ($action === 'listar') {
            $vacaId = isset($_GET['vaca_id']) && $_GET['vaca_id'] !== '' ? input_int($_GET, 'vaca_id', 1) : null;
            $sql = "SELECT va.*, v.nombre AS nombre_vaca, v.codigo AS codigo_vaca,
                           tv.nombre AS tipo_vacuna, tv.obligatoria_ica,
                           DATEDIFF(va.fecha_programada, CURDATE()) AS dias_restantes
                    FROM vacunaciones va
                    JOIN vacas v ON v.id = va.vaca_id
                    JOIN tipos_vacuna tv ON tv.id = va.tipo_vacuna_id
                    WHERE v.usuario_id = ?";
            $types = "i";
            $params = [$usuarioId];
            if ($vacaId) {
                $sql .= " AND va.vaca_id = ?";
                $types .= "i";
                $params[] = $vacaId;
            }
            $sql .= " ORDER BY va.fecha_programada ASC";
            $res = db_result($conn, $sql, $types, $params);
        } elseif ($action === 'alertas') {
            $res = db_result(
                $conn,
                "SELECT va.*, v.nombre AS nombre_vaca, tv.nombre AS tipo_vacuna,
                        DATEDIFF(va.fecha_programada, CURDATE()) AS dias_restantes
                 FROM vacunaciones va
                 JOIN vacas v ON v.id = va.vaca_id
                 JOIN tipos_vacuna tv ON tv.id = va.tipo_vacuna_id
                 WHERE v.usuario_id = ?
                   AND va.estado = 'pendiente'
                   AND va.fecha_programada BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
                 ORDER BY va.fecha_programada ASC",
                "i",
                [$usuarioId]
            );
        } elseif ($action === 'tipos') {
            $res = db_result($conn, "SELECT * FROM tipos_vacuna ORDER BY nombre");
        } elseif ($action === 'vacas') {
            $res = db_result($conn, "SELECT id, codigo, nombre FROM vacas WHERE usuario_id = ? ORDER BY nombre", "i", [$usuarioId]);
        } elseif ($action === 'resumen') {
            $res = db_result(
                $conn,
                "SELECT tv.nombre AS tipo_vacuna, tv.obligatoria_ica,
                        COUNT(CASE WHEN va.estado='aplicada' THEN 1 END) AS aplicadas,
                        COUNT(CASE WHEN va.estado='pendiente' THEN 1 END) AS pendientes,
                        COUNT(CASE WHEN va.estado='vencida' THEN 1 END) AS vencidas
                 FROM vacunaciones va
                 JOIN tipos_vacuna tv ON tv.id = va.tipo_vacuna_id
                 JOIN vacas v ON v.id = va.vaca_id
                 WHERE v.usuario_id = ?
                 GROUP BY tv.id, tv.nombre, tv.obligatoria_ica
                 ORDER BY tv.obligatoria_ica DESC, tv.nombre",
                "i",
                [$usuarioId]
            );
        } else {
            json_response(['error' => 'Accion no valida'], 400);
        }

        $data = [];
        while ($row = mysqli_fetch_assoc($res)) {
            $data[] = array_map(fn($value) => is_string($value) ? htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') : $value, $row);
        }
        json_response($data);
    }

    $body = json_decode(file_get_contents('php://input'), true);
    if (!is_array($body)) {
        json_response(['error' => 'JSON no valido'], 400);
    }

    if ($method === 'POST') {
        $vacaId = input_int($body, 'vaca_id', 1);
        $tipoVacunaId = input_int($body, 'tipo_vacuna_id', 1);
        $fecha = input_date($body, 'fecha_programada');
        $dosis = !empty($body['dosis_ml']) ? input_float($body, 'dosis_ml', 0, 10000) : null;
        $responsable = input_string($body, 'responsable', 100, false);
        $observaciones = input_string($body, 'observaciones', 500, false);

        $vacaValida = db_value($conn, "SELECT id FROM vacas WHERE id = ? AND usuario_id = ? LIMIT 1", "ii", [$vacaId, $usuarioId]);
        if (!$vacaValida) {
            json_response(['error' => 'Vaca no valida'], 400);
        }

        db_execute(
            $conn,
            "INSERT INTO vacunaciones (vaca_id, tipo_vacuna_id, fecha_programada, dosis_ml, responsable, observaciones)
             VALUES (?, ?, ?, ?, ?, ?)",
            "iisdss",
            [$vacaId, $tipoVacunaId, $fecha, $dosis, $responsable, $observaciones]
        );
        json_response(['success' => true, 'id' => mysqli_insert_id($conn)]);
    }

    if ($method === 'PUT') {
        $id = input_int($body, 'id', 1);
        $fechaAplicada = input_date($body, 'fecha_aplicada');
        $dosis = !empty($body['dosis_ml']) ? input_float($body, 'dosis_ml', 0, 10000) : null;
        $responsable = input_string($body, 'responsable', 100, false);
        $observaciones = input_string($body, 'observaciones', 500, false);

        $registroValido = db_value(
            $conn,
            "SELECT va.id FROM vacunaciones va JOIN vacas v ON v.id = va.vaca_id WHERE va.id = ? AND v.usuario_id = ? LIMIT 1",
            "ii",
            [$id, $usuarioId]
        );
        if (!$registroValido) {
            json_response(['error' => 'Registro no valido'], 400);
        }

        db_execute(
            $conn,
            "UPDATE vacunaciones
             SET estado = 'aplicada', fecha_aplicada = ?, dosis_ml = ?, responsable = ?, observaciones = ?
             WHERE id = ?",
            "sdssi",
            [$fechaAplicada, $dosis, $responsable, $observaciones, $id]
        );
        json_response(['success' => true]);
    }

    if ($method === 'DELETE') {
        $id = input_int($_GET, 'id', 1);
        $registroValido = db_value(
            $conn,
            "SELECT va.id FROM vacunaciones va JOIN vacas v ON v.id = va.vaca_id WHERE va.id = ? AND v.usuario_id = ? LIMIT 1",
            "ii",
            [$id, $usuarioId]
        );
        if (!$registroValido) {
            json_response(['error' => 'Registro no valido'], 400);
        }

        db_execute($conn, "DELETE FROM vacunaciones WHERE id = ?", "i", [$id]);
        json_response(['success' => true]);
    }

    json_response(['error' => 'Metodo no permitido'], 405);
} catch (Throwable $e) {
    app_log($e->getMessage());
    json_response(['error' => 'No fue posible procesar la solicitud'], 500);
}
?>
