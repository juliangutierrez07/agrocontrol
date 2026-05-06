<?php
session_start();
include("../Config/conexion.php");
$con = conexion();

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $vaca_id = (int) $_POST['vaca_id'];
    $fecha   = mysqli_real_escape_string($con, $_POST['fecha']);
    $litros  = (float) $_POST['litros'];

    $usuario_id = (int) $_SESSION['id'];

    $vacaQuery = mysqli_query($con, "
        SELECT nombre
        FROM vacas
        WHERE id = '$vaca_id' AND usuario_id = '$usuario_id'
        LIMIT 1
    ");
    $vaca = mysqli_fetch_assoc($vacaQuery);

    if (!$vaca) {
        $_SESSION['toast'] = [
            'type' => 'alert',
            'message' => 'No fue posible registrar la produccion para la vaca seleccionada.'
        ];
        header("Location: /AgroControl/Pages/produccion_lechera.php");
        exit();
    }

    $previoQuery = mysqli_query($con, "
        SELECT litros
        FROM registroleche
        WHERE vaca_id = '$vaca_id'
          AND usuario_id = '$usuario_id'
        ORDER BY fecha DESC, id DESC
        LIMIT 1
    ");
    $registroPrevio = mysqli_fetch_assoc($previoQuery);

    $sql = "INSERT INTO registroleche (vaca_id, fecha, litros, usuario_id) 
            VALUES ('$vaca_id','$fecha','$litros','$usuario_id')";

    $query = mysqli_query($con, $sql);

    if ($query) {
        $toast = [
            'type' => 'success',
            'message' => 'Registro guardado correctamente'
        ];

        if ($registroPrevio && (float) $registroPrevio['litros'] > 0) {
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
        header("Location: /AgroControl/Pages/produccion_lechera.php");
        exit();
    } else {
        echo "Error al registrar la producción: " . mysqli_error($con);
    }
}
?>
