-- Crear la base de datos y seleccionarla
CREATE DATABASE IF NOT EXISTS actividad_02;
USE actividad_02;

-- Tabla de usuarios generales (incluye miembros, monitores y administradores)
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
INSERT INTO usuario (id_usuario, nombre, email, contrasenya, rol)
VALUES (1, 'admin', 'admin@gmail.com', '$2y$10$.EC.dUvGSPkqTiQ8FdXMHOTiZRISmWFKz8D8sp781iDXSHEx7JiSS', 'admin');

-- Tabla específica para miembros (clientes del gimnasio)
CREATE TABLE IF NOT EXISTS miembro (
    id_miembro INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT,
    fecha_registro DATE,
    tipo_membresia ENUM('mensual', 'anual', 'limitada') DEFAULT 'mensual',
    entrenamiento VARCHAR(50),
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE CASCADE
);

-- Tabla específica para monitores (entrenadores)
CREATE TABLE IF NOT EXISTS monitor (
    id_monitor INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT,
    especialidad VARCHAR(50),
    experiencia INT COMMENT 'Años de experiencia',
    disponibilidad ENUM('disponible', 'no disponible') DEFAULT 'disponible',
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE CASCADE
);

-- Tabla de clases o actividades del gimnasio
CREATE TABLE IF NOT EXISTS clase (
    id_clase INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100),
    id_monitor INT,
    horario TIME,
    duracion INT COMMENT 'Duración en minutos',
    capacidad_maxima INT DEFAULT 20,
    FOREIGN KEY (id_monitor) REFERENCES monitor(id_monitor) ON DELETE SET NULL
);

-- Tabla para registrar la asistencia de los miembros a las clases
CREATE TABLE IF NOT EXISTS asistencia (
    id_asistencia INT AUTO_INCREMENT PRIMARY KEY,
    id_clase INT,
    id_miembro INT,
    fecha DATE,
    asistencia ENUM('presente', 'ausente') DEFAULT 'presente',
    FOREIGN KEY (id_clase) REFERENCES clase(id_clase) ON DELETE CASCADE,
    FOREIGN KEY (id_miembro) REFERENCES miembro(id_miembro) ON DELETE CASCADE
);

-- Tabla para tipos de membresía
CREATE TABLE IF NOT EXISTS membresia (
    id_membresia INT AUTO_INCREMENT PRIMARY KEY,
    tipo VARCHAR(50) UNIQUE,
    precio DECIMAL(10, 2),
    duracion INT COMMENT 'Duración en meses',
    beneficios TEXT
);

-- Relación entre miembros y tipos de membresía (historial de membresías)
CREATE TABLE IF NOT EXISTS miembro_membresia (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_miembro INT,
    id_membresia INT,
    fecha_inicio DATE,
    fecha_fin DATE,
    estado ENUM('activa', 'expirada') DEFAULT 'activa',
    FOREIGN KEY (id_miembro) REFERENCES miembro(id_miembro) ON DELETE CASCADE,
    FOREIGN KEY (id_membresia) REFERENCES membresia(id_membresia) ON DELETE CASCADE
);

-- Tabla de pagos
CREATE TABLE IF NOT EXISTS pago (
    id_pago INT AUTO_INCREMENT PRIMARY KEY,
    id_miembro INT,
    monto DECIMAL(10, 2),
    fecha_pago DATE,
    metodo_pago ENUM('efectivo', 'tarjeta', 'transferencia') DEFAULT 'tarjeta',
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

-- Tabla de roles y permisos (opcional, para un sistema de permisos detallado)
CREATE TABLE IF NOT EXISTS rol_permiso (
    id_permiso INT AUTO_INCREMENT PRIMARY KEY,
    rol ENUM('usuario', 'miembro', 'monitor', 'admin'),
    permiso VARCHAR(50),
    descripcion TEXT,
    UNIQUE (rol, permiso)
);

-- Insertar algunos tipos de membresías para referencia
INSERT INTO membresia (tipo, precio, duracion, beneficios)
VALUES 
    ('mensual', 30.00, 1, 'Acceso a todas las clases generales'),
    ('anual', 300.00, 12, 'Acceso ilimitado y descuento en clases especiales'),
    ('limitada', 15.00, 1, 'Acceso limitado a clases específicas');
