<?php

// Obtiene todos los monitores de la base de datos
function obtenerMonitores($conn, $busqueda = '', $orden_columna = 'nombre', $orden_direccion = 'ASC')
{
    // Validar columnas y dirección para evitar inyecciones SQL
    $columnas_validas = ['nombre', 'email', 'experiencia', 'disponibilidad'];
    $direccion_valida = ['ASC', 'DESC'];

    if (!in_array($orden_columna, $columnas_validas)) {
        $orden_columna = 'nombre';
    }
    if (!in_array($orden_direccion, $direccion_valida)) {
        $orden_direccion = 'ASC';
    }

    // Construir la consulta SQL para obtener los monitores con sus especialidades
    $sql = "SELECT u.id_usuario, u.nombre, u.email, m.experiencia, m.disponibilidad, 
                   GROUP_CONCAT(e.nombre SEPARATOR ', ') AS especialidades
            FROM usuario u
            INNER JOIN monitor m ON u.id_usuario = m.id_usuario
            LEFT JOIN monitor_especialidad me ON m.id_monitor = me.id_monitor
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
    $monitores = [];
    while ($row = $result->fetch_assoc()) {
        $monitores[] = $row;
    }

    $stmt->close();
    return $monitores;
}



// Función para eliminar un monitor de la base de datos
function eliminarMonitor($conn, $id_usuario)
{
    $conn->begin_transaction();

    try {
        // Eliminar de la tabla monitor
        $stmt = $conn->prepare("DELETE FROM monitor WHERE id_usuario = ?");
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $stmt->close();

        // También eliminar el registro de usuario si se desea eliminar completamente
        $stmt = $conn->prepare("DELETE FROM usuario WHERE id_usuario = ?");
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $stmt->close();

        $conn->commit();
        return ["success" => true, "message" => "Monitor eliminado correctamente."];
    } catch (Exception $e) {
        $conn->rollback();
        return ["success" => false, "message" => "Error al eliminar el monitor: " . $e->getMessage()];
    }
}
function obtenerMonitorPorID($conn, $id_usuario)
{
    // Consulta para obtener los datos básicos del monitor
    $sql = "SELECT m.id_monitor, u.id_usuario, u.nombre, u.email, m.especialidad, m.experiencia, m.disponibilidad
            FROM monitor m
            INNER JOIN usuario u ON m.id_usuario = u.id_usuario
            WHERE u.id_usuario = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    $monitor = $result->fetch_assoc();
    $stmt->close();

    // Verificar si el monitor existe
    if (!$monitor) {
        return null; // Monitor no encontrado
    }

    // Consulta para obtener los entrenamientos (especialidades) asociados al monitor
    $sql = "SELECT e.id_especialidad, e.nombre 
            FROM monitor_especialidad me
            INNER JOIN especialidad e ON me.id_especialidad = e.id_especialidad
            WHERE me.id_monitor = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $monitor['id_monitor']);
    $stmt->execute();
    $result = $stmt->get_result();

    // Almacenar las especialidades en un array
    $entrenamientos = [];
    while ($row = $result->fetch_assoc()) {
        $entrenamientos[] = $row['id_especialidad'];
    }
    $stmt->close();

    // Agregar la lista de entrenamientos al array del monitor
    $monitor['entrenamientos'] = $entrenamientos;

    return $monitor;
}
function actualizarMonitor($conn, $id_usuario, $nombre, $email, $especialidad, $experiencia, $disponibilidad)
{
    try {
        // Actualizar datos del usuario en la tabla usuario y monitor
        $sql = "UPDATE usuario u
                INNER JOIN monitor m ON u.id_usuario = m.id_usuario
                SET u.nombre = ?, u.email = ?, m.especialidad = ?, m.experiencia = ?, m.disponibilidad = ?
                WHERE u.id_usuario = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssisi", $nombre, $email, $especialidad, $experiencia, $disponibilidad, $id_usuario);
        $stmt->execute();
        $stmt->close();

        return ["success" => true, "message" => "Monitor actualizado correctamente"];
    } catch (Exception $e) {
        return ["success" => false, "message" => "Error al actualizar el monitor: " . $e->getMessage()];
    }
}
function actualizarEntrenamientosMonitor($conn, $id_monitor, $entrenamientos)
{
    // Primero, verificar que el monitor existe
    $stmt = $conn->prepare("SELECT COUNT(*) FROM monitor WHERE id_monitor = ?");
    $stmt->bind_param("i", $id_monitor);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    if ($count == 0) {
        throw new Exception("El monitor con ID $id_monitor no existe.");
    }

    // Eliminar los entrenamientos actuales del monitor
    $stmt = $conn->prepare("DELETE FROM monitor_especialidad WHERE id_monitor = ?");
    $stmt->bind_param("i", $id_monitor);
    $stmt->execute();
    $stmt->close();

    // Insertar los nuevos entrenamientos
    $stmt = $conn->prepare("INSERT INTO monitor_especialidad (id_monitor, id_especialidad) VALUES (?, ?)");
    foreach ($entrenamientos as $id_especialidad) {
        $stmt->bind_param("ii", $id_monitor, $id_especialidad);
        $stmt->execute();
    }
    $stmt->close();
}
function obtenerEspecialidades($conn)
{
    $sql = "SELECT id_especialidad AS id, nombre FROM especialidad";
    $result = $conn->query($sql);

    $especialidades = [];
    while ($row = $result->fetch_assoc()) {
        $especialidades[] = $row;
    }

    return $especialidades;
}
function obtenerEntrenamientos($conn)
{
    $sql = "SELECT id_especialidad AS id, nombre FROM especialidad";
    $result = $conn->query($sql);

    $entrenamientos = [];
    while ($row = $result->fetch_assoc()) {
        $entrenamientos[] = $row;
    }

    return $entrenamientos;
}
