<?php
require_once 'member_functions.php';

$conn = obtenerConexion();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_usuario = $_POST['id_usuario'];
    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $fecha_registro = $_POST['fecha_registro'];
    $id_membresia = $_POST['id_membresia'] ?? null;

    // Llama a la función para actualizar los datos
    $resultado = actualizarMiembro($conn, $id_usuario, $nombre, $email, $fecha_registro, $id_membresia);

    if ($resultado['success']) {
        header('Location: ../miembro.php?mensaje=perfil_actualizado');
        exit;
    } else {
        echo "Error: " . $resultado['message'];
    }
}
