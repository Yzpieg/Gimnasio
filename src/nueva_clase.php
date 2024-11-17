<?php
require_once 'admin_header.php';
require_once 'includes/class_functions.php';
require_once('includes/admin_functions.php');
verificarAdmin();
$conn = obtenerConexion();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $id_monitor = $_POST['id_monitor'];
    $id_especialidad = $_POST['id_especialidad'];
    $fecha = $_POST['fecha'];
    $horario = $_POST['horario'];
    $duracion = $_POST['duracion'];
    $capacidad = $_POST['capacidad'];

    crearClase($conn, $nombre, $id_monitor, $id_especialidad, $fecha, $horario, $duracion, $capacidad);

    header('Location: clases.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <title>Crear Clase</title>
</head>

<body>
    <main>
        <h1>Crear Nueva Clase</h1>
        <form method="POST">
            <label>Nombre:</label>
            <input type="text" name="nombre" required>

            <label>Monitor:</label>
            <select name="id_monitor">
                <?php
                // Obtener monitores de la base de datos
                $monitores = $conn->query("SELECT mo.id_monitor, u.nombre FROM monitor mo JOIN usuario u ON mo.id_usuario = u.id_usuario");
                while ($monitor = $monitores->fetch_assoc()) {
                    echo "<option value='{$monitor['id_monitor']}'>{$monitor['nombre']}</option>";
                }
                ?>
            </select>

            <label>Especialidad:</label>
            <select name="id_especialidad">
                <?php
                $especialidades = $conn->query("SELECT id_especialidad, nombre FROM especialidad");
                while ($especialidad = $especialidades->fetch_assoc()) {
                    echo "<option value='{$especialidad['id_especialidad']}'>{$especialidad['nombre']}</option>";
                }
                ?>
            </select>

            <label>Fecha:</label>
            <input type="date" name="fecha" required>

            <label>Horario:</label>
            <input type="time" name="horario" required>

            <label>Duración (min):</label>
            <input type="number" name="duracion" required>

            <label>Capacidad Máxima:</label>
            <input type="number" name="capacidad" required>

            <button type="submit">Crear Clase</button>
        </form>
    </main>
</body>

</html>