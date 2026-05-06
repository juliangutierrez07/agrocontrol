<?php
include("../Config/conexion.php");
$con = conexion();

$id  = $_GET['id'];
$sql = "DELETE FROM potreros WHERE id = '$id'";
$query = mysqli_query($con, $sql);

if($query){
    header("Location: potrero.php");
    exit();
} else {
    echo "No Se Pudo Eliminar El Potrero";
}
?>