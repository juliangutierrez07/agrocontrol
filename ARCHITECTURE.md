# ARCHITECTURE.md
> Arquitectura del sistema — AgroControl

---

## Descripción general

AgroControl es una aplicación web PHP/MySQL de arquitectura **monolítica MVC ligera**, sin framework externo, que corre sobre XAMPP (Apache + MySQL + PHP).

---

## Estructura de directorios

```
AgroControl/
├── .env                      # Variables de entorno (NO commitear)
├── .env.example              # Plantilla de configuración
├── .gitignore
│
├── Assets/
│   └── Imagenes/
│       ├── logo.png
│       ├── imagvaca.png
│       └── vacas/            # Fotos subidas por usuarios (generadas)
│
├── Config/
│   ├── conexion.php          # Singleton de conexión MySQL
│   └── helpers.php           # Librería central: seguridad, BD, validación, utilidades
│
├── Css/                      # Hojas de estilo por módulo
│   ├── Dashboard.css
│   ├── registro_vacas.css
│   ├── produccion_lechera.css
│   ├── historial_leche.css
│   ├── historial_quincenas_leche.css
│   ├── potrero.css
│   ├── vacunaciones.css
│   ├── Home.css
│   ├── iniciarsesion.css
│   └── administrador.css
│
├── JS/
│   ├── registro_vacas.js     # Lógica modal + toast + tema
│   └── potrero.js            # Lógica potreros/mangas
│
├── Login/
│   ├── iniciar_sesion.php    # Formulario y lógica de login
│   ├── administrador.php     # Panel de registro de usuarios (solo admin)
│   └── CrearR.php            # Controlador de creación de usuarios
│
├── logs/
│   └── app.log               # Log de errores de aplicación
│
├── Pages/
│   ├── Home.php              # Landing page pública
│   ├── Dashboard.php         # Panel principal (protegido)
│   │
│   ├── Registro_Vacas.php    # Vista + edición inline de vacas
│   ├── CrearV.php            # Controlador: crear vaca
│   ├── editar.php            # Página de edición alternativa (legacy)
│   ├── eliminar.php          # Controlador: eliminar vaca (POST-only)
│   │
│   ├── produccion_lechera.php # Vista de producción lechera
│   ├── crearL.php            # Controlador: crear registro de leche
│   ├── ActualizarL.php       # Controlador: actualizar registro de leche
│   ├── eliminarl.php         # Controlador: eliminar registro de leche (POST-only)
│   ├── historial_leche.php   # Historial por vaca
│   ├── historial_quincenas_leche.php # Historial quincenas generales
│   │
│   ├── potrero.php           # Vista de potreros y mangas
│   ├── CrearPotrero.php      # Controlador: crear potrero
│   ├── Actualizarpotrero.php # Controlador: actualizar potrero
│   ├── eliminarp.php         # Controlador: eliminar potrero (POST-only)
│   ├── AsignarVaca.php       # Controlador: asignar vaca a potrero
│   ├── MoverVaca.php         # Controlador: mover vaca entre potreros
│   │
│   ├── vacunaciones.html     # Interfaz SPA de vacunaciones (HTML+JS)
│   ├── vacunaciones.php      # API REST de vacunaciones (JSON)
│   ├── getHojaVida.php       # API: hoja de vida de vaca (JSON)
│   │
│   └── logout.php            # Cierre de sesión
│
├── AUDIT_REPORT.md
├── CONTINUATION_AUDIT.md
├── SECURITY.md
├── ARCHITECTURE.md
├── INSTALLATION.md
├── CHANGELOG.md
└── README.md
```

---

## Capas de la arquitectura

### Capa de configuración (`Config/`)
- `conexion.php`: singleton MySQL con reconexión lazy
- `helpers.php`: librería única con funciones de seguridad, BD, validación y utilidades

### Capa de autenticación (`Login/`)
- Formulario de login con CSRF
- Creación de usuarios protegida por rol
- Logout con destrucción completa de sesión

### Capa de vistas (`Pages/*.php` que retornan HTML)
- Cada página verifica sesión al inicio con `require_login()`
- Datos cargados con prepared statements
- Salidas escapadas con `e()` / `htmlspecialchars()`
- CSRF token embebido en todos los formularios POST

### Capa de controladores (`Pages/*.php` que procesan POST y redirigen)
- Verifican sesión + CSRF
- Validan y sanitizan entradas
- Ejecutan lógica de negocio
- Redirigen con `header('Location: ...')`

### Capa de API (`Pages/*.php` que retornan JSON)
- `vacunaciones.php`: API REST para módulo de vacunaciones
- `getHojaVida.php`: endpoint para hoja de vida de vaca
- Responden con `json_response()` del helper

---

## Base de datos (esquema inferido)

```
usuarios       (id, nombre, correo, password, rol)
vacas          (id, codigo, nombre, raza, edad, estado, foto, descripcion, vacunas_info, partos, usuario_id)
registroleche  (id, vaca_id, fecha, litros, usuario_id)
potreros       (id, nombre, hectareas, tipo_pasto, tiene_mangas, num_mangas, tamaño_manga, capacidad_max, usuario_id)
asignaciones   (id, vaca_id, potrero_id, manga_num, usuario, fecha_entrada, fecha_salida)
rotaciones     (id, vaca_id, potrero_origen_id, potrero_destino_id, fecha_traslado, observacion)
tipos_vacuna   (id, nombre, obligatoria_ica)
vacunaciones   (id, vaca_id, tipo_vacuna_id, fecha_programada, fecha_aplicada, estado, dosis_ml, responsable, observaciones)
```

---

## Flujo de seguridad

```
Request HTTP
    │
    ├─► include("../Config/conexion.php")   → carga helpers + conexión
    ├─► require_login()                      → verifica sesión o redirige
    ├─► require_csrf()  [POST/PUT/DELETE]    → valida token o 403
    ├─► input_*()                            → valida y sanitiza entradas
    ├─► db_execute() / db_result()           → prepared statements
    ├─► e() / htmlspecialchars()             → escape en salidas HTML
    └─► json_response() / header(Location)  → respuesta segura
```

---

## Decisiones de arquitectura

| Decisión | Justificación |
|----------|---------------|
| Sin ORM | Proyecto pequeño, prepared statements directos son suficientes y más transparentes |
| Helper único | Reduce dependencias, fácil de auditar, un solo punto de mantenimiento de seguridad |
| Sin framework PHP | Reduce superficie de ataque, sin dependencias de terceros vulnerables |
| Vistas con PHP inline | Compatible con XAMPP educativo, sin build step |
| API JSON para módulos complejos (vacunaciones) | Permite interfaces más ricas sin recarga de página |
