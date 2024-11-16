<?php
require_once('general.php');

function obtenerClases($conn, $filtro = '')
{
    $sql = "SELECT c.id_clase, c.nombre, m.nombre AS especialidad, u.nombre AS monitor, 
                   c.fecha, c.horario, c.duracion, c.capacidad_maxima
            FROM clase c
            LEFT JOIN monitor mo ON c.id_monitor = mo.id_monitor
            LEFT JOIN usuario u ON mo.id_usuario = u.id_usuario
            LEFT JOIN especialidad m ON c.id_especialidad = m.id_especialidad";

    if (!empty($filtro)) {
        $sql .= " WHERE c.nombre LIKE ? OR u.nombre LIKE ? OR m.nombre LIKE ?";
    }

    $stmt = $conn->prepare($sql);

    if (!empty($filtro)) {
        $filtro_param = '%' . $filtro . '%';
        $stmt->bind_param("sss", $filtro_param, $filtro_param, $filtro_param);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $clases = [];

    while ($row = $result->fetch_assoc()) {
        $clases[] = $row;
    }

    $stmt->close();
    return $clases;
}


function crearClase($conn, $nombre, $id_monitor, $id_especialidad, $fecha, $horario, $duracion, $capacidad)
{
    $sql = "INSERT INTO clase (nombre, id_monitor, id_especialidad, fecha, horario, duracion, capacidad_maxima)
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("siissii", $nombre, $id_monitor, $id_especialidad, $fecha, $horario, $duracion, $capacidad);
    $stmt->execute();
    $stmt->close();
}

function eliminarClase($conn, $id_clase)
{
    $sql = "DELETE FROM clase WHERE id_clase = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_clase);
    $stmt->execute();
    $stmt->close();
}

function actualizarClase($conn, $id_clase, $nombre, $id_monitor, $id_especialidad, $fecha, $horario, $duracion, $capacidad)
{
    $sql = "UPDATE clase
            SET nombre = ?, id_monitor = ?, id_especialidad = ?, fecha = ?, horario = ?, duracion = ?, capacidad_maxima = ?
            WHERE id_clase = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("siissiii", $nombre, $id_monitor, $id_especialidad, $fecha, $horario, $duracion, $capacidad, $id_clase);
    $stmt->execute();
    $stmt->close();
}
