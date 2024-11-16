<?php
require_once('general.php');

function obtenerClases($conn, $filtros = [])
{
    $sql = "SELECT c.id_clase, c.nombre, m.nombre AS especialidad, u.nombre AS monitor, 
                   c.fecha, c.horario, c.duracion, c.capacidad_maxima
            FROM clase c
            LEFT JOIN monitor mo ON c.id_monitor = mo.id_monitor
            LEFT JOIN usuario u ON mo.id_usuario = u.id_usuario
            LEFT JOIN especialidad m ON c.id_especialidad = m.id_especialidad
            WHERE 1=1";

    $params = [];
    $types = "";

    if (!empty($filtros['nombre_clase'])) {
        $sql .= " AND c.nombre LIKE ?";
        $params[] = '%' . $filtros['nombre_clase'] . '%';
        $types .= "s";
    }

    if (!empty($filtros['nombre_monitor'])) {
        $sql .= " AND u.nombre LIKE ?";
        $params[] = '%' . $filtros['nombre_monitor'] . '%';
        $types .= "s";
    }

    if (!empty($filtros['especialidad'])) {
        $sql .= " AND m.nombre LIKE ?";
        $params[] = '%' . $filtros['especialidad'] . '%';
        $types .= "s";
    }

    if (!empty($filtros['fecha'])) {
        $sql .= " AND c.fecha = ?";
        $params[] = $filtros['fecha'];
        $types .= "s";
    }

    $stmt = $conn->prepare($sql);

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
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
