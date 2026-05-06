<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgroControl — Control Ganadero 1.0</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet"/>
    <link rel="stylesheet" href="../Css/Home.css">
</head>
<body>

    <!-- HEADER -->
    <header id="header">
        <div class="logo">
            <div class="logo-mark">
                <svg viewBox="0 0 24 24" fill="none" stroke="#061006" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 2C6 2 3 7 3 12s3 10 9 10 9-5 9-10S18 2 12 2"/>
                    <path d="M8 14s1.5 2 4 2 4-2 4-2"/>
                    <circle cx="9" cy="9" r=".8" fill="#061006"/>
                    <circle cx="15" cy="9" r=".8" fill="#061006"/>
                </svg>
            </div>
            <span class="logo-text">AGRO<strong>CONTROL</strong></span>
        </div>



        <div class="header-actions">
            <a href="../Login/iniciar_sesion.php" class="btn-nav-login">Iniciar sesión</a>
        </div>
    </header>

    <!-- HERO -->
    <section class="hero">
        <div class="hero-bg">
            <div class="hero-blob blob-1"></div>
            <div class="hero-blob blob-2"></div>
            <div class="hero-grid"></div>
        </div>

        <div class="hero-inner">

            <!-- Pill badge -->
            <div class="hero-badge">
                <span class="badge-dot"></span>
                Control Ganadero 1.0 · Ahora disponible
            </div>

            <!-- Título -->
            <h1 class="hero-title">
                La plataforma #1 en<br>
                <span class="title-highlight">gestión ganadera</span>
            </h1>

            <!-- Descripción -->
            <p class="hero-desc">
                Controla vacas, producción de leche, nacimientos y potreros
                desde cualquier lugar. Datos en tiempo real, sin complicaciones.
            </p>

            <!-- CTAs -->
            <div class="hero-ctas">
                <a href="../Login/iniciar_sesion.php" class="btn-primary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4"/><polyline points="10,17 15,12 10,7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
                    Iniciar sesión
                </a>

            </div>

            <!-- Trust line -->
            <div class="hero-trust">
                <div class="trust-avatars">
                    <div class="ta ta-1">JG</div>
                    <div class="ta ta-2">MR</div>
                    <div class="ta ta-3">CA</div>
                    <div class="ta ta-4">+</div>
                </div>
                <span>Más de <strong>50.000</strong> ganaderos confían en AgroControl</span>
            </div>
        </div>

        <!-- STATS -->
        <div class="stats-bar">
            <div class="stat-item">
                <div class="stat-icon-wrap green">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><ellipse cx="12" cy="8" rx="7" ry="5"/><path d="M5 13c0 3.3 3.1 6 7 6s7-2.7 7-6"/></svg>
                </div>
                <div class="stat-text">
                    <div class="stat-num">+500.000</div>
                    <div class="stat-label">Animales registrados</div>
                </div>
            </div>
            <div class="stat-divider"></div>
            <div class="stat-item">
                <div class="stat-icon-wrap amber">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 2C6 2 3 9 3 14a9 9 0 0018 0c0-5-3-12-9-12z"/></svg>
                </div>
                <div class="stat-text">
                    <div class="stat-num">+110.000</div>
                    <div class="stat-label">Partos registrados</div>
                </div>
            </div>
            <div class="stat-divider"></div>
            <div class="stat-item">
                <div class="stat-icon-wrap blue">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="7" r="4"/><path d="M5.5 20a6.5 6.5 0 0113 0"/></svg>
                </div>
                <div class="stat-text">
                    <div class="stat-num">+50.000</div>
                    <div class="stat-label">Usuarios activos</div>
                </div>
            </div>
            <div class="stat-divider"></div>
            <div class="stat-item">
                <div class="stat-icon-wrap green">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 010 20M12 2a15.3 15.3 0 000 20"/></svg>
                </div>
                <div class="stat-text">
                    <div class="stat-num">1</div>
                    <div class="stat-label">País</div>
                </div>
            </div>
        </div>

        <!-- REDES SOCIALES -->
        <div class="social-bar">
            <span class="social-label">Síguenos</span>
            <div class="social-links">
                <a href="#" class="social-btn" title="TikTok">
                    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-2.88 2.5 2.89 2.89 0 01-2.89-2.89 2.89 2.89 0 012.89-2.89c.28 0 .54.04.79.1V9.01a6.3 6.3 0 00-.79-.05 6.34 6.34 0 00-6.34 6.34 6.34 6.34 0 006.34 6.34 6.34 6.34 0 006.33-6.34V8.69a8.18 8.18 0 004.78 1.52V6.75a4.85 4.85 0 01-1.01-.06z"/></svg>
                </a>
                <a href="#" class="social-btn" title="Instagram">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="2" y="2" width="20" height="20" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r=".5" fill="currentColor"/></svg>
                </a>
                <a href="#" class="social-btn" title="Facebook">
                    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/></svg>
                </a>
                <a href="#" class="social-btn" title="YouTube">
                    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M22.54 6.42a2.78 2.78 0 00-1.95-1.96C18.88 4 12 4 12 4s-6.88 0-8.59.46a2.78 2.78 0 00-1.95 1.96A29 29 0 001 12a29 29 0 00.46 5.58 2.78 2.78 0 001.95 1.95C5.12 20 12 20 12 20s6.88 0 8.59-.47a2.78 2.78 0 001.95-1.95A29 29 0 0023 12a29 29 0 00-.46-5.58zM9.75 15.02V8.98L15.5 12l-5.75 3.02z"/></svg>
                </a>
            </div>
        </div>

    </section>

    <!-- FEATURES -->
    <section class="features">
        <div class="features-inner">
            <div class="section-badge">Funcionalidades</div>
            <h2 class="section-title">Todo lo que necesitas para tu finca</h2>
            <p class="section-desc">Una sola plataforma para gestionar cada aspecto de tu operación ganadera.</p>

            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon fi-green">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><ellipse cx="12" cy="8" rx="7" ry="5"/><path d="M5 13c0 3.3 3.1 6 7 6s7-2.7 7-6"/></svg>
                    </div>
                    <h3>Gestión de Vacas</h3>
                    <p>Registra, edita y filtra, Edad y estado productivo en segundos.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon fi-blue">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 2C6 2 3 9 3 14a9 9 0 0018 0c0-5-3-12-9-12z"/></svg>
                    </div>
                    <h3>Producción Lechera</h3>
                    <p>Registra litros diarios por vaca y lleva tu control </p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon fi-amber">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="7" r="4"/><path d="M5.5 20a6.5 6.5 0 0113 0"/></svg>
                    </div>
                    <h3>Terneritos</h3>
                    <p>Controla nacimientos,estado de salud de cada ternero desde el primer día.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon fi-green">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M17 3a2.83 2.83 0 114 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg>
                    </div>
                    <h3>Potreros y Mangas</h3>
                    <p>Organiza y rotaciona tus potreros para maximizar el pastoreo y la salud del suelo.</p>
                </div>
            </div>
        </div>
    </section>

    <script>
        /* Header scroll effect */
        window.addEventListener('scroll', function(){
            document.getElementById('header').classList.toggle('scrolled', window.scrollY > 40);
        });
    </script>

</body>
</html>