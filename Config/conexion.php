<?php
require_once __DIR__ . '/helpers.php';

function conexion(): mysqli
{
    static $con = null;

    if ($con instanceof mysqli) {
        return $con;
    }

    app_init();

    $con = mysqli_connect(
        env_value('DB_HOST', 'localhost'),
        env_value('DB_USER', 'root'),
        env_value('DB_PASS', ''),
        env_value('DB_NAME', 'agrocontrol')
    );

    if (!$con) {
        app_log('DB connection failed: ' . mysqli_connect_error());
        http_response_code(500);
        exit('No fue posible conectar con la base de datos.');
    }

    mysqli_set_charset($con, env_value('DB_CHARSET', 'utf8mb4'));
    return $con;
}
?>
