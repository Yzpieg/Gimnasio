USE actividad_02;

-- Insertar usuarios con nombres simples y roles variados (sin id_usuario 1)
INSERT INTO usuario (nombre, email, contrasenya, rol, telefono) VALUES
('Laura', 'laura@gmail.com', '$2y$10$ZFYwIC8PwlZKoZZC0gvs8uKU9RWwPuqYqPj8ZhOj1Zw/j35oCxR7W', 'miembro', '123456789'),
('Carlos', 'carlos@gmail.com', '$2y$10$ZFYwIC8PwlZKoZZC0gvs8uKU9RWwPuqYqPj8ZhOj1Zw/j35oCxR7W', 'miembro', '987654321'),
('Ana', 'ana@gmail.com', '$2y$10$ZFYwIC8PwlZKoZZC0gvs8uKU9RWwPuqYqPj8ZhOj1Zw/j35oCxR7W', 'monitor', '123123123'),
('Pedro', 'pedro@gmail.com', '$2y$10$ZFYwIC8PwlZKoZZC0gvs8uKU9RWwPuqYqPj8ZhOj1Zw/j35oCxR7W', 'monitor', '321321321'),
('Maria', 'maria@gmail.com', '$2y$10$ZFYwIC8PwlZKoZZC0gvs8uKU9RWwPuqYqPj8ZhOj1Zw/j35oCxR7W', 'admin', '456456456');

-- Insertar miembros asociados a usuarios con rol 'miembro'
INSERT INTO miembro (id_usuario, fecha_registro, id_membresia) VALUES
(2, '2023-01-01', 1),
(3, '2023-02-01', 2);

-- Insertar monitores asociados a usuarios con rol 'monitor'
INSERT INTO monitor (id_usuario, especialidad, experiencia, disponibilidad) VALUES
(4, 'Yoga', 5, 'disponible'),
(5, 'Pilates', 3, 'disponible');

