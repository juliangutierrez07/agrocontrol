<?php
session_start();
include("../Config/conexion.php");
$con = conexion();

$id = $_GET['id'];
$usuario_id = $_SESSION['id'];

$sql = "DELETE FROM vacas WHERE id='$id' AND usuario_id='$usuario_id'";
mysqli_query($con, $sql);

header("Location: Registro_Vacas.php");
?>