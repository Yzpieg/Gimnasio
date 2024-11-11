<?php

require_once('general.php');

//Función para crear un usuario desde el formulario.
function crearFormUsuario($conn, $nombre, $email, $contrasenya, $confirmar_contrasenya, $rol)
{
    // Verificar que las contraseñas coincidan
    if ($contrasenya !== $confirmar_contrasenya) {
        $_SESSION['error'] = "Las contraseñas no coinciden";
        header("Location: crear_usuario.php");
        exit();
    }

    // Encriptar la contraseña
    $hashedPassword = password_hash($contrasenya, PASSWORD_DEFAULT);

    // Iniciar la transacción para asegurar consistencia entre tablas
    $conn->begin_transaction();

    try {
        // Insertar el nuevo usuario en la tabla usuario
        $stmt = $conn->prepare("INSERT INTO usuario (nombre, email, contrasenya, rol) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $nombre, $email, $hashedPassword, $rol);
        $stmt->execute();

        // Obtener el id del usuario insertado
        $id_usuario = $conn->insert_id;
        $stmt->close();

        // Insertar en tablas adicionales según el rol
        if ($rol == 'miembro') {
            crearMiembro($conn, $id_usuario);
        } elseif ($rol == 'monitor') {
            crearMonitor($conn, $id_usuario);
        }

        // Confirmar la transacción
        $conn->commit();
        $_SESSION['mensaje'] = "Usuario creado exitosamente";
        header("Location: crear_usuario.php");
        exit();
    } catch (mysqli_sql_exception $e) {
        // En caso de error, deshacer la transacción
        $conn->rollback();

        // Verificar si el error es por email duplicado (código de error 1062)
        if ($e->getCode() == 1062) {
            $_SESSION['error'] = "Error: El email '$email' ya está registrado. Por favor, utiliza otro email.";
        } else {
            $_SESSION['error'] = "Error al crear el usuario: Ocurrió un problema inesperado.";
        }

        header("Location: crear_usuario.php");
        exit();
    }
}


// Función para crear un nuevo usuario
function crearUsuario($conn, $nombre, $email, $contrasenya, $rol)
{
    $hashedPassword = password_hash($contrasenya, PASSWORD_DEFAULT);

    $conn->begin_transaction();

    try {
        $stmt = $conn->prepare("INSERT INTO usuario (nombre, email, contrasenya, rol) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $nombre, $email, $hashedPassword, $rol);
        $stmt->execute();
        $id_usuario = $conn->insert_id;
        $stmt->close();

        if ($rol === 'miembro') {
            crearMiembro($conn, $id_usuario);
        } elseif ($rol === 'monitor') {
            crearMonitor($conn, $id_usuario);
        }

        $conn->commit();
        return ["success" => true, "message" => "Usuario creado exitosamente"];
    } catch (mysqli_sql_exception $e) {
        $conn->rollback();

        if ($e->getCode() == 1062) {
            return ["success" => false, "message" => "Error: El email '$email' ya está registrado. Por favor, utiliza otro email."];
        }

        return ["success" => false, "message" => "Error al crear el usuario: Ocurrió un problema inesperado."];
    }
}


function manejarAccionUsuario($conn, $pagina = "usuarios.php")
{
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id_usuario'])) {
        $id_usuario = $_POST['id_usuario'];

        if (isset($_POST['eliminar_usuario'])) {
            eliminarUsuario($conn, $id_usuario);
            redirigirConMensaje("Usuario eliminado correctamente", $pagina);
        } elseif (isset($_POST['crear_miembro'])) {
            crearMiembro($conn, $id_usuario);
            redirigirConMensaje("Miembro creado correctamente", $pagina);
        } elseif (isset($_POST['crear_monitor'])) {
            crearMonitor($conn, $id_usuario);
            redirigirConMensaje("Monitor creado correctamente", $pagina);
        } elseif (isset($_POST['restaurar_usuario'])) {
            restaurarUsuario($conn, $id_usuario);
            redirigirConMensaje("Usuario restaurado correctamente", $pagina);
        }
    }
}

// Subfunciones para cada acción específica
function eliminarUsuario($conn, $id_usuario)
{
    $stmt = $conn->prepare("DELETE FROM usuario WHERE id_usuario = ?");
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $stmt->close();
}

function crearMiembro($conn, $id_usuario, $pagina = "usuarios.php")
{
    // Verificar si ya es miembro
    $stmt = $conn->prepare("SELECT * FROM miembro WHERE id_usuario = ?");
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $stmt->close();
        redirigirConMensaje("El usuario ya es miembro", $pagina);
        return;
    }
    $stmt->close();

    // Eliminar de monitor si existe
    $stmt = $conn->prepare("DELETE FROM monitor WHERE id_usuario = ?");
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $stmt->close();

    // Insertar en la tabla miembro (referencia a id_membresia)
    $id_membresia = 1; // Ajuste inicial, aquí se podría permitir seleccionar la membresía al crear.
    $stmt = $conn->prepare("INSERT INTO miembro (id_usuario, fecha_registro, id_membresia) VALUES (?, NOW(), ?)");
    $stmt->bind_param("ii", $id_usuario, $id_membresia);
    $stmt->execute();
    $stmt->close();

    // Actualizar rol en usuario
    $stmt = $conn->prepare("UPDATE usuario SET rol = 'miembro' WHERE id_usuario = ?");
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $stmt->close();
}




function crearMonitor($conn, $id_usuario, $pagina = "usuarios.php")
{
    // Verificar si ya es monitor
    $stmt = $conn->prepare("SELECT * FROM monitor WHERE id_usuario = ?");
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $stmt->close();
        redirigirConMensaje("El usuario ya es monitor", $pagina);
        return;
    }
    $stmt->close();

    // Eliminar de miembro si existe
    $stmt = $conn->prepare("DELETE FROM miembro WHERE id_usuario = ?");
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $stmt->close();

    // Insertar en la tabla monitor con especialidad predeterminada
    $stmt = $conn->prepare("INSERT INTO monitor (id_usuario, especialidad, disponibilidad) VALUES (?, 'General', 'disponible')");
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $id_monitor = $conn->insert_id; // Obtener id del nuevo monitor
    $stmt->close();

    // Actualizar rol en usuario
    $stmt = $conn->prepare("UPDATE usuario SET rol = 'monitor' WHERE id_usuario = ?");
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $stmt->close();
}




function restaurarUsuario($conn, $id_usuario)
{
    $stmt = $conn->prepare("DELETE FROM miembro WHERE id_usuario = ?");
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();

    $stmt = $conn->prepare("DELETE FROM monitor WHERE id_usuario = ?");
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $stmt->close();

    $stmt = $conn->prepare("UPDATE usuario SET rol = 'usuario' WHERE id_usuario = ?");
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $stmt->close();
}
function obtenerDatosUsuario($conn, $id_usuario)
{
    $nombre = $email = $telefono = $rol = ''; // Valores predeterminados como cadenas vacías

    $stmt = $conn->prepare("SELECT nombre, email, telefono, rol FROM usuario WHERE id_usuario = ?");
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $stmt->bind_result($nombre, $email, $telefono, $rol);
    $stmt->fetch();
    $stmt->close();

    // Retorna los datos como un array asociativo
    return [
        'nombre' => $nombre ?: '',      // Si es null, usa cadena vacía
        'email' => $email ?: '',        // Si es null, usa cadena vacía
        'telefono' => $telefono ?: '',  // Si es null, usa cadena vacía
        'rol' => $rol ?: ''             // Si es null, usa cadena vacía
    ];
}



function actualizarDatosUsuario($conn, $id_usuario, $nuevo_nombre, $nuevo_telefono, $nueva_contrasenya = null, $paginaRedireccion = "usuario.php")
{
    if (!empty($nuevo_telefono) && !preg_match('/^\d{9}$/', $nuevo_telefono)) {
        redirigirConMensaje("El teléfono debe tener exactamente 9 dígitos", $paginaRedireccion . "&error");
        exit();
    }

    // Preparar actualización según si hay nueva contraseña
    if (!empty($nueva_contrasenya)) {
        $password_hash = password_hash($nueva_contrasenya, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE usuario SET nombre = ?, telefono = ?, contrasenya = ? WHERE id_usuario = ?");
        $stmt->bind_param("sssi", $nuevo_nombre, $nuevo_telefono, $password_hash, $id_usuario);
    } else {
        $stmt = $conn->prepare("UPDATE usuario SET nombre = ?, telefono = ? WHERE id_usuario = ?");
        $stmt->bind_param("ssi", $nuevo_nombre, $nuevo_telefono, $id_usuario);
    }

    $resultado = $stmt->execute();
    $stmt->close();

    if ($resultado) {
        redirigirConMensaje("Datos actualizados correctamente", $paginaRedireccion);
    } else {
        redirigirConMensaje("Error al actualizar los datos", $paginaRedireccion);
    }
}

function modUsuario($conn, $id_usuario, $nuevo_nombre, $nuevo_email, $nuevo_telefono, $nuevo_rol, $nueva_contrasenya = null, $paginaRedireccion = "edit_usuario.php")
{
    // Validar el teléfono (debe tener exactamente 9 dígitos si no está vacío)
    if (!empty($nuevo_telefono) && !preg_match('/^\d{9}$/', $nuevo_telefono)) {
        redirigirConMensaje("El teléfono debe tener exactamente 9 dígitos", $paginaRedireccion . "&error");
        exit();
    }

    // Preparar la consulta SQL para actualizar el usuario
    if (!empty($nueva_contrasenya)) {
        $password_hash = password_hash($nueva_contrasenya, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE usuario SET nombre = ?, email = ?, telefono = ?, contrasenya = ?, rol = ? WHERE id_usuario = ?");
        $stmt->bind_param("sssssi", $nuevo_nombre, $nuevo_email, $nuevo_telefono, $password_hash, $nuevo_rol, $id_usuario);
    } else {
        $stmt = $conn->prepare("UPDATE usuario SET nombre = ?, email = ?, telefono = ?, rol = ? WHERE id_usuario = ?");
        $stmt->bind_param("ssssi", $nuevo_nombre, $nuevo_email, $nuevo_telefono, $nuevo_rol, $id_usuario);
    }

    // Ejecutar la actualización del usuario
    $resultado = $stmt->execute();
    $stmt->close();

    if ($resultado) {
        // Si el nuevo rol es "miembro", eliminarlo de "monitor" y asignar una membresía
        if ($nuevo_rol === 'miembro') {
            $stmt = $conn->prepare("DELETE FROM monitor WHERE id_usuario = ?");
            $stmt->bind_param("i", $id_usuario);
            $stmt->execute();
            $stmt->close();

            // Agregar a la tabla miembro si no existe
            $id_membresia = 1; // ID de membresía por defecto
            $stmt = $conn->prepare("INSERT IGNORE INTO miembro (id_usuario, fecha_registro, id_membresia) VALUES (?, NOW(), ?)");
            $stmt->bind_param("ii", $id_usuario, $id_membresia);
            $stmt->execute();
            $stmt->close();
        }
        // Si el nuevo rol es "monitor", eliminarlo de "miembro"
        elseif ($nuevo_rol === 'monitor') {
            $stmt = $conn->prepare("DELETE FROM miembro WHERE id_usuario = ?");
            $stmt->bind_param("i", $id_usuario);
            $stmt->execute();
            $stmt->close();

            // Agregar a la tabla monitor si no existe
            $stmt = $conn->prepare("INSERT IGNORE INTO monitor (id_usuario, especialidad, disponibilidad) VALUES (?, 'General', 'disponible')");
            $stmt->bind_param("i", $id_usuario);
            $stmt->execute();
            $stmt->close();
        }
        // Si el nuevo rol es "usuario" o "admin", eliminarlo de "monitor" y "miembro"
        elseif ($nuevo_rol === 'usuario' || $nuevo_rol === 'admin') {
            $stmt = $conn->prepare("DELETE FROM monitor WHERE id_usuario = ?");
            $stmt->bind_param("i", $id_usuario);
            $stmt->execute();
            $stmt->close();

            $stmt = $conn->prepare("DELETE FROM miembro WHERE id_usuario = ?");
            $stmt->bind_param("i", $id_usuario);
            $stmt->execute();
            $stmt->close();
        }

        // Redirigir con mensaje de éxito
        redirigirConMensaje("Datos actualizados correctamente", $paginaRedireccion);
    } else {
        // Redirigir con mensaje de error si la actualización falló
        redirigirConMensaje("Error al actualizar los datos", $paginaRedireccion);
    }
}

function obtenerUsuarios($conn, $id_admin, $busqueda = '', $orden_columna = 'nombre', $orden_direccion = 'ASC')
{
    // Validar las entradas de columna y dirección para evitar inyecciones SQL
    $columnas_validas = ['nombre', 'email', 'rol'];
    $direccion_valida = ['ASC', 'DESC'];

    if (!in_array($orden_columna, $columnas_validas)) {
        $orden_columna = 'nombre';
    }
    if (!in_array($orden_direccion, $direccion_valida)) {
        $orden_direccion = 'ASC';
    }

    // Construir la consulta SQL con el filtro de búsqueda y ordenamiento
    $sql = "SELECT id_usuario, nombre, email, rol FROM usuario WHERE id_usuario != ?";

    // Agregar filtro de búsqueda si se proporciona un término
    if ($busqueda) {
        $sql .= " AND (nombre LIKE ? OR email LIKE ?)";
    }

    // Agregar ordenamiento
    $sql .= " ORDER BY $orden_columna $orden_direccion";

    // Preparar y ejecutar la consulta
    $stmt = $conn->prepare($sql);
    if ($busqueda) {
        $busqueda_param = '%' . $busqueda . '%';
        $stmt->bind_param("iss", $id_admin, $busqueda_param, $busqueda_param);
    } else {
        $stmt->bind_param("i", $id_admin);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    // Devolver los resultados como un array asociativo
    $usuarios = [];
    while ($row = $result->fetch_assoc()) {
        $usuarios[] = $row;
    }

    $stmt->close();
    return $usuarios;
}
