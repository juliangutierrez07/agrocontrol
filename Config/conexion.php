<?php
function conexion(){
    $host="localhost";
    $user="root";
    $pass="";
    $db="agrocontrol";
    $con=mysqli_connect($host,$user,$pass,$db);
    return $con;
}
?>