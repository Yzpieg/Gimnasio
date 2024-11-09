<?php
require_once('includes/user_functions.php');

verificarAdmin();
$conn = obtenerConexion();

$title = "Editar Usuario";
include 'includes/admin_header.php';

// Obtener datos del usuario si se accede mediante GET o después de la actualización
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['id_usuario'])) {
    $id_usuario = $_GET['id_usuario'];
    $datos_usuario = obtenerDatosUsuario($conn, $id_usuario);

    if (!$datos_usuario) {
        echo "Usuario no encontrado";
        exit;
    }
}

// Procesar la actualización si se envía el formulario (POST)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_usuario = $_POST['id_usuario'];
    $nuevo_nombre = $_POST['nombre'];
    $nuevo_telefono = $_POST['telefono'];
    $nueva_contrasenya = $_POST['contrasenya'] ?: null;

    // Usar la función `actualizarDatosUsuario` con la página de redirección correcta
    actualizarDatosUsuario($conn, $id_usuario, $nuevo_nombre, $nuevo_telefono, $nueva_contrasenya, "edit_usuario.php?id_usuario=" . urlencode($id_usuario));

    // La redirección hace que el siguiente código no se ejecute, ya que la página se recarga
    exit();
}

$conn->close();

?>

<body>
    <h2>Editar Usuario</h2>
    <?php if (isset($_GET['mensaje'])): ?>
        <div class="mensaje-confirmacion">
            <p><?php echo htmlspecialchars($_GET['mensaje']); ?></p>
        </div>
    <?php endif; ?>

    <div class="form_container">
        <form action="edit_usuario.php" method="POST" onsubmit="return valFormUsuario();">
            <input type="hidden" name="id_usuario" value="<?php echo htmlspecialchars($id_usuario); ?>">

            <label for="nombre">Nombre:</label>
            <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($datos_usuario['nombre'] ?? ''); ?>" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($datos_usuario['email'] ?? ''); ?>" disabled>

            <label for="rol">Rol:</label>
            <input type="text" id="rol" name="rol" value="<?php echo htmlspecialchars($datos_usuario['rol'] ?? ''); ?>" disabled>

            <label for="telefono">Teléfono:</label>
            <input type="text" id="telefono" name="telefono" value="<?php echo htmlspecialchars($datos_usuario['telefono'] ?? ''); ?>" maxlength="9" pattern="\d{9}" title="Debe contener exactamente 9 dígitos numéricos" autocomplete="off">

            <label for="contrasenya">Contraseña (dejar en blanco para no cambiarla):</label>
            <input type="password" id="contrasenya" name="contrasenya" autocomplete="new-password">

            <label for="confirmar_contrasenya">Confirmar Contraseña:</label>
            <input type="password" id="confirmar_contrasenya" name="confirmar_contrasenya" autocomplete="new-password">

            <button type="submit">Actualizar Datos</button>
        </form>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>

</html>