<?php
require_once("../Config/conexion.php");
require_login();
require_csrf();
$con = conexion();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
        $codigo = input_string($_POST, 'codigo', 50);
        $nombre = input_string($_POST, 'nombre', 100);
        $raza = input_string($_POST, 'raza', 100, false);
        $edad = input_int($_POST, 'edad', 0, 40, false);
        $estado = input_string($_POST, 'estado', 40);
        if (!estado_vaca_valido($estado)) {
            throw new InvalidArgumentException('Estado de vaca no válido.');
        }
        $descripcion = input_string($_POST, 'descripcion', 1000, false);
        $vacunasInfo = input_string($_POST, 'vacunas_info', 1000, false);
        $partos = input_int($_POST, 'partos', 0, 30, false);
        $usuarioId = current_user_id();

        $existe = db_value($con, "SELECT id FROM vacas WHERE codigo = ? AND usuario_id = ? LIMIT 1", "si", [$codigo, $usuarioId]);
        if ($existe) {
            header("Location: Registro_Vacas.php?error=codigo_existente");
            exit();
        }

        $fotoRuta = isset($_FILES['foto']) ? save_uploaded_image($_FILES['foto'], $usuarioId, 'foto') : null;

        db_execute(
            $con,
            "INSERT INTO vacas (codigo, nombre, raza, edad, estado, foto, descripcion, vacunas_info, partos, usuario_id)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            "sssissssii",
            [$codigo, $nombre, $raza, $edad, $estado, $fotoRuta, $descripcion, $vacunasInfo, $partos, $usuarioId]
        );

        header("Location: Registro_Vacas.php?ok=creado");
        exit();
    } catch (Throwable $e) {
        app_log($e->getMessage());
        header("Location: Registro_Vacas.php?error=datos_invalidos");
        exit();
    }
}
?>
