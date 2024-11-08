<?php
session_start();

// Verifica que el usuario ha iniciado sesión y tiene el rol de "monitor"
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] != 'monitor') {
    header("Location: index.php?error=Acceso+denegado");
    exit();
}

$nombre = $_SESSION['nombre'];
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido Monitor</title>
</head>

<body>
    <h2>Bienvenido, <?php echo htmlspecialchars($nombre); ?>!</h2>
    <p>Accede a todas tus actividades y servicios como monitor.</p>
</body>

</html>