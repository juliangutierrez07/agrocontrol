# SECURITY.md
> Políticas y mecanismos de seguridad — AgroControl

---

## Mecanismos implementados

### Autenticación
- Contraseñas almacenadas con `password_hash(PASSWORD_DEFAULT)` (bcrypt)
- Verificación con `password_verify()`
- Migración automática: si se detecta hash legacy (texto plano), se actualiza al hacer login
- `session_regenerate_id(true)` al autenticar exitosamente
- Sesiones con cookies `HttpOnly`, `SameSite=Lax`, `Secure` (cuando HTTPS)

### Autorización
- `require_login()` — verifica sesión activa, redirige si no hay
- `require_role(['rol'])` — verifica rol del usuario, redirige si no coincide
- `current_user_id()` — todos los queries de datos filtran por `usuario_id` de la sesión
- Panel de administrador protegido con `require_role(['administrador'])`

### CSRF
- Token generado con `bin2hex(random_bytes(32))` al iniciar sesión
- Token almacenado en sesión, nunca expuesto en URLs
- `csrf_field()` genera `<input type="hidden">` para formularios
- `csrf_validate()` + `hash_equals()` para comparación segura
- `require_csrf()` aplica en todos los endpoints POST/PUT/DELETE
- Acciones destructivas (eliminar) solo aceptan método POST

### SQL
- 100% Prepared Statements vía funciones centralizadas en `Config/helpers.php`
- Nunca se interpolan variables directamente en queries
- Charset `utf8mb4` forzado en la conexión

### XSS
- Función `e()` (`htmlspecialchars` con `ENT_QUOTES|ENT_SUBSTITUTE|UTF-8`) aplicada en todas las salidas dinámicas
- Datos de API JSON retornados con `json_encode()` (no como HTML)
- Mensajes de toast construidos con `json_encode()` en PHP, nunca con concatenación

### Subida de archivos
- Validación de tipo MIME real con `finfo` (no extensión del nombre)
- Tipos permitidos: `image/jpeg`, `image/png`, `image/webp`, `image/avif`
- Límite de tamaño: 2MB
- Nombres de archivo generados aleatoriamente (`bin2hex(random_bytes(4))`)
- Almacenamiento fuera de rutas ejecutables
- Prevención de path traversal en borrado con `realpath()`

### Errores y logs
- `display_errors` = 0 en producción (controlado por `APP_DEBUG` en `.env`)
- Todos los errores SQL y de aplicación van a `logs/app.log`
- Usuario nunca ve mensajes técnicos de error
- Handler global de errores PHP que redirige a `app_log()`

### Validación de entradas
- `input_string()` — trim, longitud máxima, campo obligatorio configurable
- `input_int()` — filtro `FILTER_VALIDATE_INT`, rango min/max
- `input_float()` — filtro `FILTER_VALIDATE_FLOAT`, rango min/max
- `input_date()` — formato `Y-m-d` verificado con `DateTime::createFromFormat`
- `input_email()` — `FILTER_VALIDATE_EMAIL`
- `input_solo_digitos()` — solo dígitos `0-9` (p. ej. código de vaca), conserva ceros a la izquierda
- `input_solo_letras()` — solo letras (con tildes/ñ vía `\p{L}`) y espacios (nombres, razas)
- Whitelist en campos de estado de vaca: `['produccion', 'secado', 'enrazada']`

### Cabeceras HTTP de seguridad
- **Fuente principal**: `Config/helpers.php::send_security_headers()`, invocada por `start_secure_session()` en toda página que inicia sesión (prácticamente toda la app, vía `require_login()`/`require_role()`).
- **Complemento en `.htaccess`** (raíz del proyecto, vía `mod_headers`): cubre las dos páginas que nunca ejecutan ese código PHP — `Pages/vacunaciones.html` (HTML estático) y `Pages/Home.php` (landing pública que no incluye `Config/helpers.php`). Usa la misma política que `helpers.php` para no crear dos fuentes de verdad divergentes. El resto de archivos estáticos (CSS/JS/imágenes) recibe solo `X-Content-Type-Options: nosniff` desde `.htaccess`, ya que el resto de cabeceras no aporta nada fuera del contexto de un documento HTML.
- `X-Content-Type-Options: nosniff`
- `X-Frame-Options: SAMEORIGIN`
- `X-XSS-Protection: 1; mode=block`
- `Referrer-Policy: strict-origin-when-cross-origin`
- `Content-Security-Policy`:
  ```
  default-src 'self';
  script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net;
  style-src 'self' 'unsafe-inline' https://fonts.googleapis.com;
  font-src 'self' https://fonts.gstatic.com;
  img-src 'self' data: https://images.unsplash.com;
  connect-src 'self';
  frame-ancestors 'none';
  base-uri 'self';
  form-action 'self'
  ```
- **Bug corregido**: el CSP de `helpers.php` no incluía `https://images.unsplash.com` en `img-src`, bloqueando silenciosamente la imagen de fondo de `Login/iniciarsesion.css`. Se agregó el dominio.
- **Deuda técnica conocida**: `script-src` incluye `'unsafe-inline'` porque el proyecto usa `<script>` inline y atributos `onclick`/`onsubmit` extensivamente en casi todas las vistas (`Dashboard`, `Registro_Vacas`, `produccion_lechera`, `potrero`, `usuarios`, `historial_leche`, `historial_quincenas_leche`, `Login/iniciar_sesion`, `Login/administrador`, `vacunaciones.html`). En `vacunaciones.html` además se generan atributos `onclick` dinámicamente desde JS (template strings), lo que requeriría reescribir esa lógica a `addEventListener`/delegación de eventos, no solo mover el `<script>` a un archivo externo. Retirar `'unsafe-inline'` sin antes hacer ese refactor rompe la aplicación.
- `style-src` incluye `'unsafe-inline'` porque el proyecto usa atributos `style="..."` inline en varias vistas (detectado en `Registro_Vacas.php`, `produccion_lechera.php`, `usuarios.php`, `potrero.php`, `Login/iniciar_sesion.php`, `vacunaciones.html`).
- **HSTS pendiente**: no se activó porque el proyecto corre en local sobre HTTP y producción (Dokploy) aún no tiene HTTPS configurado. Activarla sin HTTPS puede dejar el sitio inaccesible en algunos navegadores. Activar solo después de configurar HTTPS en Dokploy (ver `CHANGELOG.md`).

---

## Configuración recomendada en .env para producción

```
APP_ENV=production
APP_DEBUG=false
DB_HOST=localhost
DB_USER=<usuario_dedicado>
DB_PASS=<password_fuerte>
DB_NAME=agrocontrol
DB_CHARSET=utf8mb4
```

El usuario de base de datos debe tener solo los permisos necesarios (SELECT, INSERT, UPDATE, DELETE) sobre la base `agrocontrol`. No usar `root`.

---

## Reporte de vulnerabilidades

Para reportar una vulnerabilidad de seguridad en este proyecto, contacta al equipo de desarrollo directamente. No publicar vulnerabilidades en issues públicos.
