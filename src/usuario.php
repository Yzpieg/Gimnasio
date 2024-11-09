<?php
session_start();
require_once('includes/user_functions.php');

$conn = obtenerConexion();

// Verificar si el usuario ha iniciado sesión, de lo contrario redirigir al inicio
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../index.php?error=Debes+iniciar+sesión+primero");
    exit();
}

$id_usuario = $_SESSION['id_usuario'];

// Obtener datos actuales del usuario
$datos_usuario = obtenerDatosUsuario($conn, $id_usuario);

// Procesar la actualización de datos cuando el formulario se envía (método POST)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nuevo_nombre = $_POST['nombre'];
    $nuevo_telefono = $_POST['telefono'];
    $nueva_contrasenya = $_POST['contrasenya'] ?: null;

    // Llamada a actualizarDatosUsuario con la página actual como parámetro de redirección
    actualizarDatosUsuario($conn, $id_usuario, $nuevo_nombre, $nuevo_telefono, $nueva_contrasenya, "usuario.php");
}


$conn->close();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil del Usuario</title>
    <link rel="stylesheet" href="../assets/css/estilos.css">
</head>

<body>
    <h2>Perfil del Usuario</h2>

    <!-- Mostrar mensaje de confirmación si los datos se actualizaron correctamente -->
    <?php if (isset($_GET['mensaje'])): ?>
        <div class="mensaje-confirmacion">
            <p><?php echo htmlspecialchars($_GET['mensaje']); ?></p>
        </div>
    <?php endif; ?>

    <!-- Formulario para que el usuario actualice sus datos personales -->
    <div class="form_container">
        <form action="usuario.php" method="POST" onsubmit="return valFormUsuario();">
            <label for="nombre">Nombre:</label>
            <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($datos_usuario['nombre']); ?>" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($datos_usuario['email']); ?>" disabled>

            <label for="telefono">Teléfono:</label>
            <input type="text" id="telefono" name="telefono" value="<?php echo htmlspecialchars($datos_usuario['telefono']); ?>" maxlength="9" pattern="\d{9}" title="Debe contener exactamente 9 dígitos numéricos" autocomplete="off">

            <label for="contrasenya">Contraseña (dejar en blanco para no cambiarla):</label>
            <input type="password" id="contrasenya" name="contrasenya" autocomplete="new-password">

            <label for="confirmar_contrasenya">Confirmar Contraseña:</label>
            <input type="password" id="confirmar_contrasenya" name="confirmar_contrasenya" autocomplete="new-password">

            <button type="submit">Actualizar Datos</button>
        </form>
    </div>
    <div class="form_container">
        <form action="includes/general.php" method="post">
            <input type="hidden" name="accion" value="logout">
            <button type="submit">Cerrar Sesión</button>
        </form>
    </div>


    <script src="../assets/js/validacion.js"></script>
</body>

</html>