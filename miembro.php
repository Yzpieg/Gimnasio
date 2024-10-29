<?php
session_start();

// Verificar si el usuario está autenticado y tiene el rol "miembro"
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] != 'miembro') {
    // Redirigir a la página de inicio con un mensaje de error si no está autenticado correctamente
    header("Location: index.php?error=No+has+iniciado+sesión+correctamente");
    exit();
}

// Conexión a la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gimnasio_db";

// Crear conexión a la base de datos
$conn = new mysqli($servername, $username, $password, $dbname);

// Comprobar si hay errores de conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Obtener el ID del usuario autenticado
$id_usuario = $_SESSION['id_usuario'];

// Preparar la consulta para obtener los datos actuales del usuario
$stmt = $conn->prepare("SELECT nombre, email, telefono FROM usuarios WHERE id_usuario = ?");
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$stmt->bind_result($nombre, $email, $telefono);
$stmt->fetch();
$stmt->close();

// Actualizar los datos del usuario si se ha enviado el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verificar y asignar los valores nuevos o mantener los actuales si están vacíos
    $nuevo_nombre = !empty($_POST['nombre']) ? $_POST['nombre'] : $nombre;
    $nuevo_telefono = !empty($_POST['telefono']) ? $_POST['telefono'] : $telefono;
    $nueva_password = $_POST['password'];

    // Preparar la consulta de actualización, dependiendo de si se ingresó una nueva contraseña
    if (!empty($nueva_password)) {
        // Encriptar la nueva contraseña si se proporciona
        $password_hash = password_hash($nueva_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE usuarios SET nombre = ?, telefono = ?, password = ? WHERE id_usuario = ?");
        $stmt->bind_param("sssi", $nuevo_nombre, $nuevo_telefono, $password_hash, $id_usuario);
    } else {
        // Actualizar los datos sin cambiar la contraseña
        $stmt = $conn->prepare("UPDATE usuarios SET nombre = ?, telefono = ? WHERE id_usuario = ?");
        $stmt->bind_param("ssi", $nuevo_nombre, $nuevo_telefono, $id_usuario);
    }

    // Ejecutar la actualización y redirigir con mensaje de éxito si es exitoso
    if ($stmt->execute()) {
        header("Location: miembro.php?mensaje=Datos+actualizados+correctamente");
        exit();
    } else {
        // Mostrar error si ocurre algún problema al actualizar
        echo "Error al actualizar los datos: " . $stmt->error;
    }

    $stmt->close();
}

// Cerrar la conexión a la base de datos
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil del Miembro</title>
    <!-- Enlace al archivo de estilos CSS -->
    <link rel="stylesheet" href="estilos.css">
</head>

<body>
    <h2>Perfil del Usuario</h2>

    <!-- Mostrar mensaje de confirmación si los datos se actualizaron correctamente -->
    <?php if (isset($_GET['mensaje'])): ?>
        <div class="mensaje-confirmacion">
            <p><?php echo htmlspecialchars($_GET['mensaje']); ?></p>
        </div>
    <?php endif; ?>

    <!-- Formulario para actualizar los datos del usuario -->
    <div class="form-container">
        <form action="miembro.php" method="POST" onsubmit="return valFormMiembro();">
            <!-- Campo para actualizar el nombre del usuario -->
            <label for="nombre">Nombre:</label>
            <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($nombre); ?>">

            <!-- Campo de email (deshabilitado para evitar edición) -->
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" disabled>

            <!-- Campo para actualizar el teléfono del usuario -->
            <label for="telefono">Teléfono:</label>
            <input type="text" id="telefono" name="telefono" value="<?php echo htmlspecialchars($telefono); ?>">

            <!-- Campo opcional para actualizar la contraseña (se mantiene sin cambios si está vacío) -->
            <label for="password">Contraseña (dejar en blanco para no cambiarla):</label>
            <input type="password" id="password" name="password" autocomplete="new-password">

            <!-- Botón para enviar el formulario y actualizar los datos -->
            <button type="submit">Actualizar Datos</button>
        </form>
    </div>

    <!-- Enlace al archivo JavaScript para validaciones de formulario -->
    <script src="validacion.js"></script>
</body>

</html>