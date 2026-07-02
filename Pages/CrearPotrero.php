<?php
require_once("../Config/conexion.php");
require_login();
require_csrf();
$con = conexion();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
        $nombre = input_string($_POST, 'nombre', 100);
        $hectareas = input_float($_POST, 'hectareas', 0.01, 100000);
        $tipoPasto = input_string($_POST, 'tipo_pasto', 60);
        $tieneMangas = isset($_POST['tiene_mangas']) ? 1 : 0;
        $numMangas = $tieneMangas ? input_int($_POST, 'num_mangas', 1, 100) : 0;
        $tamanoManga = $tieneMangas ? input_float($_POST, 'tamaño_manga', 0, 100000, false) : 0;
        $capacidadMax = input_int($_POST, 'capacidad_max', 1, 100000);
        $usuarioId = current_user_id();

        db_execute(
            $con,
            "INSERT INTO potreros (nombre, hectareas, tipo_pasto, tiene_mangas, num_mangas, tamaño_manga, capacidad_max, usuario_id)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
            "sdsiidii",
            [$nombre, $hectareas, $tipoPasto, $tieneMangas, $numMangas, $tamanoManga, $capacidadMax, $usuarioId]
        );

        header("Location: potrero.php?ok=creado");
        exit();
    } catch (Throwable $e) {
        app_log($e->getMessage());
        header("Location: potrero.php?error=datos_invalidos");
        exit();
    }
}

header("Location: potrero.php");
exit();
?>
