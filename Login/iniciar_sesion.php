<?php
require_once("../Config/conexion.php");
start_secure_session();
$con = conexion();

$mensaje   = "";
$tipo      = "";
$redirigir = false;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
        require_csrf();

        $segundosBloqueo = login_rate_limit_check();
        if ($segundosBloqueo > 0) {
            $minutos = ceil($segundosBloqueo / 60);
            $mensaje = "Demasiados intentos fallidos. Espera {$minutos} minuto(s) para volver a intentarlo.";
            $tipo    = "error";
            app_log('[LOGIN-BLOCKED] IP ' . ($_SERVER['REMOTE_ADDR'] ?? '?') . ' intento acceder durante bloqueo activo.');
        } else {
            $correo   = input_email($_POST, 'correo');
            $password = input_string($_POST, 'password', 255);

            $usuario        = db_one($con, "SELECT * FROM usuarios WHERE correo = ? LIMIT 1", "s", [$correo]);
            $passwordValida = false;

            if ($usuario) {
                $hashActual     = (string)($usuario['password'] ?? '');
                $passwordValida = password_verify($password, $hashActual);

                if (!$passwordValida && hash_equals($hashActual, $password)) {
                    $passwordValida = true;
                    $nuevoHash = password_hash($password, PASSWORD_DEFAULT);
                    db_execute($con, "UPDATE usuarios SET password = ? WHERE id = ?", "si", [$nuevoHash, (int)$usuario['id']]);
                }
            }

            if ($usuario && $passwordValida) {
                login_rate_limit_reset();
                session_regenerate_id(true);
                $_SESSION['id']     = (int)$usuario['id'];
                $_SESSION['nombre'] = $usuario['nombre'];
                $_SESSION['rol']    = $usuario['rol'] ?? 'usuario';

                $mensaje   = "Bienvenido al sistema";
                $tipo      = "success";
                $redirigir = true;
            } else {
                $correoLog = isset($correo) ? $correo : '';
                $bloqueado = login_rate_limit_record_fail($correoLog);
                if ($bloqueado > 0) {
                    $minutos = ceil($bloqueado / 60);
                    $mensaje = "Cuenta bloqueada temporalmente por {$minutos} minuto(s) por múltiples intentos fallidos.";
                } else {
                    $mensaje = "Correo o contraseña incorrectos";
                }
                $tipo = "error";
            }
        }
    } catch (Throwable $e) {
        app_log($e->getMessage());
        $mensaje = "No fue posible iniciar sesión";
        $tipo    = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgroControl | Iniciar Sesión</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../Css/iniciarsesion.css">
</head>
<body>

    <!-- ══════════════════════════════════════
         PANEL IZQUIERDO — Hero visual
         ══════════════════════════════════════ -->
    <div class="hero">

        <!-- Imagen de fondo con overlay -->
        <div class="hero-bg">
            <div class="hero-overlay"></div>
            <!-- Partículas decorativas -->
            <div class="hero-particles">
                <span class="particle p1"></span>
                <span class="particle p2"></span>
                <span class="particle p3"></span>
                <span class="particle p4"></span>
                <span class="particle p5"></span>
            </div>
        </div>

        <!-- Contenido del hero -->
        <div class="hero-content">

            <!-- Badge -->
            <div class="hero-badge">
                <span class="badge-dot"></span>
                Sistema activo · Versión 1.0
            </div>

            <!-- Branding -->
            <div class="hero-brand">
                <div class="hero-logo-icon">
                    <svg viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M14 2C8 2 4 7 4 12s3.5 10 10 10 10-5 10-10S20 2 14 2" stroke="#0F172A" stroke-width="2" stroke-linecap="round"/>
                        <path d="M9 15s1.5 2.5 5 2.5 5-2.5 5-2.5" stroke="#0F172A" stroke-width="2" stroke-linecap="round"/>
                        <circle cx="10" cy="10" r="1" fill="#0F172A"/>
                        <circle cx="18" cy="10" r="1" fill="#0F172A"/>
                    </svg>
                </div>
                <h1 class="hero-title">
                    AGRO<span>CONTROL</span>
                </h1>
            </div>

            <p class="hero-subtitle">
                Sistema inteligente para gestión ganadera.<br>
                Tecnología de punta aplicada al campo.
            </p>

            <!-- Stats -->
            <div class="hero-stats">
                <div class="stat-item">
                    <div class="stat-icon">
                        <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
                            <path d="M3 10l2-2 2 2 3-4 4 5"/>
                            <rect x="1" y="1" width="18" height="18" rx="3"/>
                        </svg>
                    </div>
                    <div class="stat-body">
                        <span class="stat-num" data-target="248">0</span>
                        <span class="stat-label">Fincas</span>
                    </div>
                </div>
                <div class="stat-divider"></div>
                <div class="stat-item">
                    <div class="stat-icon">
                        <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
                            <ellipse cx="10" cy="7" rx="6" ry="4"/>
                            <path d="M4 11c0 2.8 2.7 5 6 5s6-2.2 6-5"/>
                        </svg>
                    </div>
                    <div class="stat-body">
                        <span class="stat-num" data-target="12000" data-suffix="K">0</span>
                        <span class="stat-label">Animales</span>
                    </div>
                </div>
                <div class="stat-divider"></div>
                <div class="stat-item">
                    <div class="stat-icon">
                        <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
                            <circle cx="10" cy="10" r="8"/>
                            <path d="M10 6v4l2.5 2.5"/>
                        </svg>
                    </div>
                    <div class="stat-body">
                        <span class="stat-num" data-target="99" data-suffix="%">0</span>
                        <span class="stat-label">Disponibilidad</span>
                    </div>
                </div>
            </div>

            <!-- Features -->
            <div class="hero-features">
                <div class="feature-tag">
                    <svg viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="2,7 5.5,10.5 12,3"/></svg>
                    Trazabilidad completa
                </div>
                <div class="feature-tag">
                    <svg viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="2,7 5.5,10.5 12,3"/></svg>
                    Control lechero
                </div>
                <div class="feature-tag">
                    <svg viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="2,7 5.5,10.5 12,3"/></svg>
                    Vacunaciones ICA
                </div>
                <div class="feature-tag">
                    <svg viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="2,7 5.5,10.5 12,3"/></svg>
                    Gestión de potreros
                </div>
            </div>

        </div><!-- /hero-content -->

        <!-- Degradado inferior -->
        <div class="hero-footer-grad"></div>

    </div><!-- /hero -->

    <!-- ══════════════════════════════════════
         PANEL DERECHO — Formulario
         ══════════════════════════════════════ -->
    <div class="auth-panel">
        <div class="auth-card">

            <!-- Logo card -->
            <div class="card-logo">
                <div class="card-logo-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="#0F172A" stroke-width="2.2" stroke-linecap="round">
                        <path d="M12 2C6 2 3 7 3 12s3 10 9 10 9-5 9-10S18 2 12 2"/>
                        <path d="M8 15s1.5 2 4 2 4-2 4-2"/>
                        <circle cx="9.5" cy="9.5" r=".8" fill="#0F172A"/>
                        <circle cx="14.5" cy="9.5" r=".8" fill="#0F172A"/>
                    </svg>
                </div>
                <span class="card-logo-text">AGRO<em>CONTROL</em></span>
            </div>

            <h2 class="card-title">Iniciar sesión</h2>
            <p class="card-subtitle">Ingrese sus credenciales para acceder</p>

            <!-- Formulario -->
            <form action="iniciar_sesion.php" method="POST" class="auth-form" autocomplete="off">
                <?php echo csrf_field(); ?>

                <!-- Campo correo -->
                <div class="field-wrap">
                    <label for="correo" class="field-label">Correo electrónico</label>
                    <div class="field-input-wrap">
                        <span class="field-icon">
                            <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
                                <rect x="2" y="4" width="16" height="13" rx="2"/>
                                <path d="M2 7l8 5 8-5"/>
                            </svg>
                        </span>
                        <input
                            type="email"
                            id="correo"
                            name="correo"
                            placeholder="usuario@ejemplo.com"
                            maxlength="255"
                            autocomplete="email"
                            required
                        >
                    </div>
                </div>

                <!-- Campo contraseña -->
                <div class="field-wrap">
                    <div class="field-label-row">
                        <label for="password" class="field-label">Contraseña</label>
                        <a href="#" class="forgot-link">¿Olvidó su contraseña?</a>
                    </div>
                    <div class="field-input-wrap">
                        <span class="field-icon">
                            <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
                                <rect x="4" y="9" width="12" height="9" rx="2"/>
                                <path d="M7 9V6a3 3 0 016 0v3"/>
                                <circle cx="10" cy="14" r="1" fill="currentColor"/>
                            </svg>
                        </span>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="••••••••••"
                            maxlength="255"
                            autocomplete="current-password"
                            required
                        >
                        <button type="button" class="toggle-pass" id="togglePass" aria-label="Mostrar contraseña" tabindex="-1">
                            <!-- Ojo abierto -->
                            <svg class="eye-show" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
                                <path d="M1 10s3.5-6 9-6 9 6 9 6-3.5 6-9 6-9-6-9-6z"/>
                                <circle cx="10" cy="10" r="2.5"/>
                            </svg>
                            <!-- Ojo cerrado -->
                            <svg class="eye-hide" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" style="display:none">
                                <path d="M1 1l18 18M8.5 8.6A2.5 2.5 0 0013.4 13.5M6.4 6.4C3.7 7.8 2 10 2 10s3.5 6 8 6a8.1 8.1 0 004.1-1.1M10 4c4.5 0 8 6 8 6a13.2 13.2 0 01-2 2.7"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Recordarme -->
                <div class="remember-row">
                    <label class="checkbox-label" for="remember">
                        <input type="checkbox" id="remember" name="remember">
                        <span class="checkbox-custom"></span>
                        Recordarme
                    </label>
                </div>

                <!-- Botón -->
                <button type="submit" name="ingresar" class="btn-submit">
                    <span class="btn-text">Ingresar</span>
                    <svg class="btn-arrow" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round">
                        <path d="M4 10h12M11 5l6 5-6 5"/>
                    </svg>
                </button>

            </form>

            <!-- Footer card -->
            <p class="card-footer">
                AgroControl &copy; <?php echo date('Y'); ?> · Sistema de gestión ganadera
            </p>

        </div>
    </div>

    <!-- Toast -->
    <div id="toast" class="toast" role="alert" aria-live="polite"></div>

    <script>
    /* ── ANIMACIÓN CONTADORES ── */
    function animateCounter(el) {
        const target  = parseInt(el.dataset.target, 10);
        const suffix  = el.dataset.suffix || '';
        const dur     = 1800;
        const step    = 16;
        const steps   = Math.ceil(dur / step);
        let   current = 0;
        let   count   = 0;

        const isK = suffix === 'K';
        const displayTarget = isK ? target / 1000 : target;

        const timer = setInterval(() => {
            count++;
            current = Math.round(displayTarget * (count / steps));
            el.textContent = current + suffix;
            if (count >= steps) {
                el.textContent = displayTarget + suffix;
                clearInterval(timer);
            }
        }, step);
    }

    window.addEventListener('DOMContentLoaded', () => {
        setTimeout(() => {
            document.querySelectorAll('.stat-num').forEach(animateCounter);
        }, 600);
    });

    /* ── TOGGLE CONTRASEÑA ── */
    const togglePass = document.getElementById('togglePass');
    const passInput  = document.getElementById('password');
    if (togglePass && passInput) {
        togglePass.addEventListener('click', () => {
            const isPass = passInput.type === 'password';
            passInput.type = isPass ? 'text' : 'password';
            togglePass.querySelector('.eye-show').style.display = isPass ? 'none'  : '';
            togglePass.querySelector('.eye-hide').style.display = isPass ? ''      : 'none';
            passInput.focus();
        });
    }

    /* ── TOAST ── */
    function mostrarToast(mensaje, tipo) {
        const toast = document.getElementById('toast');
        toast.textContent = mensaje;
        toast.className   = 'toast toast-' + tipo + ' show';
        setTimeout(() => { toast.classList.remove('show'); }, 3800);
    }

    <?php if ($mensaje !== ''): ?>
    document.addEventListener('DOMContentLoaded', function () {
        mostrarToast(<?php echo json_encode($mensaje); ?>, <?php echo json_encode($tipo); ?>);
        <?php if ($redirigir): ?>
        setTimeout(function () {
            window.location.href = '../Pages/Dashboard.php';
        }, 1800);
        <?php endif; ?>
    });
    <?php endif; ?>
    </script>

</body>
</html>
