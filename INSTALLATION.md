# INSTALLATION.md
> Guía de instalación — AgroControl

---

## Requisitos

| Componente | Versión mínima |
|-----------|---------------|
| PHP | 8.1+ |
| MySQL / MariaDB | 8.0+ / 10.6+ |
| Apache | 2.4+ |
| XAMPP (opcional) | 8.2+ |

---

## Instalación en XAMPP (desarrollo local)

### 1. Clonar o copiar el proyecto

```bash
# Copiar la carpeta a htdocs
# Ejemplo: C:\xampp\htdocs\AgroControl\
```

### 2. Configurar variables de entorno

```bash
# Copiar el ejemplo
copy .env.example .env
```

Editar `.env` con tus credenciales:

```
APP_ENV=local
APP_DEBUG=false
DB_HOST=localhost
DB_USER=root
DB_PASS=
DB_NAME=agrocontrol
DB_CHARSET=utf8mb4
```

> ⚠️ **Nunca** commitear el archivo `.env`. Ya está en `.gitignore`.

### 3. Crear la base de datos

Abre phpMyAdmin o el cliente MySQL y ejecuta:

```sql
CREATE DATABASE IF NOT EXISTS agrocontrol CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE agrocontrol;

-- Usuarios del sistema
CREATE TABLE usuarios (
    id       INT AUTO_INCREMENT PRIMARY KEY,
    nombre   VARCHAR(100) NOT NULL,
    correo   VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    rol      VARCHAR(30)  NOT NULL DEFAULT 'usuario',
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Vacas
CREATE TABLE vacas (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    codigo       VARCHAR(50)  NOT NULL,
    nombre       VARCHAR(100) NOT NULL,
    raza         VARCHAR(100),
    edad         INT DEFAULT 0,
    estado       ENUM('produccion','secado','enrazada') NOT NULL DEFAULT 'produccion',
    foto         VARCHAR(255),
    descripcion  TEXT,
    vacunas_info TEXT,
    partos       INT DEFAULT 0,
    usuario_id   INT NOT NULL,
    creado_en    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Producción lechera
CREATE TABLE registroleche (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    vaca_id    INT NOT NULL,
    fecha      DATE NOT NULL,
    litros     DECIMAL(10,2) NOT NULL,
    usuario_id INT NOT NULL,
    FOREIGN KEY (vaca_id) REFERENCES vacas(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Potreros
CREATE TABLE potreros (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    nombre        VARCHAR(100) NOT NULL,
    hectareas     DECIMAL(10,2),
    tipo_pasto    VARCHAR(60),
    tiene_mangas  TINYINT(1) DEFAULT 0,
    num_mangas    INT DEFAULT 0,
    tamaño_manga  DECIMAL(10,2) DEFAULT 0,
    capacidad_max INT DEFAULT 0,
    usuario_id    INT NOT NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Asignaciones de vacas a potreros
CREATE TABLE asignaciones (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    vaca_id      INT NOT NULL,
    potrero_id   INT NOT NULL,
    manga_num    INT,
    usuario      VARCHAR(100),
    fecha_entrada DATE NOT NULL,
    fecha_salida  DATE,
    FOREIGN KEY (vaca_id) REFERENCES vacas(id) ON DELETE CASCADE,
    FOREIGN KEY (potrero_id) REFERENCES potreros(id) ON DELETE CASCADE
);

-- Rotaciones (historial de movimientos)
CREATE TABLE rotaciones (
    id                 INT AUTO_INCREMENT PRIMARY KEY,
    vaca_id            INT NOT NULL,
    potrero_origen_id  INT NOT NULL,
    potrero_destino_id INT NOT NULL,
    fecha_traslado     DATE NOT NULL,
    observacion        TEXT,
    FOREIGN KEY (vaca_id) REFERENCES vacas(id) ON DELETE CASCADE
);

-- Tipos de vacuna
CREATE TABLE tipos_vacuna (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    nombre          VARCHAR(100) NOT NULL,
    obligatoria_ica TINYINT(1) DEFAULT 0
);

-- Vacunaciones
CREATE TABLE vacunaciones (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    vaca_id          INT NOT NULL,
    tipo_vacuna_id   INT NOT NULL,
    fecha_programada DATE NOT NULL,
    fecha_aplicada   DATE,
    estado           ENUM('pendiente','aplicada','vencida') DEFAULT 'pendiente',
    dosis_ml         DECIMAL(6,2),
    responsable      VARCHAR(100),
    observaciones    TEXT,
    FOREIGN KEY (vaca_id) REFERENCES vacas(id) ON DELETE CASCADE,
    FOREIGN KEY (tipo_vacuna_id) REFERENCES tipos_vacuna(id)
);

-- Datos iniciales de vacunas ICA
INSERT INTO tipos_vacuna (nombre, obligatoria_ica) VALUES
    ('Aftosa', 1),
    ('Brucelosis', 1),
    ('Carbón sintomático', 0),
    ('Rabia bovina', 1),
    ('Leptospirosis', 0);

-- Crear usuario administrador inicial (password: Admin1234)
INSERT INTO usuarios (nombre, correo, password, rol) VALUES (
    'Administrador',
    'admin@agrocontrol.local',
    '$2y$10$placeholder_change_this_hash',
    'administrador'
);
```

> ⚠️ **Cambiar el hash del administrador** ejecutando el script de creación real en `Login/administrador.php` después de iniciar sesión, o usando un script PHP temporal con `echo password_hash('TuPasswordSeguro', PASSWORD_DEFAULT);`.

### 4. Crear usuario administrador

Accede a `http://localhost/AgroControl/Login/iniciar_sesion.php` e inicia sesión con las credenciales del admin. Luego ve a `Login/administrador.php` para crear usuarios adicionales.

### 5. Permisos de carpeta

Asegúrate de que `Assets/Imagenes/vacas/` y `logs/` tienen permisos de escritura:

```bash
# Linux/Mac
chmod 775 Assets/Imagenes/vacas logs

# Windows: click derecho → Propiedades → Seguridad → dar control total al usuario del servidor web
```

---

## Notas de producción

1. Establecer `APP_DEBUG=false` en `.env`
2. Configurar HTTPS y actualizar `APP_URL` si aplica
3. Configurar usuario MySQL con permisos mínimos (no usar `root`)
4. Programar backup automático de la base de datos
5. Revisar las recomendaciones de cabeceras HTTP en `SECURITY.md`
6. Rotar el directorio de logs periódicamente
