<?php
require_once '../Config/conexion.php';
start_secure_session();
$con = conexion();

$token = (string) ($_GET['token'] ?? $_POST['token'] ?? '');
$tokenValido = ctype_xdigit($token) && strlen($token) === 64;
$mensaje = '';
$tipo = '';
$transaccionIniciada = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        require_csrf();
        $password = input_string($_POST, 'password', 255);
        $confirmacion = input_string($_POST, 'confirmacion', 255);
        if (strlen($password) < 8 || !hash_equals($password, $confirmacion)) {
            throw new InvalidArgumentException('La contraseña debe tener al menos 8 caracteres y coincidir.');
        }
        if (!$tokenValido) {
            throw new InvalidArgumentException('El enlace no es válido.');
        }

        $tokenHash = hash('sha256', $token);
        mysqli_begin_transaction($con);
        $transaccionIniciada = true;
        $registro = db_one(
            $con,
            'SELECT pr.id, pr.usuario_id FROM password_reset_tokens pr JOIN usuarios u ON u.id = pr.usuario_id WHERE pr.token_hash = ? AND pr.used_at IS NULL AND pr.expires_at > NOW() AND u.activo = 1 LIMIT 1 FOR UPDATE',
            's',
            [$tokenHash]
        );
        if (!$registro) {
            throw new InvalidArgumentException('El enlace no es válido o ya venció.');
        }
        db_execute($con, 'UPDATE usuarios SET password = ? WHERE id = ?', 'si', [password_hash($password, PASSWORD_DEFAULT), (int) $registro['usuario_id']]);
        db_execute($con, 'UPDATE password_reset_tokens SET used_at = NOW() WHERE id = ?', 'i', [(int) $registro['id']]);
        db_execute($con, 'DELETE FROM password_reset_tokens WHERE usuario_id = ? AND id <> ?', 'ii', [(int) $registro['usuario_id'], (int) $registro['id']]);
        mysqli_commit($con);
        $transaccionIniciada = false;
        app_log('[PASSWORD-RESET] Contraseña restablecida para usuario ID ' . (int) $registro['usuario_id']);
        $mensaje = 'Contraseña actualizada. Ya puedes iniciar sesión.';
        $tipo = 'success';
    } catch (Throwable $e) {
        if ($transaccionIniciada) {
            mysqli_rollback($con);
        }
        app_log('[PASSWORD-RESET] ' . $e->getMessage());
        $mensaje = $e instanceof InvalidArgumentException ? $e->getMessage() : 'No fue posible restablecer la contraseña.';
        $tipo = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer contraseña | AgroControl</title>
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet"><link rel="stylesheet" href="../Css/iniciarsesion.css">
</head>
<body><main class="auth-panel" style="width:100%; border-left:0;"><section class="auth-card">
    <div class="card-logo"><span class="card-logo-text">AGRO<em>CONTROL</em></span></div>
    <h1 class="card-title">Nueva contraseña</h1>
    <?php if ($tipo === 'success'): ?>
        <p class="card-subtitle" style="color:#86EFAC;"><?= e($mensaje) ?></p><p class="card-footer"><a class="forgot-link" href="iniciar_sesion.php">Iniciar sesión</a></p>
    <?php elseif (!$tokenValido): ?>
        <p class="card-subtitle" style="color:#FCA5A5;">El enlace no es válido.</p><p class="card-footer"><a class="forgot-link" href="recuperar_contrasena.php">Solicitar otro enlace</a></p>
    <?php else: ?>
        <p class="card-subtitle">Elige una contraseña nueva de mínimo 8 caracteres.</p>
        <form method="POST" class="auth-form" novalidate>
            <?= csrf_field() ?><input type="hidden" name="token" value="<?= e($token) ?>">
            <div class="field-wrap"><label class="field-label" for="password">Nueva contraseña</label><div class="field-input-wrap"><input type="password" id="password" name="password" minlength="8" maxlength="255" autocomplete="new-password" required></div></div>
            <div class="field-wrap"><label class="field-label" for="confirmacion">Confirmar contraseña</label><div class="field-input-wrap"><input type="password" id="confirmacion" name="confirmacion" minlength="8" maxlength="255" autocomplete="new-password" required></div></div>
            <button class="btn-submit" type="submit">Guardar contraseña</button>
        </form>
        <?php if ($mensaje !== ''): ?><p class="card-subtitle" style="margin-top:18px; color:#FCA5A5;"><?= e($mensaje) ?></p><?php endif; ?>
    <?php endif; ?>
</section></main></body>
</html>
