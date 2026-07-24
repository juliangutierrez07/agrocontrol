<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once("../Config/conexion.php");
require_login();

$currentPage = basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
if (!empty($_GET['current'])) {
    $currentPage = basename($_GET['current']);
}

$userName    = htmlspecialchars($_SESSION['nombre'] ?? 'Usuario', ENT_QUOTES, 'UTF-8');
$userRole    = htmlspecialchars($_SESSION['rol'] ?? 'usuario', ENT_QUOTES, 'UTF-8');

// Tarjeta de contexto
$_uid           = isset($_SESSION['id']) ? (int)$_SESSION['id'] : 0;
$_con           = conexion();
$_totalAnimales = (int)(db_value($_con, "SELECT COUNT(*) FROM vacas    WHERE usuario_id = ?", "i", [$_uid]) ?? 0);
$_totalPotreros = (int)(db_value($_con, "SELECT COUNT(*) FROM potreros WHERE usuario_id = ?", "i", [$_uid]) ?? 0);

function sb_active(string $href, string $cur): string {
    return $href === $cur ? 'sb-item active' : 'sb-item';
}
?>
<aside id="sidebar" class="sidebar" aria-label="Navegación principal">

    <!-- ══ HEADER ══ -->
    <div class="sb-header">
        <div class="sb-logo-badge" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="#0B1220" stroke-width="2.5"
                 stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 2C6 2 3 7 3 12s3 10 9 10 9-5 9-10S18 2 12 2"/>
                <path d="M8 15s1.5 2 4 2 4-2 4-2"/>
                <circle cx="9.5"  cy="9.5" r=".9" fill="#0B1220"/>
                <circle cx="14.5" cy="9.5" r=".9" fill="#0B1220"/>
            </svg>
        </div>
        <div class="sb-wordmark">
            <div class="sb-wordmark-name">AGRO<span>CONTROL</span></div>
            <div class="sb-wordmark-sub">GESTIÓN GANADERA</div>
        </div>
    </div>

    <!-- ══ NAV ══ -->
    <nav class="sb-body" role="navigation">

        <span class="sb-section-label">Principal</span>

        <a href="Dashboard.php"
           class="<?php echo sb_active('Dashboard.php', $currentPage); ?>"
           title="Dashboard">
            <span class="sb-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"
                     stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3"  y="3"  width="7" height="7" rx="1.5"/>
                    <rect x="14" y="3"  width="7" height="7" rx="1.5"/>
                    <rect x="14" y="14" width="7" height="7" rx="1.5"/>
                    <rect x="3"  y="14" width="7" height="7" rx="1.5"/>
                </svg>
            </span>
            Dashboard
        </a>

        <a href="Registro_Vacas.php"
           class="<?php echo sb_active('Registro_Vacas.php', $currentPage); ?>"
           title="Gestión de vacas">
            <span class="sb-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"
                     stroke-linecap="round">
                    <ellipse cx="12" cy="8" rx="7" ry="5"/>
                    <path d="M5 13c0 3.3 3.1 6 7 6s7-2.7 7-6"/>
                </svg>
            </span>
            Gestión de vacas
        </a>

        <a href="produccion_lechera.php"
           class="<?php echo sb_active('produccion_lechera.php', $currentPage); ?>"
           title="Producción lechera">
            <span class="sb-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"
                     stroke-linecap="round">
                    <path d="M10 2C5 2 3 7.5 3 11.5A7 7 0 0017 11.5C17 7.5 15 2 10 2z"/>
                </svg>
            </span>
            Producción lechera
        </a>

        <a href="potrero.php"
           class="<?php echo sb_active('potrero.php', $currentPage); ?>"
           title="Potreros y mangas">
            <span class="sb-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"
                     stroke-linecap="round">
                    <rect x="3" y="3" width="18" height="18" rx="2"/>
                    <line x1="9"  y1="3"  x2="9"  y2="21"/>
                    <line x1="15" y1="3"  x2="15" y2="21"/>
                    <line x1="3"  y1="9"  x2="21" y2="9"/>
                    <line x1="3"  y1="15" x2="21" y2="15"/>
                </svg>
            </span>
            Potreros y mangas
        </a>

        <a href="vacunaciones.html"
           class="<?php echo sb_active('vacunaciones.html', $currentPage); ?>"
           title="Vacunaciones">
            <span class="sb-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"
                     stroke-linecap="round">
                    <path d="M19 3l2 2-7 7"/>
                    <path d="M17 5l2 2"/>
                    <path d="M3 21l9-9"/>
                    <path d="M14.5 5.5l-11 11 4 4 11-11z"/>
                </svg>
            </span>
            Vacunaciones
        </a>

        <?php if (current_user_role() === 'administrador'): ?>
        <div class="sb-divider"></div>
        <span class="sb-section-label">Administración</span>

        <a href="usuarios.php"
           class="<?php echo sb_active('usuarios.php', $currentPage); ?>"
           title="Gestión de usuarios">
            <span class="sb-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"
                     stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 00-3-3.87"/>
                    <path d="M16 3.13a4 4 0 010 7.75"/>
                </svg>
            </span>
            Gestión de usuarios
        </a>
        <?php endif; ?>

        <div class="sb-divider"></div>
        <span class="sb-section-label">Sistema</span>

        <a href="logout.php" class="sb-item sb-logout" title="Cerrar sesión">
            <span class="sb-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"
                     stroke-linecap="round">
                    <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/>
                    <polyline points="16,17 21,12 16,7"/>
                    <line x1="21" y1="12" x2="9" y2="12"/>
                </svg>
            </span>
            Cerrar sesión
        </a>

    </nav>

    <!-- ══ TARJETA DE CONTEXTO ══ -->
    <div class="sb-context-card" aria-label="Información del operador">
        <div class="sb-ctx-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round">
                <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>
                <circle cx="12" cy="7" r="4"/>
            </svg>
        </div>
        <div class="sb-ctx-info">
            <span class="sb-ctx-name"><?php echo $userName; ?></span>
            <span class="sb-ctx-meta"><?php echo $_totalAnimales; ?> animales · <?php echo $_totalPotreros; ?> potreros</span>
        </div>
    </div>

</aside>
