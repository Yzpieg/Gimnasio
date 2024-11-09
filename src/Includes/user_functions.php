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
    } catch (Exception $e) {
        // En caso de error, deshacer la transacción
        $conn->rollback();
        $_SESSION['error'] = "Error al crear el usuario: " . $e->getMessage();
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
    } catch (Exception $e) {
        $conn->rollback();
        return ["success" => false, "message" => "Error al crear el usuario: " . $e->getMessage()];
    }
}


function manejarAccionUsuario($conn)
{
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id_usuario'])) {
        $id_usuario = $_POST['id_usuario'];

        if (isset($_POST['eliminar_usuario'])) {
            eliminarUsuario($conn, $id_usuario);
            redirigirConMensaje("Usuario eliminado correctamente");
        } elseif (isset($_POST['crear_miembro'])) {
            crearMiembro($conn, $id_usuario);
            redirigirConMensaje("Miembro creado correctamente");
        } elseif (isset($_POST['crear_monitor'])) {
            crearMonitor($conn, $id_usuario);
            redirigirConMensaje("Monitor creado correctamente");
        } elseif (isset($_POST['restaurar_usuario'])) {
            restaurarUsuario($conn, $id_usuario);
            redirigirConMensaje("Usuario restaurado correctamente");
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

function crearMiembro($conn, $id_usuario)
{
    // Verificar si ya es miembro
    $stmt = $conn->prepare("SELECT * FROM miembro WHERE id_usuario = ?");
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $stmt->close();
        redirigirConMensaje("El usuario ya es miembro");
    }
    $stmt->close();

    // Eliminar de monitor si existe
    $stmt = $conn->prepare("DELETE FROM monitor WHERE id_usuario = ?");
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $stmt->close();

    // Insertar en la tabla miembro
    $stmt = $conn->prepare("INSERT INTO miembro (id_usuario, fecha_registro, tipo_membresia, entrenamiento) VALUES (?, NOW(), 'Básica', 'General')");
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $stmt->close();

    // Actualizar rol en usuario
    $stmt = $conn->prepare("UPDATE usuario SET rol = 'miembro' WHERE id_usuario = ?");
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $stmt->close();
}


function crearMonitor($conn, $id_usuario)
{
    // Verificar si ya es monitor
    $stmt = $conn->prepare("SELECT * FROM monitor WHERE id_usuario = ?");
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $stmt->close();
        redirigirConMensaje("El usuario ya es monitor");
    }
    $stmt->close();

    // Eliminar de miembro si existe
    $stmt = $conn->prepare("DELETE FROM miembro WHERE id_usuario = ?");
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $stmt->close();

    // Insertar en la tabla monitor
    $stmt = $conn->prepare("INSERT INTO monitor (id_usuario, especialidad, disponibilidad) VALUES (?, 'General', 'Disponible')");
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
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
    // Validar el teléfono (debe tener exactamente 9 dígitos si no está vacío)
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

    // Ejecutar actualización y devolver el resultado
    $resultado = $stmt->execute();
    $stmt->close();

    // Redireccionar con mensaje de éxito o error según el resultado
    if ($resultado) {
        redirigirConMensaje("Datos actualizados correctamente", $paginaRedireccion);
    } else {
        redirigirConMensaje("Error al actualizar los datos", $paginaRedireccion);
    }
}
