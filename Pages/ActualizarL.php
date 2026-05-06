<?php
session_start();
include("../Config/conexion.php");
$con = conexion();

$id     = (int) $_POST['id'];
$vacaId = (int) $_POST['vaca_id'];
$fecha  = mysqli_real_escape_string($con, $_POST['fecha']);
$litros = (float) $_POST['litros'];
$usuario_id = isset($_SESSION['id']) ? (int) $_SESSION['id'] : 0;

$vacaQuery = mysqli_query($con, "
    SELECT nombre
    FROM vacas
    WHERE id = '$vacaId' AND usuario_id = '$usuario_id'
    LIMIT 1
");
$vaca = mysqli_fetch_assoc($vacaQuery);

$previoQuery = mysqli_query($con, "
    SELECT litros
    FROM registroleche
    WHERE vaca_id = '$vacaId'
      AND usuario_id = '$usuario_id'
      AND id <> '$id'
    ORDER BY fecha DESC, id DESC
    LIMIT 1
");
$registroPrevio = mysqli_fetch_assoc($previoQuery);

mysqli_query($con, "
    UPDATE registroleche
    SET vaca_id = '$vacaId',
        fecha   = '$fecha',
        litros  = '$litros'
    WHERE id = '$id'
      AND usuario_id = '$usuario_id'
");

$toast = [
    'type' => 'success',
    'message' => 'Registro actualizado correctamente'
];

if ($vaca && $registroPrevio && (float) $registroPrevio['litros'] > 0) {
    $litrosPrevios = (float) $registroPrevio['litros'];
    $caidaPorcentual = (($litrosPrevios - $litros) / $litrosPrevios) * 100;

    if ($caidaPorcentual >= 8) {
        $toast = [
            'type' => 'alert',
            'message' => sprintf(
                'Alerta: %s bajo %.1f%% su produccion (de %.1f L a %.1f L).',
                $vaca['nombre'],
                $caidaPorcentual,
                $litrosPrevios,
                $litros
            )
        ];
    }
}

$_SESSION['toast'] = $toast;
header("Location: produccion_lechera.php");
exit;
?>
