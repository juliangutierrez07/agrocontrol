<?php
require_once("../Config/conexion.php");
require_role(['administrador']);
require_csrf();
$con = conexion();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
        $nombre   = input_string($_POST, 'nombre', 50);
        $correo   = input_email($_POST, 'correo', 150);
        $password = input_string($_POST, 'password', 150);
        $rol      = input_string($_POST, 'rol', 30);

        if (!in_array($rol, ['usuario', 'administrador'], true)) {
            $rol = 'usuario';
        }

        if (strlen($password) < 8) {
            header("Location: usuarios.php?error=password_debil");
            exit();
        }

        $existe = db_value($con, "SELECT id FROM usuarios WHERE correo = ? LIMIT 1", "s", [$correo]);
        if ($existe) {
            header("Location: usuarios.php?error=correo_existente");
            exit();
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        db_execute(
            $con,
            "INSERT INTO usuarios (nombre, correo, password, rol) VALUES (?, ?, ?, ?)",
            "ssss",
            [$nombre, $correo, $hash, $rol]
        );

        app_log('[ADMIN] Usuario creado: ' . $correo . ' por admin ID ' . current_user_id());
        header("Location: usuarios.php?ok=creado");
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
