<?php
require_once("../Config/conexion.php");
require_login();
$con = conexion();
$usuarioId = current_user_id();

try {
    $id = input_int($_GET, 'id', 1);
    $row = db_one($con, "SELECT * FROM vacas WHERE id = ? AND usuario_id = ? LIMIT 1", "ii", [$id, $usuarioId]);
    if (!$row) {
        header('Location: Registro_Vacas.php?error=no_encontrada');
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        require_csrf();
        $nombre = input_string($_POST, 'nombre', 100);
        $raza = input_string($_POST, 'raza', 100, false);
        $edad = input_int($_POST, 'edad', 0, 40);
        $estadosPermitidos = ['produccion', 'secado', 'enrazada'];
        $estado = input_string($_POST, 'estado', 40);
        if (!in_array($estado, $estadosPermitidos, true)) {
            throw new InvalidArgumentException('Estado de vaca no válido.');
        }
        db_execute(
            $con,
            "UPDATE vacas SET nombre = ?, raza = ?, edad = ?, estado = ? WHERE id = ? AND usuario_id = ?",
            "ssisii",
            [$nombre, $raza, $edad, $estado, $id, $usuarioId]
        );
        header('Location: Registro_Vacas.php?ok=editado');
        exit();
    }
} catch (Throwable $e) {
    app_log($e->getMessage());
    header('Location: Registro_Vacas.php?error=datos_invalidos');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgroControl | Editar Vaca</title>
    <link rel="stylesheet" href="../Css/registro_vacas.css">
</head>
<body>
    <form method="POST">
        <?php echo csrf_field(); ?>
        <label>Nombre:
            <input type="text" name="nombre" maxlength="100" value="<?php echo e($row['nombre']); ?>" required>
        </label><br>

        <label>Raza:
            <input type="text" name="raza" maxlength="100" value="<?php echo e($row['raza']); ?>" required>
        </label><br>

        <label>Edad:
            <input type="number" name="edad" min="0" max="40" value="<?php echo (int)$row['edad']; ?>" required>
        </label><br>
        <label>Estado:</label>
        <select name="estado" required>
            <option value="">Seleccione Estado</option>
            <option value="produccion" <?php if ($row['estado'] === "produccion") echo "selected"; ?>>En Produccion</option>
            <option value="enrazada" <?php if ($row['estado'] === "enrazada") echo "selected"; ?>>Enrazada</option>
            <option value="secado" <?php if ($row['estado'] === "secado") echo "selected"; ?>>En Secado</option>
        </select>

        <button type="submit">Guardar Cambios</button>
    </form>
</body>
</html>
