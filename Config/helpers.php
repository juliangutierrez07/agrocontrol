<?php
function app_init(): void
{
    static $initialized = false;
    if ($initialized) {
        return;
    }
    $initialized = true;

    load_env(dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env');
    ini_set('display_errors', env_value('APP_DEBUG', 'false') === 'true' ? '1' : '0');
    ini_set('log_errors', '1');
    ini_set('error_log', dirname(__DIR__) . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . 'app.log');

    $logDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'logs';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0775, true);
    }

    set_error_handler(function ($severity, $message, $file, $line) {
        if (!(error_reporting() & $severity)) {
            return false;
        }
        app_log("PHP error [$severity] $message in $file:$line");
        return env_value('APP_DEBUG', 'false') !== 'true';
    });
}

function load_env(string $path): void
{
    if (!is_file($path)) {
        return;
    }

    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = array_map('trim', explode('=', $line, 2));
        $value = trim($value, "\"'");
        if (getenv($key) === false) {
            putenv($key . '=' . $value);
            $_ENV[$key] = $value;
        }
    }
}

function env_value(string $key, ?string $default = null): ?string
{
    $value = getenv($key);
    return $value === false ? $default : $value;
}

function app_url(string $path = ''): string
{
    $base = rtrim((string) env_value('APP_URL', ''), '/');
    if (!filter_var($base, FILTER_VALIDATE_URL)) {
        throw new RuntimeException('APP_URL no está configurada correctamente.');
    }

    return $base . '/' . ltrim($path, '/');
}

/** Envía el enlace temporal de recuperación sin incluir datos sensibles en logs. */
function send_password_reset_email(string $correo, string $url): bool
{
    $from = (string) env_value('MAIL_FROM', '');
    $fromName = (string) env_value('MAIL_FROM_NAME', 'AgroControl');
    if (!filter_var($from, FILTER_VALIDATE_EMAIL)) {
        throw new RuntimeException('MAIL_FROM no está configurado correctamente.');
    }

    $subject = 'Recuperación de contraseña - AgroControl';
    $message = "Solicitaste restablecer tu contraseña de AgroControl.\n\n"
        . "Usa este enlace dentro de los próximos 30 minutos:\n{$url}\n\n"
        . "Si no hiciste esta solicitud, puedes ignorar este correo.";
    $safeName = str_replace(["\r", "\n"], '', $fromName);
    $headers = [
        'From: ' . $safeName . ' <' . $from . '>',
        'MIME-Version: 1.0',
        'Content-Type: text/plain; charset=UTF-8',
    ];

    return @mail($correo, $subject, $message, implode("\r\n", $headers));
}

function app_log(string $message): void
{
    $dir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'logs';
    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }
    error_log('[' . date('Y-m-d H:i:s') . '] ' . $message);
}

// ─────────────────────────────────────────────────────────────
// CABECERAS HTTP DE SEGURIDAD
// Se invoca automáticamente desde start_secure_session().
// No es necesario llamarla manualmente en cada página.
// ─────────────────────────────────────────────────────────────
function send_security_headers(): void
{
    // Evita que el navegador adivine el Content-Type
    header('X-Content-Type-Options: nosniff');

    // Impide que la página sea cargada en iframes (clickjacking)
    header('X-Frame-Options: SAMEORIGIN');

    // Controla qué información de referencia se envía
    header('Referrer-Policy: strict-origin-when-cross-origin');

    // Deshabilita la heurística XSS del navegador (moderno: CSP lo reemplaza)
    header('X-XSS-Protection: 1; mode=block');

    // Content-Security-Policy compatible con la aplicación actual:
    // - Scripts propios + CDN de Chart.js + inline (requerido por bloques <script> inline en vistas)
    // - Estilos propios + Google Fonts + inline (requerido por potrero.php con <style> embebido)
    // - Fuentes de Google Fonts
    // - Imágenes propias + data URIs (avatares SVG inline) + fondo de Unsplash (Login/iniciarsesion.css)
    // - Sin frame-ancestors (refuerza X-Frame-Options)
    $csp = implode('; ', [
        "default-src 'self'",
        "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net",
        "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com",
        "font-src 'self' https://fonts.gstatic.com",
        "img-src 'self' data: https://images.unsplash.com",
        "connect-src 'self'",
        "frame-ancestors 'none'",
        "base-uri 'self'",
        "form-action 'self'",
    ]);
    header('Content-Security-Policy: ' . $csp);
}

function start_secure_session(): void
{
    app_init();
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'httponly' => true,
            'samesite' => 'Lax',
            'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        ]);
        session_start();
    }
    // Enviar cabeceras de seguridad HTTP en todas las páginas que inician sesión.
    // La función es idempotente — si headers ya fueron enviados (ej: JSON API) no hace nada.
    if (!headers_sent()) {
        send_security_headers();
    }
}

function require_login_reject(string $motivo = ''): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
        }
        session_destroy();
    }

    if (defined('AC_JSON_ENDPOINT') && AC_JSON_ENDPOINT) {
        json_response(['error' => 'No autorizado'], 401);
    }

    $query = $motivo !== '' ? ('?error=' . $motivo) : '';
    header('Location: ../Login/iniciar_sesion.php' . $query);
    exit();
}

function require_login(): void
{
    start_secure_session();
    if (empty($_SESSION['id'])) {
        require_login_reject();
    }

    static $revalidado = false;
    if ($revalidado) {
        return;
    }
    $revalidado = true;

    try {
        $usuario = db_one(
            conexion(),
            "SELECT rol, activo FROM usuarios WHERE id = ? LIMIT 1",
            "i",
            [(int)$_SESSION['id']]
        );
    } catch (Throwable $e) {
        // Fallo transitorio de BD: no desloguear a todos, solo registrar.
        app_log('[SESSION-REVALIDATE] ' . $e->getMessage());
        return;
    }

    if (!$usuario || (int)$usuario['activo'] !== 1) {
        require_login_reject('sesion_cerrada');
    }

    if (($_SESSION['rol'] ?? null) !== $usuario['rol']) {
        $_SESSION['rol'] = $usuario['rol'];
    }
}

function current_user_id(): int
{
    return isset($_SESSION['id']) ? (int)$_SESSION['id'] : 0;
}

function current_user_role(): string
{
    return $_SESSION['rol'] ?? 'usuario';
}

function require_role(array $roles): void
{
    require_login();
    if (!in_array(current_user_role(), $roles, true)) {
        header('Location: ../Pages/Dashboard.php?error=no_autorizado');
        exit();
    }
}

function e($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function csrf_token(): string
{
    start_secure_session();
    if (empty($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf_token" value="' . e(csrf_token()) . '">';
}

function csrf_validate(?string $token = null): bool
{
    start_secure_session();
    $token = $token ?? ($_POST['_csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    return is_string($token)
        && isset($_SESSION['_csrf_token'])
        && hash_equals($_SESSION['_csrf_token'], $token);
}

function require_csrf(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'GET' && !csrf_validate()) {
        http_response_code(403);
        exit('Solicitud no autorizada.');
    }
}

function input_string(array $source, string $key, int $max = 255, bool $required = true): string
{
    $value = trim((string)($source[$key] ?? ''));
    if ($required && $value === '') {
        throw new InvalidArgumentException("El campo $key es obligatorio.");
    }
    if (mb_strlen($value) > $max) {
        throw new InvalidArgumentException("El campo $key supera la longitud permitida.");
    }
    return $value;
}

function input_email(array $source, string $key, int $max = 255): string
{
    $value = input_string($source, $key, $max);
    if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
        throw new InvalidArgumentException('El correo electronico no es valido.');
    }
    return $value;
}

function input_int(array $source, string $key, int $min = 0, ?int $max = null, bool $required = true): int
{
    if (!isset($source[$key]) || $source[$key] === '') {
        if ($required) {
            throw new InvalidArgumentException("El campo $key es obligatorio.");
        }
        return 0;
    }
    $value = filter_var($source[$key], FILTER_VALIDATE_INT);
    if ($value === false || $value < $min || ($max !== null && $value > $max)) {
        throw new InvalidArgumentException("El campo $key no tiene un valor valido.");
    }
    return (int)$value;
}

function input_float(array $source, string $key, float $min = 0, ?float $max = null, bool $required = true): float
{
    if (!isset($source[$key]) || $source[$key] === '') {
        if ($required) {
            throw new InvalidArgumentException("El campo $key es obligatorio.");
        }
        return 0.0;
    }
    $value = filter_var($source[$key], FILTER_VALIDATE_FLOAT);
    if ($value === false || $value < $min || ($max !== null && $value > $max)) {
        throw new InvalidArgumentException("El campo $key no tiene un valor valido.");
    }
    return (float)$value;
}

function input_date(array $source, string $key): string
{
    $value = input_string($source, $key, 10);
    $date = DateTime::createFromFormat('Y-m-d', $value);
    if (!$date || $date->format('Y-m-d') !== $value) {
        throw new InvalidArgumentException("El campo $key no contiene una fecha valida.");
    }
    return $value;
}

/**
 * Cadena que solo admite dígitos (0-9). Para campos numéricos guardados como
 * texto, como el código de vaca (en la tabla se almacena con cero a la
 * izquierda: "01", "02"...). Mantiene el formato original, no castea a int.
 */
function input_solo_digitos(array $source, string $key, int $max = 15, bool $required = true): string
{
    $value = input_string($source, $key, $max, $required);
    if ($value !== '' && !ctype_digit($value)) {
        throw new InvalidArgumentException("El campo $key solo admite números.");
    }
    return $value;
}

/**
 * Cadena que solo admite letras (incluye tildes y ñ mediante \p{L}) y
 * espacios. Para nombres propios y razas escritas manualmente. Rechaza
 * dígitos y símbolos.
 */
function input_solo_letras(array $source, string $key, int $max = 50, bool $required = true): string
{
    $value = input_string($source, $key, $max, $required);
    if ($value !== '' && !preg_match('/^[\p{L}\p{M} ]+$/u', $value)) {
        throw new InvalidArgumentException("El campo $key solo admite letras y espacios.");
    }
    return $value;
}

// ─────────────────────────────────────────────────────────────
// LÓGICA DE NEGOCIO EXTRAÍDA PARA PRUEBAS UNITARIAS
// Funciones puras (sin BD/sesión), antes duplicadas o mezcladas
// inline en Pages/. Ver tests/Unit/.
// ─────────────────────────────────────────────────────────────

/**
 * Estados válidos para una vaca. Antes declarada como array literal
 * repetido en CrearV.php, Registro_Vacas.php y editar.php.
 */
function estado_vaca_valido(string $estado): bool
{
    return in_array($estado, ['produccion', 'secado', 'enrazada'], true);
}

/**
 * Porcentaje de caída entre el litraje previo y el actual.
 * Antes duplicada de forma idéntica en crearL.php y ActualizarL.php.
 * El umbral de alerta (>= 8) sigue decidiéndose en cada controlador.
 */
function calcular_caida_produccion(float $litrosPrevios, float $litrosActuales): float
{
    return $litrosPrevios > 0 ? (($litrosPrevios - $litrosActuales) / $litrosPrevios) * 100 : 0.0;
}

/**
 * Índice (0-based) de la quincena de 15 días en la que cae $fecha,
 * contando desde $fechaInicio. Antes inline en historial_quincenas_leche.php.
 */
function indice_quincena(DateTime $fechaInicio, DateTime $fecha): int
{
    $dias = (int) $fechaInicio->diff($fecha)->format('%a');
    return (int) floor($dias / 15);
}

function db_prepare(mysqli $con, string $sql, string $types = '', array $params = []): mysqli_stmt
{
    $stmt = mysqli_prepare($con, $sql);
    if (!$stmt) {
        app_log('SQL prepare failed: ' . mysqli_error($con) . ' | ' . $sql);
        throw new RuntimeException('No fue posible preparar la consulta.');
    }
    if ($types !== '') {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    return $stmt;
}

function db_execute(mysqli $con, string $sql, string $types = '', array $params = []): mysqli_stmt
{
    $stmt = db_prepare($con, $sql, $types, $params);
    if (!mysqli_stmt_execute($stmt)) {
        app_log('SQL execute failed: ' . mysqli_stmt_error($stmt) . ' | ' . $sql);
        throw new RuntimeException('No fue posible ejecutar la consulta.');
    }
    return $stmt;
}

function db_result(mysqli $con, string $sql, string $types = '', array $params = []): mysqli_result
{
    $stmt = db_execute($con, $sql, $types, $params);
    $result = mysqli_stmt_get_result($stmt);
    if (!$result) {
        app_log('SQL result failed: ' . mysqli_stmt_error($stmt) . ' | ' . $sql);
        throw new RuntimeException('No fue posible obtener resultados.');
    }
    return $result;
}

function db_one(mysqli $con, string $sql, string $types = '', array $params = []): ?array
{
    $result = db_result($con, $sql, $types, $params);
    $row = mysqli_fetch_assoc($result);
    return $row ?: null;
}

function db_value(mysqli $con, string $sql, string $types = '', array $params = [])
{
    $row = db_one($con, $sql, $types, $params);
    return $row ? array_values($row)[0] : null;
}

function json_response($payload, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

function handle_controller_error(Throwable $e, string $redirect): void
{
    app_log($e->getMessage());
    $_SESSION['toast'] = ['type' => 'alert', 'message' => 'No fue posible procesar la solicitud.'];
    header('Location: ' . $redirect);
    exit();
}

function save_uploaded_image(array $file, int $usuarioId, string $fieldLabel = 'foto'): ?string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        throw new InvalidArgumentException("No se pudo subir la $fieldLabel.");
    }
    if (($file['size'] ?? 0) > 2 * 1024 * 1024) {
        throw new InvalidArgumentException("La $fieldLabel supera el tamano maximo de 2MB.");
    }

    $permitidos = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/avif' => 'avif',
    ];
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    if (!isset($permitidos[$mime])) {
        throw new InvalidArgumentException("La $fieldLabel debe estar en formato JPG, PNG, WEBP o AVIF.");
    }

    $directorio = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Assets' . DIRECTORY_SEPARATOR . 'Imagenes' . DIRECTORY_SEPARATOR . 'vacas';
    if (!is_dir($directorio)) {
        mkdir($directorio, 0775, true);
    }

    $nombreArchivo = 'vaca_' . $usuarioId . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $permitidos[$mime];
    $rutaFisica = $directorio . DIRECTORY_SEPARATOR . $nombreArchivo;

    if (!move_uploaded_file($file['tmp_name'], $rutaFisica)) {
        throw new RuntimeException("No se pudo guardar la $fieldLabel.");
    }

    return '../Assets/Imagenes/vacas/' . $nombreArchivo;
}

function delete_cow_image(?string $relativePath): void
{
    if (!$relativePath) {
        return;
    }
    $baseDir = realpath(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Assets' . DIRECTORY_SEPARATOR . 'Imagenes' . DIRECTORY_SEPARATOR . 'vacas');
    $fileName = basename(str_replace(['../Assets/Imagenes/vacas/', '..\\Assets\\Imagenes\\vacas\\'], '', $relativePath));
    $path = $baseDir ? $baseDir . DIRECTORY_SEPARATOR . $fileName : '';
    $real = $path && file_exists($path) ? realpath($path) : false;
    if ($baseDir && $real && strpos($real, $baseDir) === 0 && is_file($real)) {
        @unlink($real);
    }
}

// ─────────────────────────────────────────────────────────────
// RATE LIMITING DE LOGIN — basado en sesión PHP
// Sin necesidad de tabla adicional en BD.
//
// Configuración (sobrescribible desde .env):
//   LOGIN_MAX_ATTEMPTS  = máximo de intentos fallidos (default 5)
//   LOGIN_LOCKOUT_SECS  = segundos de bloqueo (default 900 = 15 min)
// ─────────────────────────────────────────────────────────────

/**
 * Verifica si el cliente actual está bloqueado por demasiados intentos.
 * Devuelve los segundos restantes de bloqueo, o 0 si no está bloqueado.
 */
function login_rate_limit_check(): int
{
    start_secure_session();

    $lockoutSecs = (int)(env_value('LOGIN_LOCKOUT_SECS', '900'));
    $maxAttempts = (int)(env_value('LOGIN_MAX_ATTEMPTS', '5'));
    $ip          = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $key         = 'rl_' . md5($ip);

    $data = $_SESSION[$key] ?? ['attempts' => 0, 'locked_until' => 0];

    // Si hay un bloqueo activo, calcular segundos restantes
    if ($data['locked_until'] > time()) {
        return (int)($data['locked_until'] - time());
    }

    // Si el bloqueo expiró, resetear contador automáticamente
    if ($data['locked_until'] > 0 && $data['locked_until'] <= time()) {
        $_SESSION[$key] = ['attempts' => 0, 'locked_until' => 0];
        return 0;
    }

    return 0;
}

/**
 * Registra un intento fallido de login.
 * Si se supera el límite, establece el bloqueo temporal.
 * Retorna los segundos de bloqueo si acaba de activarse, 0 si no.
 */
function login_rate_limit_record_fail(string $correo): int
{
    start_secure_session();

    $lockoutSecs = (int)(env_value('LOGIN_LOCKOUT_SECS', '900'));
    $maxAttempts = (int)(env_value('LOGIN_MAX_ATTEMPTS', '5'));
    $ip          = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $key         = 'rl_' . md5($ip);

    $data = $_SESSION[$key] ?? ['attempts' => 0, 'locked_until' => 0];

    // Si ya estaba bloqueado, no incrementar más
    if ($data['locked_until'] > time()) {
        return (int)($data['locked_until'] - time());
    }

    $data['attempts']++;

    if ($data['attempts'] >= $maxAttempts) {
        $data['locked_until'] = time() + $lockoutSecs;
        $_SESSION[$key] = $data;
        app_log(sprintf(
            '[RATE-LIMIT] IP %s bloqueada por %ds tras %d intentos fallidos (ultimo correo: %s)',
            $ip,
            $lockoutSecs,
            $data['attempts'],
            $correo
        ));
        return $lockoutSecs;
    }

    $_SESSION[$key] = $data;
    app_log(sprintf(
        '[LOGIN-FAIL] IP %s — intento %d/%d (correo: %s)',
        $ip,
        $data['attempts'],
        $maxAttempts,
        $correo
    ));
    return 0;
}

/**
 * Resetea el contador de intentos fallidos tras un login exitoso.
 */
function login_rate_limit_reset(): void
{
    start_secure_session();
    $ip  = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $key = 'rl_' . md5($ip);
    unset($_SESSION[$key]);
}
?>
