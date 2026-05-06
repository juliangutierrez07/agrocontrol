<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

$conn = new mysqli('localhost', 'root', '', 'agrocontrol');
if ($conn->connect_error) {
    die(json_encode(['error' => 'Conexión fallida: ' . $conn->connect_error]));
}
$conn->set_charset('utf8');

// Obtener usuario_id de la sesión
$usuario_id = (int)($_SESSION['id'] ?? 0);
if (!$usuario_id) {
    echo json_encode(['error' => 'No autenticado']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

// Marcar como vencidas automáticamente al entrar (solo las del usuario)
$conn->query("
    UPDATE vacunaciones 
    SET estado = 'vencida' 
    WHERE estado = 'pendiente' 
    AND fecha_programada < CURDATE()
    AND vaca_id IN (SELECT id FROM vacas WHERE usuario_id = $usuario_id)
");

// ── GET ─────────────────────────────────────────────
if ($method === 'GET') {

    // Listar todas las vacunaciones del usuario
    if ($action === 'listar') {
        $vaca_id = isset($_GET['vaca_id']) ? (int)$_GET['vaca_id'] : null;
        $extra   = $vaca_id ? "AND va.vaca_id = $vaca_id" : '';
        $sql = "
            SELECT va.*, 
                   v.nombre AS nombre_vaca, v.codigo AS codigo_vaca,
                   tv.nombre AS tipo_vacuna, tv.obligatoria_ica,
                   DATEDIFF(va.fecha_programada, CURDATE()) AS dias_restantes
            FROM vacunaciones va
            JOIN vacas v ON v.id = va.vaca_id
            JOIN tipos_vacuna tv ON tv.id = va.tipo_vacuna_id
            WHERE v.usuario_id = $usuario_id $extra
            ORDER BY va.fecha_programada ASC
        ";
        $res  = $conn->query($sql);
        $data = [];
        while ($row = $res->fetch_assoc()) $data[] = $row;
        echo json_encode($data);
    }

    // Alertas: vacunas en los próximos 7 días del usuario
    elseif ($action === 'alertas') {
        $sql = "
            SELECT va.*, v.nombre AS nombre_vaca, tv.nombre AS tipo_vacuna,
                   DATEDIFF(va.fecha_programada, CURDATE()) AS dias_restantes
            FROM vacunaciones va
            JOIN vacas v ON v.id = va.vaca_id
            JOIN tipos_vacuna tv ON tv.id = va.tipo_vacuna_id
            WHERE v.usuario_id = $usuario_id
            AND va.estado = 'pendiente'
            AND va.fecha_programada BETWEEN CURDATE() 
                AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
            ORDER BY va.fecha_programada ASC
        ";
        $res  = $conn->query($sql);
        $data = [];
        while ($row = $res->fetch_assoc()) $data[] = $row;
        echo json_encode($data);
    }

    // Tipos de vacuna para el select
    elseif ($action === 'tipos') {
        $res  = $conn->query("SELECT * FROM tipos_vacuna ORDER BY nombre");
        $data = [];
        while ($row = $res->fetch_assoc()) $data[] = $row;
        echo json_encode($data);
    }

    // Lista de vacas del usuario para el select
    elseif ($action === 'vacas') {
        $res  = $conn->query("SELECT id, codigo, nombre FROM vacas WHERE usuario_id = $usuario_id ORDER BY nombre");
        $data = [];
        while ($row = $res->fetch_assoc()) $data[] = $row;
        echo json_encode($data);
    }

    // Resumen para reporte ICA (solo del usuario)
    elseif ($action === 'resumen') {
        $sql = "
            SELECT 
                tv.nombre AS tipo_vacuna,
                tv.obligatoria_ica,
                COUNT(CASE WHEN va.estado='aplicada'  THEN 1 END) AS aplicadas,
                COUNT(CASE WHEN va.estado='pendiente' THEN 1 END) AS pendientes,
                COUNT(CASE WHEN va.estado='vencida'   THEN 1 END) AS vencidas
            FROM vacunaciones va
            JOIN tipos_vacuna tv ON tv.id = va.tipo_vacuna_id
            JOIN vacas v ON v.id = va.vaca_id
            WHERE v.usuario_id = $usuario_id
            GROUP BY tv.id, tv.nombre, tv.obligatoria_ica
            ORDER BY tv.obligatoria_ica DESC, tv.nombre
        ";
        $res  = $conn->query($sql);
        $data = [];
        while ($row = $res->fetch_assoc()) $data[] = $row;
        echo json_encode($data);
    }
}

// ── POST: crear vacunación ───────────────────────────
elseif ($method === 'POST') {
    $body = json_decode(file_get_contents('php://input'), true);

    $vaca_id        = (int)$body['vaca_id'];
    $tipo_vacuna_id = (int)$body['tipo_vacuna_id'];
    $fecha          = $conn->real_escape_string($body['fecha_programada']);
    $dosis          = !empty($body['dosis_ml']) ? (float)$body['dosis_ml'] : null;
    $responsable    = $conn->real_escape_string($body['responsable'] ?? '');
    $obs            = $conn->real_escape_string($body['observaciones'] ?? '');
    $dosis_sql      = $dosis ? $dosis : 'NULL';

    // Verificar que la vaca pertenece al usuario
    $checkVaca = $conn->query("SELECT id FROM vacas WHERE id = $vaca_id AND usuario_id = $usuario_id LIMIT 1");
    if ($checkVaca->num_rows === 0) {
        echo json_encode(['error' => 'Vaca no válida']);
        exit;
    }

    $sql = "
        INSERT INTO vacunaciones 
            (vaca_id, tipo_vacuna_id, fecha_programada, dosis_ml, responsable, observaciones)
        VALUES 
            ($vaca_id, $tipo_vacuna_id, '$fecha', $dosis_sql, '$responsable', '$obs')
    ";
    if ($conn->query($sql)) {
        echo json_encode(['success' => true, 'id' => $conn->insert_id]);
    } else {
        echo json_encode(['error' => $conn->error]);
    }
}

// ── PUT: marcar como aplicada ────────────────────────
elseif ($method === 'PUT') {
    $body          = json_decode(file_get_contents('php://input'), true);
    $id            = (int)$body['id'];
    $fecha_aplicada = $conn->real_escape_string($body['fecha_aplicada']);
    $dosis         = !empty($body['dosis_ml']) ? (float)$body['dosis_ml'] : null;
    $responsable   = $conn->real_escape_string($body['responsable'] ?? '');
    $obs           = $conn->real_escape_string($body['observaciones'] ?? '');
    $dosis_sql     = $dosis ? $dosis : 'NULL';

    // Verificar que la vacunación pertenece al usuario
    $checkVac = $conn->query("SELECT va.id FROM vacunaciones va JOIN vacas v ON v.id = va.vaca_id WHERE va.id = $id AND v.usuario_id = $usuario_id LIMIT 1");
    if ($checkVac->num_rows === 0) {
        echo json_encode(['error' => 'Registro no válido']);
        exit;
    }

    $sql = "
        UPDATE vacunaciones SET
            estado         = 'aplicada',
            fecha_aplicada = '$fecha_aplicada',
            dosis_ml       = $dosis_sql,
            responsable    = '$responsable',
            observaciones  = '$obs'
        WHERE id = $id
    ";
    if ($conn->query($sql)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => $conn->error]);
    }
}

elseif ($method === 'DELETE') {
    $id = (int)$_GET['id'];

    // Verificar que la vacunación pertenece al usuario antes de eliminar
    $checkVac = $conn->query("SELECT va.id FROM vacunaciones va JOIN vacas v ON v.id = va.vaca_id WHERE va.id = $id AND v.usuario_id = $usuario_id LIMIT 1");
    if ($checkVac->num_rows === 0) {
        echo json_encode(['error' => 'Registro no válido']);
        exit;
    }

    if ($conn->query("DELETE FROM vacunaciones WHERE id = $id")) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => $conn->error]);
    }
}

$conn->close();
?>