<?php
require_once("../Config/conexion.php");
require_login();
require_csrf();
$con = conexion();

try {
    $uid = current_user_id();
    $id = input_int($_POST, 'id', 1);
    $nombre = input_string($_POST, 'nombre', 100);
    $hectareas = input_float($_POST, 'hectareas', 0.01, 100000);
    $tipoPasto = input_string($_POST, 'tipo_pasto', 60);
    $tieneMangas = isset($_POST['tiene_mangas']) ? 1 : 0;
    $numMangas = $tieneMangas ? input_int($_POST, 'num_mangas', 1, 100) : 0;
    $tamanoManga = $tieneMangas ? input_float($_POST, 'tamaño_manga', 0, 100000, false) : 0;
    $capacidad = input_int($_POST, 'capacidad_max', 1, 100000);

    db_execute(
        $con,
        "UPDATE potreros
         SET nombre = ?, hectareas = ?, tipo_pasto = ?, tiene_mangas = ?, num_mangas = ?, tamaño_manga = ?, capacidad_max = ?
         WHERE id = ? AND usuario_id = ?",
        "sdsiidiii",
        [$nombre, $hectareas, $tipoPasto, $tieneMangas, $numMangas, $tamanoManga, $capacidad, $id, $uid]
    );

    header("Location: potrero.php?ok=editado");
    exit;
} catch (Throwable $e) {
    app_log($e->getMessage());
    header("Location: potrero.php?error=datos_invalidos");
    exit;
}
?>
