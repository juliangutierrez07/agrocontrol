<?php
include("../Config/conexion.php");
$con = conexion();
if(isset($_POST['nombre']) && isset($_POST['correo']) && isset($_POST['password'])){
    if($_POST['nombre'] !== '' && $_POST['correo'] !== '' && $_POST['password'] !== ''){
        $nombre = mysqli_real_escape_string($con, trim($_POST['nombre']));
        $correo = mysqli_real_escape_string($con, trim($_POST['correo']));
        $password = mysqli_real_escape_string($con, trim($_POST['password']));

        $validarCorreo = "SELECT id FROM usuarios WHERE correo = '$correo' LIMIT 1";
        $resultadoCorreo = mysqli_query($con, $validarCorreo);

        if($resultadoCorreo && mysqli_num_rows($resultadoCorreo) > 0){
            header("Location: administrador.php?registro=correo_existente");
            exit();
        }

        $sql = "INSERT INTO usuarios (nombre,correo,password) VALUES ('$nombre','$correo','$password')";
        mysqli_query($con,$sql);
        header("Location: administrador.php?registro=ok");
        exit();
    }
}
?>
