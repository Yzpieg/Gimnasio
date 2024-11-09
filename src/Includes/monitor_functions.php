<?php

// Obtiene todos los monitores de la base de datos
function obtenerMonitores($conn, $busqueda = '', $orden_columna = 'nombre', $orden_direccion = 'ASC')
{
    // Validar columnas y dirección para evitar inyecciones SQL
    $columnas_validas = ['nombre', 'email'];
    $direccion_valida = ['ASC', 'DESC'];

    if (!in_array($orden_columna, $columnas_validas)) {
        $orden_columna = 'nombre';
    }
    if (!in_array($orden_direccion, $direccion_valida)) {
        $orden_direccion = 'ASC';
    }

    // Construir la consulta SQL para obtener solo los monitores
    $sql = "SELECT u.id_usuario, u.nombre, u.email, u.rol
            FROM usuario u
            INNER JOIN monitor m ON u.id_usuario = m.id_usuario";

    // Agregar filtro de búsqueda si se proporciona un término
    if ($busqueda) {
        $sql .= " WHERE u.nombre LIKE ? OR u.email LIKE ?";
    }

    // Agregar ordenamiento
    $sql .= " ORDER BY $orden_columna $orden_direccion";

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
