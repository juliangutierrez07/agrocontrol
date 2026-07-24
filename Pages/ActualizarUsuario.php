<?php
require_once("../Config/conexion.php");
require_role(['administrador']);
require_csrf();
$con = conexion();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
        $id     = input_int($_POST, 'id', 1);
        $nombre = input_string($_POST, 'nombre', 50);
        $correo = input_email($_POST, 'correo', 150);
        $rol    = input_string($_POST, 'rol', 30);

        if (!in_array($rol, ['usuario', 'administrador'], true)) {
            $rol = 'usuario';
        }

        if ($id === current_user_id() && $rol !== 'administrador') {
            header("Location: usuarios.php?error=auto_degradar");
            exit();
        }

        if ($rol !== 'administrador') {
            $objetivo = db_one($con, "SELECT rol, activo FROM usuarios WHERE id = ? LIMIT 1", "i", [$id]);
            $eraAdminActivo = $objetivo && $objetivo['rol'] === 'administrador' && (int)$objetivo['activo'] === 1;
            if ($eraAdminActivo) {
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
        }

        $existe = db_value($con, "SELECT id FROM usuarios WHERE correo = ? AND id != ? LIMIT 1", "si", [$correo, $id]);
        if ($existe) {
            header("Location: usuarios.php?error=correo_existente");
            exit();
        }

        db_execute(
            $con,
            "UPDATE usuarios SET nombre = ?, correo = ?, rol = ? WHERE id = ?",
            "sssi",
            [$nombre, $correo, $rol, $id]
        );

        if ($id === current_user_id()) {
            $_SESSION['nombre'] = $nombre;
        }

        app_log('[ADMIN] Usuario ID ' . $id . ' actualizado por admin ID ' . current_user_id());
        header("Location: usuarios.php?ok=editado");
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
