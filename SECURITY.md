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
- Whitelist en campos de estado de vaca: `['produccion', 'secado', 'enrazada']`

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

## Cabeceras HTTP recomendadas (pendiente implementar)

Añadir en `.htaccess` o en `Config/helpers.php` dentro de `app_init()`:

```apache
Header always set X-Content-Type-Options "nosniff"
Header always set X-Frame-Options "SAMEORIGIN"
Header always set X-XSS-Protection "1; mode=block"
Header always set Referrer-Policy "strict-origin-when-cross-origin"
Header always set Content-Security-Policy "default-src 'self'; script-src 'self' cdn.jsdelivr.net fonts.googleapis.com; style-src 'self' 'unsafe-inline' fonts.googleapis.com fonts.gstatic.com; img-src 'self' data:; font-src 'self' fonts.gstatic.com"
```

---

## Reporte de vulnerabilidades

Para reportar una vulnerabilidad de seguridad en este proyecto, contacta al equipo de desarrollo directamente. No publicar vulnerabilidades en issues públicos.
