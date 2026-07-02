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

$userName = htmlspecialchars($_SESSION['nombre'] ?? 'Usuario', ENT_QUOTES, 'UTF-8');
$userRole = htmlspecialchars($_SESSION['rol'] ?? 'usuario', ENT_QUOTES, 'UTF-8');
$userInitial = strtoupper(mb_substr($_SESSION['nombre'] ?? 'U', 0, 1, 'UTF-8'));

function active_class(string $href, string $currentPage): string {
    return $href === $currentPage ? ' active' : '';
}
?>
<aside id="sidebar" class="sidebar">
    <div class="logo">
        <div class="logo-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="#061006" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 2C6 2 3 7 3 12s3 10 9 10 9-5 9-10S18 2 12 2"/>
                <path d="M8 14s1.5 2 4 2 4-2 4-2"/>
                <circle cx="9" cy="9" r=".8" fill="#061006"/>
                <circle cx="15" cy="9" r=".8" fill="#061006"/>
            </svg>
        </div>
        <div class="logo-text">
            <div class="logo-name">AGRO<span>CONTROL</span></div>
            <div class="logo-sub">Gestión Ganadera</div>
        </div>
    </div>

    <div class="nav-section">
        <div class="menu-label">Principal</div>
        <div class="menu">
            <a href="Dashboard.php" class="<?php echo trim(active_class('Dashboard.php', $currentPage)); ?>">
                <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/></svg>
                Dashboard
            </a>
            <a href="Registro_Vacas.php" class="<?php echo trim(active_class('Registro_Vacas.php', $currentPage)); ?>">
                <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><ellipse cx="12" cy="8" rx="7" ry="5"/><path d="M5 13c0 3.3 3.1 6 7 6s7-2.7 7-6"/></svg>
                Gestión de Vacas
            </a>
            <a href="produccion_lechera.php" class="<?php echo trim(active_class('produccion_lechera.php', $currentPage)); ?>">
                <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M3 9l9-7 9 7v11a1 1 0 01-1 1H4a1 1 0 01-1-1z"/><polyline points="9,22 9,12 15,12 15,22"/></svg>
                Producción Lechera
            </a>
            <a href="potrero.php" class="<?php echo trim(active_class('potrero.php', $currentPage)); ?>">
                <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M17 3a2.83 2.83 0 114 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg>
                Potreros y Mangas
            </a>
            <a href="vacunaciones.html" class="<?php echo trim(active_class('vacunaciones.html', $currentPage)); ?>">
                <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M19 3l2 2-7 7"/><path d="M17 5l2 2"/><path d="M3 21l9-9"/><path d="M14.5 5.5l-11 11 4 4 11-11z"/></svg>
                Vacunaciones
            </a>
        </div>
    </div>

    <div class="nav-divider"></div>

    <div class="nav-section">
        <div class="menu-label">Sistema</div>
        <div class="menu">
            <a href="logout.php" class="logout-link">
                <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16,17 21,12 16,7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                Cerrar sesión
            </a>
        </div>
    </div>
</aside>
