<?php

require_once('general.php');


// Obtiene todos los miembros de la base de datos
function obtenerMiembros($conn, $busqueda = '', $orden_columna = 'nombre', $orden_direccion = 'ASC')
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

    // Construir la consulta SQL para obtener solo los miembros
    $sql = "SELECT u.id_usuario, u.nombre, u.email, u.rol
            FROM usuario u
            INNER JOIN miembro m ON u.id_usuario = m.id_usuario";

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
