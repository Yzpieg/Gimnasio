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
