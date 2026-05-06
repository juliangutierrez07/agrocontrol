<?php
session_start();
include("../Config/conexion.php");
$con = conexion();
$uid = (int)$_SESSION['id'];

$id           = (int)$_POST['id'];
$nombre       = mysqli_real_escape_string($con, trim($_POST['nombre']));
$hectareas    = (float)$_POST['hectareas'];
$tipo_pasto   = mysqli_real_escape_string($con, $_POST['tipo_pasto']);
$tiene_mangas = isset($_POST['tiene_mangas']) ? 1 : 0;
$num_mangas   = $tiene_mangas ? (int)$_POST['num_mangas']         : 0;
$tamaño_manga = $tiene_mangas ? (float)$_POST['tamaño_manga']     : 0;
$capacidad    = (int)$_POST['capacidad_max'];

$sql = "UPDATE potreros SET
    nombre        = '$nombre',
    hectareas     = $hectareas,
    tipo_pasto    = '$tipo_pasto',
    tiene_mangas  = $tiene_mangas,
    num_mangas    = $num_mangas,
    tamaño_manga  = $tamaño_manga,
    capacidad_max = $capacidad
    WHERE id = $id AND usuario_id = $uid";

mysqli_query($con, $sql);
header("Location: potrero.php?ok=editado");
exit;