<?php
    include("../Config/conexion.php");
    $con = conexion();



    $id = $_GET['id'];
    $sql = "SELECT * FROM vacas WHERE id = $id";
    $query = mysqli_query($con,$sql);
    $row = mysqli_fetch_array($query);

    if($_SERVER['REQUEST_METHOD'] == 'POST'){
        $nombre= $_POST['nombre'];
        $raza = $_POST['raza'];
        $edad = $_POST['edad'];
        $estado = $_POST['estado'];
        $sql_update = "UPDATE vacas SET nombre='$nombre', raza='$raza', edad='$edad', estado='$estado' WHERE id = $id";
        $query_update = mysqli_query($con,$sql_update);
        if($query_update){
            header('Location: Registro_Vacas.php');
            exit();
        }else{
            echo "error";
        }
    }
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema De Control/ Editar Aprendiz</title>
    <link rel="stylesheet" href="Diseño/EditarA.css">
</head>

<body>
    <form method="POST">
        <label>Nombre:
            <input type="text" name="nombre" value="<?php echo $row['nombre'];?>" required>
        </label><br>

        <label>Raza:
            <input type="text" name="raza" value="<?php echo $row['raza'];?>" required>
        </label><br>

        <label>Edad:
            <input type="number" name="edad" value="<?php echo $row['edad'];?>" required>
        </label><br>
        <label >Estado:</label>
        <select name="estado" required>
            <option value="">Seleccione Estado</option>
            <option value="produccion" <?php if($row['estado']=="produccion") echo "selected"; ?>>En Producción</option>
            <option value="enrazada" <?php if($row['estado']=="enrazada") echo "selected"; ?>>Enrazada</option>
            <option value="secado" <?php if($row['estado']=="secado") echo "selected"; ?>>En Secado</option>
        </select>

        <button type="submit">Guardar Cambios</button>
    </form>
    </div>
</body>

</html>