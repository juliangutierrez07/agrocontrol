# CHANGELOG.md

Todos los cambios relevantes del proyecto AgroControl están documentados en este archivo.  
Formato basado en [Keep a Changelog](https://keepachangelog.com/es/).

---

## [Unreleased]

### Pendiente
- Rate limiting en formulario de login (protección fuerza bruta)
- Paginación server-side en tablas de listado
- Tabla de roles y permisos granulares en BD
- 2FA para cuentas de administrador
- HSTS (`Strict-Transport-Security`) — pendiente hasta activar HTTPS en producción (Dokploy)
- Refactor de JS/onclick inline a archivos externos + `addEventListener`, para poder retirar `'unsafe-inline'` de `script-src` en el CSP

---

## [1.2.0] — 2026-08-06

### Seguridad
- **MEDIO** Cabeceras HTTP de seguridad (`X-Content-Type-Options`, `X-Frame-Options`, `X-XSS-Protection`, `Referrer-Policy`, `Content-Security-Policy`) ya estaban implementadas en `Config/helpers.php::send_security_headers()` para toda página autenticada, pero `Pages/vacunaciones.html` (estático) y `Pages/Home.php` (landing pública sin `require` de `Config/`) quedaban sin cubrir. Se agregó `.htaccess` con la misma política vía `mod_headers` para cerrar ese hueco.
- Corregido bug en el CSP de `Config/helpers.php`: `img-src` no incluía `https://images.unsplash.com`, bloqueando la imagen de fondo del login.
- `Dockerfile`: habilitado `mod_headers` (`a2enmod headers`) para que el `.htaccess` también aplique en producción (Dokploy).

---

## [1.1.0] — 2026-06-18

### Seguridad
- **CRÍTICO** `Login/administrador.php`: protegido con `require_role(['administrador'])`. Antes cualquier usuario autenticado (o sin autenticar) podía acceder.
- **ALTO** `Pages/eliminar.php`, `eliminarl.php`, `eliminarp.php`: convertidos a solo-POST. El token CSRF ya no se expone en URLs, historial del navegador ni logs de servidor.
- **ALTO** `Pages/Registro_Vacas.php`, `produccion_lechera.php`, `potrero.php`: botones de eliminar reemplazados por formularios POST con token CSRF oculto.
- **MEDIO** `Pages/CrearV.php`, `editar.php`, `Registro_Vacas.php`: validación whitelist en campo `estado` de vaca (`produccion`, `secado`, `enrazada`).
- **MEDIO** `Pages/AsignarVaca.php`, `MoverVaca.php`: campo `usuario` ahora proviene de `$_SESSION['nombre']` (no del cliente). Elimina riesgo de impersonación en registros de auditoría.

### Correcciones
- `Pages/editar.php`: CSS referenciado corregido (de ruta inexistente `Diseno/EditarA.css` a `../Css/registro_vacas.css`).
- `Pages/CrearV.php`: mensajes de error cambiados de `alert()` JS a redirecciones con query params semánticos.
- `Pages/Registro_Vacas.php`: añadido `<div id="toast">` requerido por el sistema de notificaciones.
- `Pages/potrero.php`: campos de "Responsable" eliminados de formularios de asignar y mover (ya proviene de sesión).

### Mejoras
- `JS/registro_vacas.js`: sistema de toast centralizado, manejo de mensajes desde query params (`ok=creado`, `error=codigo_existente`, etc.), `alert()` reemplazado.

### Documentación
- Creado `CONTINUATION_AUDIT.md` — estado detallado antes/después de esta fase.
- Creado `AUDIT_REPORT.md` — reporte consolidado de todas las vulnerabilidades.
- Creado `SECURITY.md` — políticas y mecanismos de seguridad implementados.
- Creado `ARCHITECTURE.md` — documentación de arquitectura del sistema.
- Creado `INSTALLATION.md` — guía de instalación con SQL de creación de tablas.
- Creado `CHANGELOG.md` — este archivo.

---

## [1.0.0] — 2026 (Fase 1 — Auditoría anterior)

### Seguridad implementada en fase 1
- Migración completa a Prepared Statements (eliminación de SQL injection)
- Implementación de `password_hash()` / `password_verify()` con migración de legacy
- Sistema CSRF: `csrf_token()`, `csrf_field()`, `csrf_validate()`, `require_csrf()`
- Sesiones seguras: `HttpOnly`, `SameSite=Lax`, `Secure` condicional
- `session_regenerate_id(true)` en login exitoso
- `require_login()` en todas las páginas privadas
- `require_role()` para control de acceso
- Función `e()` para escape XSS en todas las salidas
- Validaciones tipadas centralizadas en `helpers.php`
- Subida segura de imágenes con MIME real (finfo), límite 2MB, nombres aleatorios
- Logs centralizados en `logs/app.log`
- Credenciales en `.env` (eliminadas del código)
- `try/catch(Throwable)` en todos los controladores
- `json_response()` seguro para APIs

### Módulos construidos
- Dashboard con estadísticas
- Gestión de vacas (CRUD + historial de leche)
- Producción lechera con alertas de caída
- Historial quincenas de pago
- Potreros y mangas con capacidad y rotación
- Vacunaciones (API REST JSON)
- Hoja de vida de vaca
- Sistema de login con tokens de sesión
