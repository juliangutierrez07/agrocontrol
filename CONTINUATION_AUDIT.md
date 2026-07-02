# CONTINUATION_AUDIT.md
> Auditoría de continuación — AgroControl  
> Fecha: 2026-06-18  
> Auditor: Kiro (arquitecto IA, continuación de auditoría previa)

---

## 1. CAMBIOS ENCONTRADOS (ya aplicados por auditoría anterior)

### Config/helpers.php
- ✅ Sistema completo de Prepared Statements (`db_prepare`, `db_execute`, `db_result`, `db_one`, `db_value`)
- ✅ `password_hash()` + `password_verify()` con migración automática de hashes legacy (text plano)
- ✅ Tokens CSRF reutilizables con `hash_equals()` + `csrf_field()` + `csrf_validate()` + `require_csrf()`
- ✅ Función `e()` para escape XSS (`htmlspecialchars` con `ENT_QUOTES|ENT_SUBSTITUTE`)
- ✅ Sesiones seguras: `HttpOnly`, `SameSite=Lax`, `Secure` condicional
- ✅ `session_regenerate_id(true)` al hacer login exitoso
- ✅ `require_login()` centralizado
- ✅ `require_role()` para control de acceso por roles
- ✅ Validaciones tipadas: `input_string()`, `input_int()`, `input_float()`, `input_date()`, `input_email()`
- ✅ Gestión segura de archivos: `save_uploaded_image()` con validación MIME real (`finfo`), límite 2MB, nombre aleatorio
- ✅ `delete_cow_image()` con path traversal prevention
- ✅ Sistema centralizado de logs (`app_log()`)
- ✅ Carga de `.env` sin exponer credenciales hardcodeadas
- ✅ `json_response()` con Content-Type correcto
- ✅ Singleton de conexión con `mysqli_set_charset(utf8mb4)`

### Config/conexion.php
- ✅ Conexión singleton segura
- ✅ Credenciales en `.env` (nunca en código)

### Login/iniciar_sesion.php
- ✅ CSRF protegido con `require_csrf()`
- ✅ Autenticación con `password_verify()` + migración compatible
- ✅ Email validado con `input_email()`
- ✅ Mensajes toast seguros con `json_encode()`
- ✅ Redirección post-login segura

### Login/CrearR.php
- ✅ CSRF protegido
- ✅ `password_hash()` aplicado
- ✅ Validación de email y longitud de password
- ✅ Verificación de correo duplicado con prepared statement

### Pages/ (todos los controladores)
- ✅ `require_login()` en todos
- ✅ `require_csrf()` en todas las acciones POST/DELETE/PUT
- ✅ Todas las consultas SQL con Prepared Statements
- ✅ Outputs con `e()` / `htmlspecialchars()`
- ✅ `current_user_id()` para aislamiento de datos por usuario
- ✅ Manejo de errores con `try/catch(Throwable)` + `app_log()`

---

## 2. CAMBIOS INCOMPLETOS (detectados al iniciar esta sesión)

| # | Archivo | Problema |
|---|---------|---------|
| 1 | `Login/administrador.php` | Usaba `start_secure_session()` sin verificar rol — cualquiera podía registrar usuarios |
| 2 | `Pages/eliminar.php` | Aceptaba GET con `_csrf_token` en URL (exposición en logs/historial) |
| 3 | `Pages/eliminarl.php` | Igual que eliminar.php |
| 4 | `Pages/eliminarp.php` | Igual que eliminar.php |
| 5 | `Pages/Registro_Vacas.php` | Botón eliminar usaba `<a href>` con token en URL |
| 6 | `Pages/produccion_lechera.php` | Botón eliminar usaba `<a href>` con token en URL |
| 7 | `Pages/potrero.php` | Botón eliminar usaba `<a href>` con token en URL |
| 8 | `Pages/editar.php` | Referenciaba `Diseno/EditarA.css` (no existe) |
| 9 | `Pages/CrearV.php` | Usaba `alert()` JS para errores (rompe CSP, experiencia inconsistente) |
| 10 | `Pages/CrearV.php`, `editar.php`, `Registro_Vacas.php` | Campo `estado` aceptaba cualquier string (sin whitelist) |
| 11 | `Pages/AsignarVaca.php`, `MoverVaca.php` | Campo `usuario` venía del cliente (impersonación en auditoría) |
| 12 | `Pages/potrero.php` | Formularios de asignar/mover tenían campo `usuario` visible en HTML |
| 13 | `Pages/Registro_Vacas.php` | Faltaba div `#toast` requerido por el sistema de mensajes |

---

## 3. CAMBIOS APLICADOS EN ESTA SESIÓN

### CRÍTICO — Control de acceso
- **`Login/administrador.php`**: Cambiado `start_secure_session()` por `require_role(['administrador'])`. Ahora solo usuarios con rol `administrador` pueden acceder al panel de registro.

### ALTO — CSRF token en URL
- **`Pages/eliminar.php`**: Convertido a solo-POST, token CSRF leído de `$_POST` únicamente.
- **`Pages/eliminarl.php`**: Idem.
- **`Pages/eliminarp.php`**: Idem.
- **`Pages/Registro_Vacas.php`**: Botón eliminar reemplazado por `<form method="POST">` con CSRF hidden.
- **`Pages/produccion_lechera.php`**: Idem.
- **`Pages/potrero.php`**: Idem.

### MEDIO — Validación de entrada
- **`Pages/CrearV.php`**: Whitelist para campo `estado` (`produccion`, `secado`, `enrazada`). Eliminados `alert()` JS, reemplazados por redirecciones con query params.
- **`Pages/editar.php`**: Whitelist para campo `estado`.
- **`Pages/Registro_Vacas.php`**: Whitelist para campo `estado`.

### MEDIO — Impersonación en auditoría
- **`Pages/AsignarVaca.php`**: `$usuario` ahora proviene de `$_SESSION['nombre']` (no del cliente).
- **`Pages/MoverVaca.php`**: Idem.
- **`Pages/potrero.php`**: Campos de "Responsable" eliminados de ambos formularios (asignar y mover).

### BAJO — Calidad y UX
- **`Pages/editar.php`**: CSS corregido a `../Css/registro_vacas.css` (anterior ruta rota).
- **`Pages/Registro_Vacas.php`**: Añadido div `#toast` requerido.
- **`JS/registro_vacas.js`**: Añadido sistema de toast, `alert()` reemplazado, manejo de query params para mensajes de resultado.

---

## 4. RIESGOS DETECTADOS (pendientes para futuras fases)

| Riesgo | Severidad | Archivo(s) |
|--------|-----------|-----------|
| Sin rate limiting en login (fuerza bruta) | 🟠 MEDIO | `Login/iniciar_sesion.php` |
| Sin tabla de roles en BD (rol almacenado solo en session/columna) | 🟠 MEDIO | `Login/iniciar_sesion.php`, `Config/helpers.php` |
| `editar.php` es página huérfana (funcionalidad duplicada en modal) | 🟡 BAJO | `Pages/editar.php` |
| No hay política de rotación de tokens CSRF por request (token de sesión reutilizable — aceptable para UX pero no ideal) | 🟡 BAJO | `Config/helpers.php` |
| No hay cabeceras HTTP de seguridad (CSP, X-Frame-Options, etc.) | 🟠 MEDIO | Global |
| `vacunaciones.html` enlaza a `vacunaciones.php` pero existe separado | 🟡 BAJO | `Pages/vacunaciones.html` |
| Sin paginación en tablas (potencial DoS con muchos registros) | 🟡 BAJO | Vistas de listado |
| Sin 2FA para cuentas de administrador | 🟡 BAJO | `Login/administrador.php` |
