# FIX_REDECLARE_CONNECTION.md
> Corrección definitiva del error fatal `Cannot redeclare Conexion()`  
> Fecha: 2026-06-18

---

## Error reportado

```
Fatal error: Cannot redeclare Conexion() (previously declared in
C:\xampp\htdocs\AgroControl\Config\conexion.php:6)
in C:\xampp\htdocs\AgroControl\Config\conexion.php on line 33
```

URL afectada: `http://localhost:8012/AgroControl/Login/iniciar_sesion.php`

---

## Causa raíz

PHP **no distingue mayúsculas de minúsculas en nombres de funciones**.  
`conexion()` y `Conexion()` son **el mismo identificador** para el engine de PHP.

`Config/conexion.php` declaraba **ambas funciones en el mismo archivo**:

```php
// Línea 4 — primera declaración
function conexion(): mysqli { ... }

// Línea 31 — REDECLARACIÓN del mismo nombre → FATAL ERROR
function Conexion(): mysqli
{
    return conexion();
}
```

PHP registra `conexion` al cargar la línea 4, y cuando llega a la línea 31 intenta registrar `conexion` de nuevo (porque `Conexion` == `conexion` para PHP) → `Cannot redeclare`.

Esto existía desde el inicio del proyecto. El error se volvió visible ahora porque `require_once` garantiza que el archivo se carga exactamente una vez, y PHP llega siempre hasta la línea 31. Con `include()` el comportamiento era impredecible según el orden de carga.

---

## Archivos modificados

### `Config/conexion.php` — eliminada la función alias `Conexion()`

**Diff:**
```diff
  function conexion(): mysqli { ... }
-
- function Conexion(): mysqli
- {
-     return conexion();
- }
```

### `Login/iniciar_sesion.php` — actualizada la única llamada con mayúscula

**Diff:**
```diff
- $con = Conexion();
+ $con = conexion();
```

---

## Verificación post-corrección

| Comprobación | Resultado |
|---|---|
| `function Conexion()` en el proyecto | **0** |
| `function conexion()` en el proyecto | **1** (solo en `Config/conexion.php`) |
| Llamadas a `Conexion()` con mayúscula | **0** |
| Llamadas a `conexion()` en minúsculas | 22 archivos — todas correctas |
| Error fatal reproducible | **No** — corregido |

---

## Resumen de correcciones acumuladas relacionadas con este error

| Fase | Corrección |
|------|-----------|
| Anterior | `include()` → `require_once()` en 23 archivos (evita doble carga) |
| Esta fase | Eliminado alias `Conexion()` de `conexion.php` (elimina la redeclaración) |
| Esta fase | `Conexion()` → `conexion()` en `iniciar_sesion.php` (única llamada con mayúscula) |
