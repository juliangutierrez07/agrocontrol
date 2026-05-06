<?php
    include("../Config/conexion.php");
    $con = conexion();

    $id=$_GET['id'];

    $sql="DELETE FROM registroleche WHERE id=$id";
    $query=mysqli_query($con,$sql);

    if($query){
        header("Location:produccion_lechera.php");
        exit();
    }else{
        echo"No se pudo eliminar el registro de produccion ";
    }
?>