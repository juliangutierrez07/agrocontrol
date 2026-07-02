# AUDIT_REPORT.md
> Reporte final de auditoría de seguridad — AgroControl  
> Fecha: 2026-06-18

---

## Resumen ejecutivo

AgroControl es un sistema de gestión ganadera en PHP/MySQL con XAMPP. La auditoría abarcó dos fases realizadas por distintas IAs. Al inicio de la segunda fase, el sistema ya contaba con una base sólida de seguridad implementada. Esta segunda fase completó las correcciones pendientes.

**Estado final**: Sistema apto para producción con las recomendaciones pendientes en proceso.

---

## Vulnerabilidades corregidas (acumulado de ambas fases)

### CRÍTICAS — Resueltas
| ID | Vulnerabilidad | Archivo(s) afectados | Estado |
|----|---------------|---------------------|--------|
| V01 | SQL Injection en todas las consultas | Todos los archivos PHP | ✅ Corregido (Prepared Statements) |
| V02 | Contraseñas en texto plano | `Login/`, `Login/CrearR.php` | ✅ Corregido (password_hash) |
| V03 | Panel de admin accesible sin autenticación | `Login/administrador.php` | ✅ Corregido (require_role) |
| V04 | Sin protección CSRF en formularios POST | Todos los formularios | ✅ Corregido (csrf_field/validate) |
| V05 | Token CSRF expuesto en URLs | `eliminar.php`, `eliminarl.php`, `eliminarp.php` | ✅ Corregido (solo POST) |

### ALTAS — Resueltas
| ID | Vulnerabilidad | Archivo(s) afectados | Estado |
|----|---------------|---------------------|--------|
| V06 | XSS en salidas dinámicas | Todas las vistas | ✅ Corregido (e()/htmlspecialchars) |
| V07 | Sin validación de sesión en páginas privadas | `Pages/` | ✅ Corregido (require_login) |
| V08 | Credenciales hardcodeadas en código | `Config/conexion.php` | ✅ Corregido (.env) |
| V09 | Subida de archivos sin validación MIME | `Pages/CrearV.php`, `Registro_Vacas.php` | ✅ Corregido (finfo) |
| V10 | Path traversal en borrado de imágenes | `Config/helpers.php` | ✅ Corregido (realpath check) |
| V11 | Impersonación de usuario en registros de auditoría | `Pages/AsignarVaca.php`, `MoverVaca.php` | ✅ Corregido (desde sesión) |

### MEDIAS — Resueltas
| ID | Vulnerabilidad | Archivo(s) afectados | Estado |
|----|---------------|---------------------|--------|
| V12 | Sin validación whitelist en campo `estado` | `CrearV.php`, `editar.php`, `Registro_Vacas.php` | ✅ Corregido |
| V13 | Errores SQL expuestos al usuario | Todos | ✅ Corregido (try/catch + app_log) |
| V14 | Cookies de sesión sin HttpOnly/SameSite | `Config/helpers.php` | ✅ Corregido |
| V15 | Sin regeneración de session ID en login | `Login/iniciar_sesion.php` | ✅ Corregido |
| V16 | `alert()` JS con mensajes de error internos | `Pages/CrearV.php`, `JS/registro_vacas.js` | ✅ Corregido |
| V17 | CSS referenciado inexistente | `Pages/editar.php` | ✅ Corregido |

---

## Vulnerabilidades pendientes (próximas fases)

| ID | Vulnerabilidad | Severidad | Recomendación |
|----|---------------|-----------|---------------|
| P01 | Sin rate limiting en login | 🟠 MEDIO | Implementar conteo de intentos fallidos + lockout temporal |
| P02 | Sin cabeceras HTTP de seguridad (CSP, X-Frame-Options, HSTS, etc.) | 🟠 MEDIO | Añadir en `Config/helpers.php` o `.htaccess` |
| P03 | Roles almacenados solo en columna de usuario (sin tabla de permisos) | 🟠 MEDIO | Crear tabla `roles` y `permisos` para control granular |
| P04 | Sin 2FA para administradores | 🟡 BAJO | Implementar TOTP (Google Authenticator compatible) |
| P05 | Sin paginación en tablas (potencial sobrecarga) | 🟡 BAJO | Implementar paginación server-side |
| P06 | Token CSRF reutilizable por sesión (no por request) | 🟡 BAJO | Considerar tokens por form-action para mayor seguridad |
| P07 | `editar.php` duplica funcionalidad del modal (superficie de ataque extra) | 🟡 BAJO | Deprecar y eliminar |

---

## Archivos modificados en esta auditoría

```
Login/administrador.php          — require_role(['administrador'])
Pages/eliminar.php               — solo POST, token de $_POST
Pages/eliminarl.php              — solo POST, token de $_POST
Pages/eliminarp.php              — solo POST, token de $_POST
Pages/Registro_Vacas.php         — eliminar via POST form, whitelist estado, div#toast
Pages/produccion_lechera.php     — eliminar via POST form
Pages/potrero.php                — eliminar via POST form, campos usuario eliminados
Pages/editar.php                 — whitelist estado, CSS corregido
Pages/CrearV.php                 — whitelist estado, sin alert()
Pages/AsignarVaca.php            — usuario desde sesión
Pages/MoverVaca.php              — usuario desde sesión
JS/registro_vacas.js             — sistema toast, sin alert(), manejo query params
```
