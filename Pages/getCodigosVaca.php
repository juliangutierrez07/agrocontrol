<?php
// ─────────────────────────────────────────────────────────────
// Endpoint JSON: códigos de vaca del usuario autenticado.
// Devuelve:
//   - usados:    lista de códigos ya registrados (para validar en vivo)
//   - sugeridos: 5 primeros códigos numéricos libres, con el formato
//                establecido en la tabla (cero a la izquierda, "01"..)
// Los códigos son únicos POR usuario (vacas.codigo + vacas.usuario_id),
// por eso todo se filtra por current_user_id().
// Sigue el patrón de getHojaVida.php.
// ─────────────────────────────────────────────────────────────
define('AC_JSON_ENDPOINT', true);
require_once("../Config/conexion.php");
require_login();
$con = conexion();
$usuarioId = current_user_id();

try {
    $res = db_result(
        $con,
        "SELECT codigo FROM vacas WHERE usuario_id = ? ORDER BY codigo ASC",
        "i",
        [$usuarioId]
    );

    $usados = [];
    while ($row = mysqli_fetch_assoc($res)) {
        $codigo = trim((string)($row['codigo'] ?? ''));
        if ($codigo !== '') {
            $usados[] = $codigo;
        }
    }

    // Búsqueda O(1) de códigos ya ocupados.
    $usadosSet = array_flip($usados);

    // Sugerencias: primeros números libres, con relleno de ceros a 2
    // dígitos (formato actual de la tabla: "01".."12"). Para n >= 100
    // str_pad conserva el número completo sin truncar.
    $sugeridos = [];
    for ($n = 1; $n <= 999 && count($sugeridos) < 5; $n++) {
        $candidato = str_pad((string)$n, 2, '0', STR_PAD_LEFT);
        if (!isset($usadosSet[$candidato])) {
            $sugeridos[] = $candidato;
        }
    }

    json_response([
        'usados' => $usados,
        'sugeridos' => $sugeridos,
    ]);
} catch (Throwable $e) {
    app_log($e->getMessage());
    json_response(['error' => 'No fue posible cargar los códigos'], 500);
}
