-- Crear la base de datos y seleccionarla
CREATE DATABASE IF NOT EXISTS actividad_02;
USE actividad_02;

-- Tabla de usuarios generales
CREATE TABLE IF NOT EXISTS usuario (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100),
    email VARCHAR(50) UNIQUE,
    contrasenya VARCHAR(100),
    rol ENUM('usuario', 'miembro', 'monitor', 'admin') DEFAULT 'usuario',
    telefono VARCHAR(15),
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
-- Insertar un usuario administrador predeterminado
INSERT INTO usuario (nombre, email, contrasenya, rol, telefono)
VALUES ('admin', 'admin@gmail.com', '$2y$10$.EC.dUvGSPkqTiQ8FdXMHOTiZRISmWFKz8D8sp781iDXSHEx7JiSS', 'admin', NULL);

-- Tabla para tipos de membresía
CREATE TABLE IF NOT EXISTS membresia (
    id_membresia INT AUTO_INCREMENT PRIMARY KEY,
    tipo VARCHAR(50) UNIQUE,
    precio DECIMAL(10, 2),
    duracion INT COMMENT 'Duración en meses',
    beneficios TEXT
);

-- Tabla específica para miembros
CREATE TABLE IF NOT EXISTS miembro (
    id_miembro INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT,
    fecha_registro DATE,
    id_membresia INT NULL,
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE CASCADE,
    FOREIGN KEY (id_membresia) REFERENCES membresia(id_membresia) ON DELETE SET NULL
);

-- Relación entre miembros y tipos de membresía (historial de membresías)
CREATE TABLE IF NOT EXISTS miembro_membresia (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_miembro INT,
    id_membresia INT,
    monto_pagado DECIMAL(10, 2),
    fecha_inicio DATE,
    fecha_fin DATE,
    estado ENUM('activa', 'expirada') DEFAULT 'activa',
    renovacion_automatica BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (id_miembro) REFERENCES miembro(id_miembro) ON DELETE CASCADE,
    FOREIGN KEY (id_membresia) REFERENCES membresia(id_membresia) ON DELETE CASCADE
);

-- Tabla específica para monitores
CREATE TABLE IF NOT EXISTS monitor (
    id_monitor INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT,
    especialidad VARCHAR(50),
    experiencia INT COMMENT 'Años de experiencia',
    disponibilidad ENUM('disponible', 'no disponible') DEFAULT 'disponible',
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE CASCADE
);

-- Tabla para especialidades de los monitores y entrenamientos de los miembros
CREATE TABLE IF NOT EXISTS especialidad (
    id_especialidad INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) UNIQUE
);

-- Relación entre monitores y sus especialidades
CREATE TABLE IF NOT EXISTS monitor_especialidad (
    id_monitor INT,
    id_especialidad INT,
    PRIMARY KEY (id_monitor, id_especialidad),
    FOREIGN KEY (id_monitor) REFERENCES monitor(id_monitor) ON DELETE CASCADE,
    FOREIGN KEY (id_especialidad) REFERENCES especialidad(id_especialidad) ON DELETE CASCADE
);

-- Relación entre miembros y entrenamientos
CREATE TABLE IF NOT EXISTS miembro_entrenamiento (
    id_miembro INT,
    id_especialidad INT,
    PRIMARY KEY (id_miembro, id_especialidad),
    FOREIGN KEY (id_miembro) REFERENCES miembro(id_miembro) ON DELETE CASCADE,
    FOREIGN KEY (id_especialidad) REFERENCES especialidad(id_especialidad) ON DELETE CASCADE
);

-- Tabla de clases o actividades del gimnasio
CREATE TABLE IF NOT EXISTS clase (
    id_clase INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100),
    id_monitor INT,
    id_especialidad INT,
    fecha DATE,
    horario TIME,
    duracion INT COMMENT 'Duración en minutos',
    capacidad_maxima INT DEFAULT 20,
    FOREIGN KEY (id_monitor) REFERENCES monitor(id_monitor) ON DELETE SET NULL,
    FOREIGN KEY (id_especialidad) REFERENCES especialidad(id_especialidad) ON DELETE CASCADE
);

-- Tabla para registrar la asistencia de los miembros a las clases
CREATE TABLE IF NOT EXISTS asistencia (
    id_asistencia INT AUTO_INCREMENT PRIMARY KEY,
    id_clase INT,
    id_miembro INT,
    id_especialidad INT,
    fecha DATE,
    asistencia ENUM('presente', 'ausente') DEFAULT 'presente',
    FOREIGN KEY (id_clase) REFERENCES clase(id_clase) ON DELETE CASCADE,
    FOREIGN KEY (id_miembro) REFERENCES miembro(id_miembro) ON DELETE CASCADE,
    FOREIGN KEY (id_especialidad) REFERENCES especialidad(id_especialidad) ON DELETE CASCADE
);

-- Tabla de pagos
CREATE TABLE IF NOT EXISTS pago (
    id_pago INT AUTO_INCREMENT PRIMARY KEY,
    id_miembro INT,
    monto DECIMAL(10, 2),
    fecha_pago DATE,
    metodo_pago ENUM('efectivo', 'tarjeta', 'transferencia','bizum','paypal') DEFAULT 'tarjeta',
    FOREIGN KEY (id_miembro) REFERENCES miembro(id_miembro) ON DELETE CASCADE
);

-- Tabla para notificaciones enviadas a los usuarios
CREATE TABLE IF NOT EXISTS notificacion (
    id_notificacion INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT,
    mensaje TEXT,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    leida BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE CASCADE
);

-- Tabla de roles y permisos
CREATE TABLE IF NOT EXISTS rol_permiso (
    id_permiso INT AUTO_INCREMENT PRIMARY KEY,
    rol ENUM('usuario', 'miembro', 'monitor', 'admin'),
    permiso VARCHAR(50),
    descripcion TEXT,
    UNIQUE (rol, permiso)
);
CREATE TABLE membresia_entrenamiento (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_membresia INT,
    id_entrenamiento INT,
    FOREIGN KEY (id_membresia) REFERENCES membresia(id_membresia) ON DELETE CASCADE,
    FOREIGN KEY (id_entrenamiento) REFERENCES especialidad(id_especialidad) ON DELETE CASCADE
);


-- Insertar especialidades de ejemplo
INSERT INTO especialidad (nombre) VALUES ('Yoga'), ('Pilates'), ('Cardio'), ('Pesas'), ('Entrenamiento Funcional');
-- Insertar algunos tipos de membresías para referencia
INSERT INTO membresia (tipo, precio, duracion, beneficios)
VALUES 
    ('mensual', 30.00, 1, 'Acceso a todas las clases generales'),
    ('anual', 300.00, 12, 'Acceso ilimitado y descuento en clases especiales'),
    ('limitada', 15.00, 1, 'Acceso limitado a clases específicas');
CREATE INDEX idx_miembro_entrenamiento ON miembro_entrenamiento (id_miembro, id_especialidad);
CREATE INDEX idx_clase_especialidad ON clase (id_especialidad);
CREATE INDEX idx_asistencia_clase_miembro ON asistencia (id_clase, id_miembro);




