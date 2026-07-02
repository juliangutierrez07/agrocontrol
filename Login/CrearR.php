<?php
require_once("../Config/conexion.php");
// Solo administradores pueden crear nuevas cuentas.
// require_role llama internamente a require_login, que llama a start_secure_session.
require_role(['administrador']);
$con = conexion();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
        require_csrf();
        $nombre   = input_string($_POST, 'nombre', 100);
        $correo   = input_email($_POST, 'correo', 255);
        $password = input_string($_POST, 'password', 255);

        if (strlen($password) < 8) {
            header("Location: administrador.php?registro=password_debil");
            exit();
        }

        $existe = db_value($con, "SELECT id FROM usuarios WHERE correo = ? LIMIT 1", "s", [$correo]);
        if ($existe) {
            header("Location: administrador.php?registro=correo_existente");
            exit();
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        db_execute(
            $con,
            "INSERT INTO usuarios (nombre, correo, password) VALUES (?, ?, ?)",
            "sss",
            [$nombre, $correo, $hash]
        );

        app_log('[ADMIN] Usuario creado: ' . $correo . ' por admin ID ' . current_user_id());
        header("Location: administrador.php?registro=ok");
        exit();
    } catch (Throwable $e) {
        app_log($e->getMessage());
        header("Location: administrador.php?registro=error");
        exit();
    }
}

header("Location: administrador.php");
exit();
?>
