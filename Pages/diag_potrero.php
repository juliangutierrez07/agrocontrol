<?php
// DIAGNOSTICO TEMPORAL - subir a Pages/ (para reusar Config/conexion.php via ../Config/)
// y BORRAR del VPS apenas termines de revisarlo. Expone schema real y el log de errores.
require_once("../Config/conexion.php");
require_role(['admin']);
$con = conexion();

header('Content-Type: text/plain; charset=utf-8');

echo "=== SHOW CREATE TABLE potreros ===\n";
$res = mysqli_query($con, "SHOW CREATE TABLE potreros");
$row = mysqli_fetch_assoc($res);
echo ($row['Create Table'] ?? '(no se pudo leer)') . "\n\n";

echo "=== @@sql_mode ===\n";
$res2 = mysqli_query($con, "SELECT @@sql_mode AS m");
echo (mysqli_fetch_assoc($res2)['m'] ?? '(desconocido)') . "\n\n";

echo "=== version() ===\n";
$res3 = mysqli_query($con, "SELECT VERSION() AS v");
echo (mysqli_fetch_assoc($res3)['v'] ?? '(desconocido)') . "\n\n";

echo "=== Ultimas 40 lineas de logs/app.log ===\n";
$logPath = dirname(__DIR__) . '/logs/app.log';
if (is_file($logPath)) {
    $lines = file($logPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    echo implode("\n", array_slice($lines, -40));
} else {
    echo "No existe $logPath o el proceso PHP no puede leerlo.\n";
}
echo "\n";
