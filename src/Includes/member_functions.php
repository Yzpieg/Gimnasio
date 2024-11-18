<?php

require_once('general.php');


// Obtiene todos los miembros de la base de datos
function obtenerMiembros($conn, $busqueda = '', $orden_columna = 'nombre', $orden_direccion = 'ASC')
{
    // Validar columnas y dirección para evitar inyecciones SQL
    $columnas_validas = ['nombre', 'email', 'fecha_registro', 'tipo', 'precio', 'duracion'];
    $direccion_valida = ['ASC', 'DESC'];

    if (!in_array($orden_columna, $columnas_validas)) {
        $orden_columna = 'nombre';
    }
    if (!in_array($orden_direccion, $direccion_valida)) {
        $orden_direccion = 'ASC';
    }

    // Construir la consulta SQL para obtener los miembros con sus membresías y entrenamientos
    $sql = "SELECT u.id_usuario, u.nombre, u.email, u.rol, m.fecha_registro, mb.tipo, mb.precio, mb.duracion,
                   GROUP_CONCAT(e.nombre SEPARATOR ', ') AS entrenamientos
            FROM usuario u
            INNER JOIN miembro m ON u.id_usuario = m.id_usuario
            LEFT JOIN membresia mb ON m.id_membresia = mb.id_membresia
            LEFT JOIN miembro_entrenamiento me ON m.id_miembro = me.id_miembro
            LEFT JOIN especialidad e ON me.id_especialidad = e.id_especialidad";

    // Agregar filtro de búsqueda si se proporciona un término
    if ($busqueda) {
        $sql .= " WHERE u.nombre LIKE ? OR u.email LIKE ?";
    }

    // Agregar agrupación y ordenamiento
    $sql .= " GROUP BY u.id_usuario ORDER BY $orden_columna $orden_direccion";

    // Preparar y ejecutar la consulta
    $stmt = $conn->prepare($sql);
    if ($busqueda) {
        $busqueda_param = '%' . $busqueda . '%';
        $stmt->bind_param("ss", $busqueda_param, $busqueda_param);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    // Devolver los resultados como un array asociativo
    $miembros = [];
    while ($row = $result->fetch_assoc()) {
        $miembros[] = $row;
    }

    $stmt->close();
    return $miembros;
}



function eliminarMiembro($conn, $id_usuario)
{
    // Iniciar una transacción para eliminar de múltiples tablas si es necesario
    $conn->begin_transaction();

    try {
        // Eliminar de la tabla miembro
        $stmt = $conn->prepare("DELETE FROM miembro WHERE id_usuario = ?");
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $stmt->close();

        // También eliminar el registro de usuario si se desea eliminar completamente
        $stmt = $conn->prepare("DELETE FROM usuario WHERE id_usuario = ?");
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $stmt->close();

        // Confirmar la transacción
        $conn->commit();
        return ["success" => true, "message" => "Miembro eliminado correctamente."];
    } catch (Exception $e) {
        // En caso de error, revertir la transacción
        $conn->rollback();
        return ["success" => false, "message" => "Error al eliminar el miembro: " . $e->getMessage()];
    }
}
function obtenerMiembroPorID($conn, $id_usuario)
{
    // Consultar datos básicos del miembro, incluyendo la membresía
    $sql = "SELECT u.id_usuario, u.nombre, u.email, u.rol, m.fecha_registro, m.id_membresia, m.id_miembro, mb.tipo AS tipo_membresia
            FROM usuario u
            INNER JOIN miembro m ON u.id_usuario = m.id_usuario
            LEFT JOIN membresia mb ON m.id_membresia = mb.id_membresia
            WHERE u.id_usuario = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    $miembro = $result->fetch_assoc();
    $stmt->close();

    if (!$miembro) {
        return null; // Miembro no encontrado
    }

    // Obtener los entrenamientos asociados
    $sql = "SELECT e.id_especialidad
            FROM miembro_entrenamiento me
            INNER JOIN especialidad e ON me.id_especialidad = e.id_especialidad
            WHERE me.id_miembro = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $miembro['id_miembro']);
    $stmt->execute();
    $result = $stmt->get_result();

    // Guardar los IDs de entrenamientos en un array
    $entrenamientos = [];
    while ($row = $result->fetch_assoc()) {
        $entrenamientos[] = $row['id_especialidad'];
    }
    $stmt->close();

    $miembro['entrenamientos'] = $entrenamientos;

    return $miembro;
}



function actualizarMiembro($conn, $id_usuario, $nombre, $email, $fecha_registro, $id_membresia)
{
    $conn->begin_transaction();

    try {
        // Actualizar los datos del usuario
        $sqlUsuario = "UPDATE usuario SET nombre = ?, email = ? WHERE id_usuario = ?";
        $stmtUsuario = $conn->prepare($sqlUsuario);
        $stmtUsuario->bind_param("ssi", $nombre, $email, $id_usuario);
        $stmtUsuario->execute();

        // Obtener el id_miembro relacionado con el id_usuario
        $sqlMiembro = "SELECT id_miembro FROM miembro WHERE id_usuario = ?";
        $stmtMiembro = $conn->prepare($sqlMiembro);
        $stmtMiembro->bind_param("i", $id_usuario);
        $stmtMiembro->execute();
        $resultadoMiembro = $stmtMiembro->get_result();

        if ($resultadoMiembro->num_rows === 0) {
            throw new Exception("Miembro no encontrado para este usuario.");
        }

        $id_miembro = $resultadoMiembro->fetch_assoc()['id_miembro'];

        // Actualizar la membresía en la tabla miembro
        $sqlActualizarMiembro = "UPDATE miembro SET fecha_registro = ?, id_membresia = ? WHERE id_miembro = ?";
        $stmtActualizarMiembro = $conn->prepare($sqlActualizarMiembro);
        $stmtActualizarMiembro->bind_param("sii", $fecha_registro, $id_membresia, $id_miembro);
        $stmtActualizarMiembro->execute();

        // Registrar el cambio en la tabla miembro_membresia
        $fecha_actual = date('Y-m-d');
        $fecha_fin = date('Y-m-d', strtotime("+1 month")); // Cambiar según la duración de la membresía
        $sqlInsertarMembresia = "
            INSERT INTO miembro_membresia (id_miembro, id_membresia, monto_pagado, fecha_inicio, fecha_fin, estado, renovacion_automatica) 
            VALUES (?, ?, ?, ?, ?, 'activa', FALSE)
        ";
        $stmtInsertarMembresia = $conn->prepare($sqlInsertarMembresia);

        // Obtener el precio de la membresía para registrar el monto pagado
        $sqlPrecioMembresia = "SELECT precio, duracion FROM membresia WHERE id_membresia = ?";
        $stmtPrecioMembresia = $conn->prepare($sqlPrecioMembresia);
        $stmtPrecioMembresia->bind_param("i", $id_membresia);
        $stmtPrecioMembresia->execute();
        $resultadoPrecio = $stmtPrecioMembresia->get_result();

        if ($resultadoPrecio->num_rows > 0) {
            $membresia = $resultadoPrecio->fetch_assoc();
            $monto_pagado = $membresia['precio'];
            $duracion_meses = $membresia['duracion'];
            $fecha_fin = date('Y-m-d', strtotime("+$duracion_meses months", strtotime($fecha_actual)));

            // Registrar la nueva membresía
            $stmtInsertarMembresia->bind_param("iisss", $id_miembro, $id_membresia, $monto_pagado, $fecha_actual, $fecha_fin);
            $stmtInsertarMembresia->execute();
        } else {
            throw new Exception("Membresía no encontrada.");
        }

        // Eliminar los entrenamientos anteriores del miembro
        $sqlBorrarEntrenamientos = "DELETE FROM miembro_entrenamiento WHERE id_miembro = ?";
        $stmtBorrarEntrenamientos = $conn->prepare($sqlBorrarEntrenamientos);
        $stmtBorrarEntrenamientos->bind_param("i", $id_miembro);
        $stmtBorrarEntrenamientos->execute();

        // Obtener los nuevos entrenamientos de la membresía seleccionada
        if (!is_null($id_membresia)) {
            $sqlEntrenamientosMembresia = "
                SELECT id_entrenamiento 
                FROM membresia_entrenamiento 
                WHERE id_membresia = ?
            ";
            $stmtEntrenamientosMembresia = $conn->prepare($sqlEntrenamientosMembresia);
            $stmtEntrenamientosMembresia->bind_param("i", $id_membresia);
            $stmtEntrenamientosMembresia->execute();
            $resultadoEntrenamientos = $stmtEntrenamientosMembresia->get_result();

            $sqlInsertarEntrenamiento = "
                INSERT INTO miembro_entrenamiento (id_miembro, id_especialidad) 
                VALUES (?, ?)
            ";
            $stmtInsertarEntrenamiento = $conn->prepare($sqlInsertarEntrenamiento);

            while ($entrenamiento = $resultadoEntrenamientos->fetch_assoc()) {
                $id_especialidad = $entrenamiento['id_entrenamiento']; // Asigna id_entrenamiento a id_especialidad
                $stmtInsertarEntrenamiento->bind_param("ii", $id_miembro, $id_especialidad);
                $stmtInsertarEntrenamiento->execute();
            }
        }

        // Confirmar la transacción
        $conn->commit();

        return ['success' => true];
    } catch (Exception $e) {
        // Revertir la transacción en caso de error
        $conn->rollback();
        return ['success' => false, 'message' => $e->getMessage()];
    }
}





function obtenerEntrenamientos($conn)
{
    $sql = "SELECT id_especialidad, nombre FROM especialidad";
    $result = $conn->query($sql);

    $entrenamientos = [];
    while ($row = $result->fetch_assoc()) {
        $entrenamientos[] = $row;
    }

    return $entrenamientos;
}
function actualizarEntrenamientosMiembro($conn, $id_miembro, $entrenamientos)
{
    // Inicializar $count para evitar advertencias en el editor
    $count = 0;
    // Primero, validar que el id_miembro existe en la tabla miembro
    $stmt = $conn->prepare("SELECT COUNT(*) FROM miembro WHERE id_miembro = ?");
    $stmt->bind_param("i", $id_miembro);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    if ($count == 0) {
        throw new Exception("El miembro con ID $id_miembro no existe.");
    }

    // Eliminar entrenamientos actuales
    $stmt = $conn->prepare("DELETE FROM miembro_entrenamiento WHERE id_miembro = ?");
    $stmt->bind_param("i", $id_miembro);
    $stmt->execute();
    $stmt->close();

    // Insertar los nuevos entrenamientos
    $stmt = $conn->prepare("INSERT INTO miembro_entrenamiento (id_miembro, id_especialidad) VALUES (?, ?)");
    foreach ($entrenamientos as $id_especialidad) {
        $stmt->bind_param("ii", $id_miembro, $id_especialidad);
        $stmt->execute();
    }
    $stmt->close();
}

function obtenerMembresias($conn)
{
    $sql = "SELECT id_membresia, tipo, precio, duracion, beneficios FROM membresia";
    $result = $conn->query($sql);

    $membresias = [];
    while ($row = $result->fetch_assoc()) {
        $membresias[] = $row;
    }

    return $membresias;
}
function obtenerIdMiembroPorUsuario($conn, $id_usuario)
{
    $sql = "SELECT id_miembro FROM miembro WHERE id_usuario = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    $miembro = $result->fetch_assoc();
    $stmt->close();
    return $miembro['id_miembro'] ?? null;
}
function obtenerFechasMembresiaActiva($conn, $id_miembro)
{
    $query = "SELECT fecha_inicio, fecha_fin 
              FROM miembro_membresia 
              WHERE id_miembro = ? AND estado = 'activa' 
              ORDER BY fecha_inicio DESC LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id_miembro);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->fetch_assoc();
}
function obtenerInformacionMiembro($id_usuario)
{
    $conexion = obtenerConexion();

    $sql = "
        SELECT 
            u.nombre AS nombre_usuario,
            u.email,
            u.telefono,
            u.fecha_creacion,
            m.fecha_registro
        FROM usuario u
        LEFT JOIN miembro m ON u.id_usuario = m.id_usuario
        WHERE u.id_usuario = ?
    ";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        return $resultado->fetch_assoc();
    } else {
        return null;
    }
}
function informacionMembresia($id_usuario)
{
    $conexion = obtenerConexion();

    $sql = "
        SELECT 
            u.nombre AS nombre_usuario,
            u.email,
            u.telefono,
            u.fecha_creacion,
            m.fecha_registro,
            mem.tipo AS nombre_membresia,
            mm.fecha_inicio,
            mm.fecha_fin,
            mm.estado,
            mm.renovacion_automatica,
            mm.monto_pagado AS monto_pago,
            p.metodo_pago,
            p.fecha_pago
        FROM usuario u
        LEFT JOIN miembro m ON u.id_usuario = m.id_usuario
        LEFT JOIN miembro_membresia mm ON m.id_miembro = mm.id_miembro AND mm.estado = 'activa'
        LEFT JOIN membresia mem ON mm.id_membresia = mem.id_membresia
        LEFT JOIN pago p ON m.id_miembro = p.id_miembro
        WHERE u.id_usuario = ?
        ORDER BY p.fecha_pago DESC
        LIMIT 1
    ";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $resultado = $stmt->get_result();

    $datosMiembro = $resultado->fetch_assoc();

    // Obtener los entrenamientos/especialidades asignados al miembro
    $sqlEspecialidades = "
        SELECT e.nombre AS especialidad
        FROM miembro_entrenamiento me
        INNER JOIN especialidad e ON me.id_especialidad = e.id_especialidad
        WHERE me.id_miembro = (
            SELECT id_miembro FROM miembro WHERE id_usuario = ?
        )
    ";

    $stmtEspecialidades = $conexion->prepare($sqlEspecialidades);
    $stmtEspecialidades->bind_param("i", $id_usuario);
    $stmtEspecialidades->execute();
    $resultadoEspecialidades = $stmtEspecialidades->get_result();

    $especialidades = [];
    while ($fila = $resultadoEspecialidades->fetch_assoc()) {
        $especialidades[] = $fila['especialidad'];
    }

    $datosMiembro['especialidades'] = $especialidades;

    return $datosMiembro;
}
