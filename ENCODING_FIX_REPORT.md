# ENCODING_FIX_REPORT.md
> Corrección de problema de codificación de caracteres — AgroControl  
> Fecha: 2026-06-18

---

## Síntomas reportados

```
Producci?n
Vacas Pre?adas
Pr?ximas Vacunas
Cerrar sesi?n
en producci?n
```

Caracteres con tilde y la ñ aparecían como `?` en el navegador.

---

## Auditoría de encoding — resultado completo

| Tipo de verificación | Resultado |
|---|---|
| Archivos con BOM (Byte Order Mark) | **0** — ninguno tiene BOM |
| Archivos con bytes UTF-8 inválidos | **1** — solo `Dashboard.php` |
| `<meta charset="UTF-8">` presente | ✅ en todos los archivos HTML/PHP con vistas |
| `mysqli_set_charset($con, 'utf8mb4')` | ✅ en `Config/conexion.php` |
| Encoding de helpers.php | ✅ UTF-8 válido |
| Encoding de iniciar_sesion.php | ✅ UTF-8 válido |
| Encoding de produccion_lechera.php | ✅ UTF-8 válido |
| Encoding de vacunaciones.php | ✅ UTF-8 válido |
| Encoding de historial_leche.php | ✅ UTF-8 válido |
| Encoding de administrador.php | ✅ UTF-8 válido |

---

## Causa raíz

**Un solo archivo afectado: `Pages/Dashboard.php`**

El archivo fue reescrito durante una sesión anterior de modificaciones usando **PowerShell `Set-Content` sin especificar encoding explícito**. En sistemas Windows, `Set-Content` usa el encoding del sistema operativo por defecto — en este caso **Windows-1252 (CP1252/Latin-1)** — en lugar de UTF-8.

Los caracteres con tilde y ñ tienen representaciones de bytes diferentes en cada encoding:

| Carácter | UTF-8 (correcto) | Windows-1252 (incorrecto) |
|---|---|---|
| `ó` | `0xC3 0xB3` (2 bytes) | `0xF3` (1 byte) |
| `ñ` | `0xC3 0xB1` (2 bytes) | `0xF1` (1 byte) |
| `é` | `0xC3 0xA9` (2 bytes) | `0xE9` (1 byte) |
| `á` | `0xC3 0xA1` (2 bytes) | `0xE1` (1 byte) |
| `í` | `0xC3 0xAD` (2 bytes) | `0xED` (1 byte) |

Cuando Apache/PHP sirve el archivo con `Content-Type: text/html; charset=utf-8`, el navegador intenta interpretar los bytes como UTF-8. Los bytes `0xF3`, `0xF1`, etc. son secuencias UTF-8 incompletas — el navegador los muestra como `?` (carácter de reemplazo U+FFFD).

---

## Caracteres corruptos encontrados y corregidos

| Línea | Antes (Windows-1252) | Después (UTF-8) |
|---|---|---|
| 15 | `// PRODUCCI?N RECIENTE` | `// PRODUCCIÓN RECIENTE` |
| 28 | `// GR?FICO` | `// GRÁFICO` |
| 101 | `Gesti?n Ganadera` | `Gestión Ganadera` |
| 113 | `Gesti?n de Vacas` | `Gestión de Vacas` |
| 117 | `Producci?n Lechera` | `Producción Lechera` |
| 135 | `Cerrar sesi?n` | `Cerrar sesión` |
| 177 | `en producci?n` | `en producción` |
| 183 | `Producci?n Hoy` | `Producción Hoy` |
| 197 | `Vacas Pre?adas` | `Vacas Preñadas` |
| 226 | `PRODUCCI?N RECIENTE` | `PRODUCCIÓN RECIENTE` |
| 229 | `Producci?n Reciente` | `Producción Reciente` |
| 260 | `GR?FICO` | `GRÁFICO` |
| 263 | `Producci?n por Vaca` | `Producción por Vaca` |
| 273 | `Pr?ximas Vacunas` | `Próximas Vacunas` |
| 288 | `'Ma?ana'` | `'Mañana'` |
| 291 | `' d?as'` | `' días'` |
| 314 | `pr?ximas.` | `próximas.` |
| 325 | `/* GR?FICO */` | `/* GRÁFICO */` |

**Total: 18 caracteres corruptos corregidos.**

---

## Solución aplicada

```powershell
# 1. Leer los bytes crudos del archivo dañado
$bytes = [System.IO.File]::ReadAllBytes($path)

# 2. Decodificar correctamente como Windows-1252 (encoding original real)
$win1252  = [System.Text.Encoding]::GetEncoding(1252)
$content  = $win1252.GetString($bytes)

# 3. Reescribir como UTF-8 sin BOM
$utf8NoBom = New-Object System.Text.UTF8Encoding($false)
[System.IO.File]::WriteAllText($path, $content, $utf8NoBom)
```

---

## Archivos modificados

| Archivo | Acción |
|---|---|
| `Pages/Dashboard.php` | Reescrito de Windows-1252 a UTF-8 sin BOM |

---

## Verificación final

```
Pasada completa sobre 39 archivos (PHP + HTML + JS + CSS):
OK — Todos los archivos están en UTF-8 válido
Caracteres de reemplazo UTF-8 restantes en Dashboard.php: 0
```

---

## Prevención futura

Para evitar que PowerShell vuelva a corromper archivos al escribirlos, siempre usar encoding explícito:

```powershell
# Escritura segura en UTF-8 sin BOM
$utf8NoBom = New-Object System.Text.UTF8Encoding($false)
[System.IO.File]::WriteAllText($ruta, $contenido, $utf8NoBom)

# O con Set-Content especificando encoding
Set-Content -Path $ruta -Value $contenido -Encoding UTF8NoBOM
```

El uso de `Set-Content` sin `-Encoding` en PowerShell 5.x usa Windows-1252 por defecto en sistemas con locale en español. En PowerShell 7+ el default es UTF-8, pero siempre es mejor ser explícito.
