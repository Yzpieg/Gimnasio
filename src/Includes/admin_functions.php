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

// admin_functions.php

function agregarMembresia($conn, $tipo, $precio, $duracion, $beneficios)
{
    $stmt = $conn->prepare("INSERT INTO membresia (tipo, precio, duracion, beneficios) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sdis", $tipo, $precio, $duracion, $beneficios);
    if ($stmt->execute()) {
        $stmt->close();
        return "Membresía añadida exitosamente.";
    } else {
        $error = "Error al añadir la membresía: " . $stmt->error;
        $stmt->close();
        return $error;
    }
}

function editarMembresia($conn, $id_membresia, $tipo, $precio, $duracion, $beneficios)
{
    $stmt = $conn->prepare("UPDATE membresia SET tipo = ?, precio = ?, duracion = ?, beneficios = ? WHERE id_membresia = ?");
    $stmt->bind_param("sdiss", $tipo, $precio, $duracion, $beneficios, $id_membresia);
    if ($stmt->execute()) {
        $stmt->close();
        return "Membresía actualizada exitosamente.";
    } else {
        $error = "Error al actualizar la membresía: " . $stmt->error;
        $stmt->close();
        return $error;
    }
}

function eliminarMembresia($conn, $id_membresia)
{
    $stmt = $conn->prepare("DELETE FROM membresia WHERE id_membresia = ?");
    $stmt->bind_param("i", $id_membresia);
    if ($stmt->execute()) {
        $stmt->close();
        return "Membresía eliminada exitosamente.";
    } else {
        $error = "Error al eliminar la membresía: " . $stmt->error;
        $stmt->close();
        return $error;
    }
}
function asignarMembresiaAlMiembro($conn, $id_miembro, $id_membresia)
{
    // Obtener información de la membresía para calcular la fecha de expiración y monto pagado
    $stmt = $conn->prepare("SELECT precio, duracion FROM membresia WHERE id_membresia = ?");
    $stmt->bind_param("i", $id_membresia);
    $stmt->execute();
    $stmt->bind_result($precio, $duracion);

    if ($stmt->fetch()) {
        // Calcular fechas
        $fecha_inicio = date("Y-m-d");
        $fecha_fin = date("Y-m-d", strtotime("+$duracion months"));

        // Insertar el registro en la tabla miembro_membresía
        $stmt->close();

        $insert_stmt = $conn->prepare("INSERT INTO miembro_membresía (id_miembro, id_membresia, monto_pagado, fecha_inicio, fecha_fin, estado) VALUES (?, ?, ?, ?, ?, 'activa')");
        $insert_stmt->bind_param("iisss", $id_miembro, $id_membresia, $precio, $fecha_inicio, $fecha_fin);

        if ($insert_stmt->execute()) {
            $insert_stmt->close();
            return "Membresía asignada exitosamente.";
        } else {
            $error = "Error al asignar la membresía: " . $insert_stmt->error;
            $insert_stmt->close();
            return $error;
        }
    } else {
        $stmt->close();
        return "La membresía especificada no existe.";
    }
}
