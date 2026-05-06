<?php
session_start();
include("../Config/conexion.php");
$con = conexion();

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $nombre        = $_POST['nombre'];
    $hectareas     = $_POST['hectareas'];
    $tipo_pasto    = $_POST['tipo_pasto'];
    $tiene_mangas  = isset($_POST['tiene_mangas']) ? 1 : 0;
    $num_mangas    = isset($_POST['num_mangas']) ? $_POST['num_mangas'] : 0;
    $tamaño_manga  = isset($_POST['tamaño_manga']) ? $_POST['tamaño_manga'] : 0;
    $capacidad_max = $_POST['capacidad_max'];
    $usuario_id    = (int)$_SESSION['id']; // Usuario logueado

    $sql = "INSERT INTO potreros (nombre, hectareas, tipo_pasto, tiene_mangas, num_mangas, tamaño_manga, capacidad_max, usuario_id) 
            VALUES ('$nombre', '$hectareas', '$tipo_pasto', '$tiene_mangas', '$num_mangas', '$tamaño_manga', '$capacidad_max', '$usuario_id')";

    $query = mysqli_query($con, $sql);

    if($query){
        header("Location: potrero.php");
        exit();
    } else {
        echo "No Se Realizó El Registro Del Potrero";
    }
}
?>