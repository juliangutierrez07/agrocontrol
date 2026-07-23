<?php
require_once("../Config/conexion.php");
require_role(['administrador']);
require_csrf();
$con = conexion();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
        $id       = input_int($_POST, 'id', 1);
        $password = input_string($_POST, 'password', 150);

        if (strlen($password) < 8) {
            header("Location: usuarios.php?error=password_debil");
            exit();
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        db_execute($con, "UPDATE usuarios SET password = ? WHERE id = ?", "si", [$hash, $id]);

        app_log('[ADMIN] Contraseña restablecida para usuario ID ' . $id . ' por admin ID ' . current_user_id());
        header("Location: usuarios.php?ok=password");
        exit();
    } catch (Throwable $e) {
        app_log($e->getMessage());
        header("Location: usuarios.php?error=datos_invalidos");
        exit();
    }
}

header("Location: usuarios.php");
exit();
?>
