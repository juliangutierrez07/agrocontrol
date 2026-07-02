<?php
require_once("../Config/conexion.php");
require_login();

// Solo acepta POST para evitar exponer el token CSRF en URLs/logs
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: potrero.php");
    exit();
}

if (!csrf_validate($_POST['_csrf_token'] ?? '')) {
    http_response_code(403);
    exit('Solicitud no autorizada.');
}

$con = conexion();

try {
    $id = input_int($_POST, 'id', 1);
    db_execute($con, "DELETE FROM potreros WHERE id = ? AND usuario_id = ?", "ii", [$id, current_user_id()]);
    header("Location: potrero.php?ok=eliminado");
    exit();
} catch (Throwable $e) {
    app_log($e->getMessage());
    header("Location: potrero.php?error=db");
    exit();
}
?>
