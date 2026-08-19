<?php
require_once '../Config/conexion.php';
start_secure_session();
$con = conexion();

$mensaje = '';
$tipo = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        require_csrf();
        $correo = input_email($_POST, 'correo', 255);
        $usuario = db_one($con, 'SELECT id FROM usuarios WHERE correo = ? AND activo = 1 LIMIT 1', 's', [$correo]);

        if ($usuario) {
            $token = bin2hex(random_bytes(32));
            $tokenHash = hash('sha256', $token);
            $url = app_url('Login/restablecer_contrasena.php?token=' . rawurlencode($token));

            db_execute($con, 'DELETE FROM password_reset_tokens WHERE usuario_id = ?', 'i', [(int) $usuario['id']]);
            db_execute(
                $con,
                'INSERT INTO password_reset_tokens (usuario_id, token_hash, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 30 MINUTE))',
                'is',
                [(int) $usuario['id'], $tokenHash]
            );

            if (!send_password_reset_email($correo, $url)) {
                db_execute($con, 'DELETE FROM password_reset_tokens WHERE token_hash = ?', 's', [$tokenHash]);
                throw new RuntimeException('No fue posible enviar el correo de recuperación.');
            }
            app_log('[PASSWORD-RESET] Solicitud procesada para usuario ID ' . (int) $usuario['id']);
        }
    } catch (Throwable $e) {
        app_log('[PASSWORD-RESET] ' . $e->getMessage());
    }

    // El mismo mensaje evita revelar si un correo tiene cuenta o está activo.
    $mensaje = 'Si el correo está registrado, recibirás un enlace para restablecer la contraseña.';
    $tipo = 'success';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar contraseña | AgroControl</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../Css/iniciarsesion.css">
</head>
<body>
    <main class="auth-panel" style="width:100%; border-left:0;">
        <section class="auth-card">
            <div class="card-logo"><span class="card-logo-text">AGRO<em>CONTROL</em></span></div>
            <h1 class="card-title">Recuperar contraseña</h1>
            <p class="card-subtitle">Ingresa tu correo y te enviaremos un enlace válido por 30 minutos.</p>
            <form method="POST" class="auth-form" novalidate>
                <?= csrf_field() ?>
                <div class="field-wrap">
                    <label class="field-label" for="correo">Correo electrónico</label>
                    <div class="field-input-wrap"><input type="email" id="correo" name="correo" maxlength="255" autocomplete="email" required></div>
                </div>
                <button class="btn-submit" type="submit">Enviar enlace</button>
            </form>
            <p class="card-footer"><a class="forgot-link" href="iniciar_sesion.php">Volver a iniciar sesión</a></p>
            <?php if ($mensaje !== ''): ?><p class="card-subtitle" style="margin-top:18px; color:#86EFAC;"><?= e($mensaje) ?></p><?php endif; ?>
        </section>
    </main>
</body>
</html>
