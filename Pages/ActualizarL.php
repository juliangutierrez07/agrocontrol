<?php
require_once("../Config/conexion.php");
require_login();
require_csrf();
$con = conexion();

try {
    $id = input_int($_POST, 'id', 1);
    $vacaId = input_int($_POST, 'vaca_id', 1);
    $fecha = input_date($_POST, 'fecha');
    $litros = input_float($_POST, 'litros', 0, 1000);
    $usuarioId = current_user_id();

    $vaca = db_one($con, "SELECT nombre FROM vacas WHERE id = ? AND usuario_id = ? LIMIT 1", "ii", [$vacaId, $usuarioId]);
    $registroPrevio = db_one(
        $con,
        "SELECT litros FROM registroleche WHERE vaca_id = ? AND usuario_id = ? AND id <> ? ORDER BY fecha DESC, id DESC LIMIT 1",
        "iii",
        [$vacaId, $usuarioId, $id]
    );

    db_execute(
        $con,
        "UPDATE registroleche SET vaca_id = ?, fecha = ?, litros = ? WHERE id = ? AND usuario_id = ?",
        "isdii",
        [$vacaId, $fecha, $litros, $id, $usuarioId]
    );

    $toast = ['type' => 'success', 'message' => 'Registro actualizado correctamente'];
    if ($vaca && $registroPrevio && (float)$registroPrevio['litros'] > 0) {
        $litrosPrevios = (float)$registroPrevio['litros'];
        $caidaPorcentual = (($litrosPrevios - $litros) / $litrosPrevios) * 100;
        if ($caidaPorcentual >= 8) {
            $toast = [
                'type' => 'alert',
                'message' => sprintf('Alerta: %s bajo %.1f%% su produccion (de %.1f L a %.1f L).', $vaca['nombre'], $caidaPorcentual, $litrosPrevios, $litros)
            ];
        }
    }

    $_SESSION['toast'] = $toast;
    header("Location: produccion_lechera.php");
    exit;
} catch (Throwable $e) {
    app_log($e->getMessage());
    $_SESSION['toast'] = ['type' => 'alert', 'message' => 'No fue posible actualizar el registro.'];
    header("Location: produccion_lechera.php");
    exit;
}
?>
