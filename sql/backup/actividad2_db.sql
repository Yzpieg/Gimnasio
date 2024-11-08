-- Crear la base de datos y seleccionarla
CREATE DATABASE IF NOT EXISTS actividad_02;
USE actividad_02;

-- Crear tabla usuario
CREATE TABLE IF NOT EXISTS usuario (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100),
    email VARCHAR(50) UNIQUE,
    contrasenya VARCHAR(100),
    rol ENUM('usuario', 'miembro', 'monitor', 'admin') DEFAULT 'usuario',
    telefono VARCHAR(15)
);

-- Insertar un usuario administrador
INSERT INTO usuario (id_usuario, nombre, email, contrasenya, rol, telefono)
VALUES (1, 'admin', 'admin@gmail.com', '$2y$10$.EC.dUvGSPkqTiQ8FdXMHOTiZRISmWFKz8D8sp781iDXSHEx7JiSS', 'admin', NULL);

-- Crear tabla miembro
CREATE TABLE IF NOT EXISTS miembro (
    id_miembro INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT,
    fecha_registro DATE,
    tipo_membresia VARCHAR(50),
    entrenamiento VARCHAR(50),
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE CASCADE
);

-- Crear tabla monitor
CREATE TABLE IF NOT EXISTS monitor (
    id_monitor INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT,
    especialidad VARCHAR(20),
    disponibilidad VARCHAR(50),
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE CASCADE
);

-- Crear tabla clase
CREATE TABLE IF NOT EXISTS clase (
    id_clase INT AUTO_INCREMENT PRIMARY KEY,
    nombre_clase VARCHAR(50),
    capacidad INT,
    fecha DATE,
    duracion INT,
    precio_clase DECIMAL(10,2),
    nivel_dificultad INT,
    id_monitor INT,
    FOREIGN KEY (id_monitor) REFERENCES monitor(id_monitor) ON DELETE CASCADE
);

-- Crear tabla reserva
CREATE TABLE IF NOT EXISTS reserva (
    id_reserva INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT,
    id_clase INT,
    fecha_reserva DATE,
    estado VARCHAR(50),
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE CASCADE,
    FOREIGN KEY (id_clase) REFERENCES clase(id_clase) ON DELETE CASCADE
);

-- Crear tabla pago
CREATE TABLE IF NOT EXISTS pago (
    id_pago INT AUTO_INCREMENT PRIMARY KEY,
    id_miembro INT,
    id_clase INT,
    fecha_pago DATE,
    metodo VARCHAR(50),
    descripcion VARCHAR(200),
    cantidad_pago DECIMAL(10,2),
    FOREIGN KEY (id_miembro) REFERENCES miembro(id_miembro) ON DELETE CASCADE,
    FOREIGN KEY (id_clase) REFERENCES clase(id_clase) ON DELETE CASCADE
);


