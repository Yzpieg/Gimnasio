


CREATE TABLE IF NOT EXISTS miembro (
    id_miembro INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT, -- Agregamos id_usuario para la clave foránea
    fecha_registro DATE,
    tipo_membresia VARCHAR(50),
    entrenamiento VARCHAR(50),
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario)
);


create table if not exists reserva(
id_reserva int auto_increment primary key,
fecha_registro date,
tipo_membresia varchar (50),
entrenamiento varchar (50),
foreign key (id_usuario) references usuario(id_usuario),
foreign key (id_clase) references clase(id_clase)
);

create table if not exists usuario(
id_usuario int auto_increment primary key,
nombre varchar (100),
email varchar (50),
contrasenya varchar (100),
telefono int (15)
);

create table if not exists monitor(
id_monitor int auto_increment primary key,
especialidad varchar (20),
disponibilidad varchar (50),
foreign key (id_usuario) references usuario(id_usuario)
);

create table if not exists clase(
id_clase int auto_increment primary key,
nombre_clase varchar (50),
capacidad int (50),
fecha date,
duracion int (50),
precio_clase decimal (10,2),
nivel_dificultad int (10),
foreign key (id_monitor) references monitor(id_monitor)
);

create table if not exists pago(
id_pago int auto_increment primary key,
fecha_pago date,
metodo varchar (50),
descripcion varchar (200),
cantidad_pago decimal (10,2),
foreign key (id_miembro) references miembro(id_miembro),
foreign key (id_clase) references clase(id_clase)
);
