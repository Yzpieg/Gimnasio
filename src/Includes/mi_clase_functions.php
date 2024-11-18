<?php

require_once 'member_functions.php';

/**
 * Obtener el ID del miembro.
 */
function obtenerIdMiembro($conn, $id_usuario)
{
    $sql = "SELECT id_miembro FROM miembro WHERE id_usuario = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        return $resultado->fetch_assoc()['id_miembro'];
    } else {
        throw new Exception("No se encontró información del miembro.");
    }
}

/**
 * Apuntarse a una clase.
 */
function apuntarseClase($conn, $id_clase, $id_miembro)
{
    // Verificar si el miembro ya está inscrito en la clase
    $sqlVerificar = "SELECT * FROM asistencia WHERE id_clase = ? AND id_miembro = ?";
    $stmtVerificar = $conn->prepare($sqlVerificar);
    $stmtVerificar->bind_param("ii", $id_clase, $id_miembro);
    $stmtVerificar->execute();
    $resultadoVerificar = $stmtVerificar->get_result();

    if ($resultadoVerificar->num_rows > 0) {
        return "ya_inscrito";
    }

    // Insertar el registro en la tabla asistencia
    $fecha_actual = date('Y-m-d');
    $sqlInsertar = "
        INSERT INTO asistencia (id_clase, id_miembro, fecha, asistencia)
        VALUES (?, ?, ?, 'presente')
    ";
    $stmtInsertar = $conn->prepare($sqlInsertar);
    $stmtInsertar->bind_param("iis", $id_clase, $id_miembro, $fecha_actual);
    $stmtInsertar->execute();

    if ($stmtInsertar->affected_rows > 0) {
        return "apuntado";
    } else {
        throw new Exception("Error al apuntarse a la clase.");
    }
}

/**
 * Borrarse de una clase.
 */
function borrarseClase($conn, $id_clase, $id_miembro)
{
    $sqlBorrar = "DELETE FROM asistencia WHERE id_clase = ? AND id_miembro = ?";
    $stmtBorrar = $conn->prepare($sqlBorrar);
    $stmtBorrar->bind_param("ii", $id_clase, $id_miembro);
    $stmtBorrar->execute();

    if ($stmtBorrar->affected_rows > 0) {
        return "borrado";
    } else {
        return "no_borrado";
    }
}
