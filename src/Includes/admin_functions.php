<?php

require_once('general.php');

// Función para obtener el conteo de miembros
function obtenerConteoMiembros($conn)
{
    $query = $conn->query("SELECT COUNT(*) AS total FROM miembro");
    return $query ? $query->fetch_assoc()['total'] : 0;
}

// Función para obtener el conteo de clases
function obtenerConteoClases($conn)
{
    $query = $conn->query("SELECT COUNT(*) AS total FROM clase");
    return $query ? $query->fetch_assoc()['total'] : 0;
}

// Función para obtener el conteo de monitores
function obtenerConteoMonitores($conn)
{
    $query = $conn->query("SELECT COUNT(*) AS total FROM monitor");
    return $query ? $query->fetch_assoc()['total'] : 0;
}

// Función para obtener las notificaciones
function obtenerNotificaciones($conn, $limit = 5)
{
    $stmt = $conn->prepare("SELECT * FROM notificacion WHERE leida = 0 ORDER BY fecha DESC LIMIT ?");
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    $notificaciones = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $notificaciones;
}
function agregarEspecialidad($conn, $nombre_especialidad)
{
    if (empty($nombre_especialidad)) {
        return "Por favor, introduce un nombre de especialidad.";
    }
    $stmt = $conn->prepare("INSERT INTO especialidad (nombre) VALUES (?)");
    $stmt->bind_param("s", $nombre_especialidad);
    if ($stmt->execute()) {
        $stmt->close();
        return "Especialidad añadida exitosamente.";
    } else {
        $error = "Error al añadir la especialidad: " . $stmt->error;
        $stmt->close();
        return $error;
    }
}

function editarEspecialidad($conn, $id_especialidad, $nombre_especialidad)
{
    if (empty($nombre_especialidad)) {
        return "Por favor, introduce un nombre de especialidad.";
    }
    $stmt = $conn->prepare("UPDATE especialidad SET nombre = ? WHERE id_especialidad = ?");
    $stmt->bind_param("si", $nombre_especialidad, $id_especialidad);
    if ($stmt->execute()) {
        $stmt->close();
        return "Especialidad actualizada exitosamente.";
    } else {
        $error = "Error al actualizar la especialidad: " . $stmt->error;
        $stmt->close();
        return $error;
    }
}

function eliminarEspecialidad($conn, $id_especialidad)
{
    $stmt = $conn->prepare("DELETE FROM especialidad WHERE id_especialidad = ?");
    $stmt->bind_param("i", $id_especialidad);
    if ($stmt->execute()) {
        $stmt->close();
        return "Especialidad eliminada exitosamente.";
    } else {
        $error = "Error al eliminar la especialidad: " . $stmt->error;
        $stmt->close();
        return $error;
    }
}
