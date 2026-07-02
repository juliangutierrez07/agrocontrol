<?php
require_once("../Config/conexion.php");
require_login();

// Solo acepta POST para evitar exponer el token CSRF en URLs/logs
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: Registro_Vacas.php");
    exit();
}

if (!csrf_validate($_POST['_csrf_token'] ?? '')) {
    http_response_code(403);
    exit('Solicitud no autorizada.');
}

$con = conexion();

try {
    $id   = input_int($_POST, 'id', 1);
    $foto = db_value($con, "SELECT foto FROM vacas WHERE id = ? AND usuario_id = ? LIMIT 1", "ii", [$id, current_user_id()]);
    db_execute($con, "DELETE FROM vacas WHERE id = ? AND usuario_id = ?", "ii", [$id, current_user_id()]);
    delete_cow_image($foto);
    header("Location: Registro_Vacas.php?ok=eliminado");
    exit();
} catch (Throwable $e) {
    app_log($e->getMessage());
    header("Location: Registro_Vacas.php?error=db");
    exit();
}
?>
