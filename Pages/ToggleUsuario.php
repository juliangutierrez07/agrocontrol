<?php
require_once("../Config/conexion.php");
require_role(['administrador']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: usuarios.php");
    exit();
}

if (!csrf_validate($_POST['_csrf_token'] ?? '')) {
    http_response_code(403);
    exit('Solicitud no autorizada.');
}

$con = conexion();

try {
    $id = input_int($_POST, 'id', 1);

    if ($id === current_user_id()) {
        header("Location: usuarios.php?error=auto_desactivar");
        exit();
    }

    $usuario = db_one($con, "SELECT rol, activo FROM usuarios WHERE id = ?", "i", [$id]);
    if (!$usuario) {
        header("Location: usuarios.php?error=datos_invalidos");
        exit();
    }

    $nuevoEstado = (int)$usuario['activo'] === 1 ? 0 : 1;

    if ($nuevoEstado === 0 && $usuario['rol'] === 'administrador') {
        $otrosAdmins = (int)db_value(
            $con,
            "SELECT COUNT(*) FROM usuarios WHERE rol = 'administrador' AND activo = 1 AND id != ?",
            "i",
            [$id]
        );
        if ($otrosAdmins === 0) {
            header("Location: usuarios.php?error=ultimo_admin");
            exit();
        }
    }

    db_execute($con, "UPDATE usuarios SET activo = ? WHERE id = ?", "ii", [$nuevoEstado, $id]);

    app_log('[ADMIN] Usuario ID ' . $id . ' marcado como ' . ($nuevoEstado ? 'activo' : 'inactivo') . ' por admin ID ' . current_user_id());
    header("Location: usuarios.php?ok=" . ($nuevoEstado ? 'activado' : 'desactivado'));
    exit();
} catch (Throwable $e) {
    app_log($e->getMessage());
    header("Location: usuarios.php?error=db");
    exit();
}
?>
