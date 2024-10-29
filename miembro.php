<?php
session_start();

// Verificar si el usuario es un miembro y está autenticado
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] != 'miembro') {
    header("Location: index.php?error=No+has+iniciado+sesión+correctamente");
    exit();
}

// Conexión a la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gimnasio_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$id_usuario = $_SESSION['id_usuario'];

// Obtener los datos actuales del usuario
$stmt = $conn->prepare("SELECT nombre, email, telefono FROM usuarios WHERE id_usuario = ?");
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$stmt->bind_result($nombre, $email, $telefono);
$stmt->fetch();
$stmt->close();

// Actualizar los datos si se envía el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nuevo_nombre = $_POST['nombre'];
    $nuevo_telefono = $_POST['telefono'];
    $nueva_password = $_POST['password'];

    // Preparar la consulta de actualización
    if (!empty($nueva_password)) {
        // Encriptar la nueva contraseña si se proporciona
        $password_hash = password_hash($nueva_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE usuarios SET nombre = ?, telefono = ?, password = ? WHERE id_usuario = ?");
        $stmt->bind_param("sssi", $nuevo_nombre, $nuevo_telefono, $password_hash, $id_usuario);
    } else {
        // Actualizar sin cambiar la contraseña
        $stmt = $conn->prepare("UPDATE usuarios SET nombre = ?, telefono = ? WHERE id_usuario = ?");
        $stmt->bind_param("ssi", $nuevo_nombre, $nuevo_telefono, $id_usuario);
    }

    if ($stmt->execute()) {
        header("Location: miembro.php?mensaje=Datos+actualizados+correctamente");
        exit();
    } else {
        echo "Error al actualizar los datos: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil del Miembro</title>
    <link rel="stylesheet" href="estilos.css">
</head>

<body>
    <h2>Perfil del Usuario</h2>

    <!-- Mensaje de éxito si se actualizan los datos -->
    <?php if (isset($_GET['mensaje'])): ?>
        <div class="mensaje-confirmacion">
            <p><?php echo htmlspecialchars($_GET['mensaje']); ?></p>
        </div>
    <?php endif; ?>

    <form action="miembro.php" method="POST">
        <label for="nombre">Nombre:</label>
        <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($nombre); ?>" required>

        <label for="telefono">Teléfono:</label>
        <input type="text" id="telefono" name="telefono" value="<?php echo htmlspecialchars($telefono); ?>" required>

        <label for="password">Nueva Contraseña (opcional):</label>
        <input type="password" id="password" name="password">

        <button type="submit">Actualizar Datos</button>
    </form>
</body>

</html>