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
        <form action="edit_usuario.php" method="POST" onsubmit="return validarFormulario();">
            <input type="hidden" name="id_usuario" value="<?php echo htmlspecialchars($id_usuario); ?>">

            <label for="nombre">Nombre:</label>
            <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($datos_usuario['nombre'] ?? ''); ?>" required aria-label="Nombre completo del usuario">

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($datos_usuario['email'] ?? ''); ?>" disabled title="El email no se puede editar" aria-label="Correo electrónico del usuario" aria-describedby="emailHelp">
            <small id="emailHelp" style="display: block; color: #555; font-size: 12px;">* El email no se puede modificar.</small>

            <label for="rol">Rol:</label>
            <input type="text" id="rol" name="rol" value="<?php echo htmlspecialchars($datos_usuario['rol'] ?? ''); ?>" disabled title="Para modificar el rol, dirígete a la pantalla de gestión de usuarios" aria-describedby="rolHelp">
            <small id="rolHelp" style="display: block; color: #555; font-size: 12px;">* Para modificar el rol, dirígete a la pantalla de gestión de usuarios.</small>

            <label for="telefono">Teléfono:</label>
            <input type="text" id="telefono" name="telefono" value="<?php echo htmlspecialchars($datos_usuario['telefono'] ?? ''); ?>" maxlength="9" pattern="\d{9}" title="Debe contener exactamente 9 dígitos numéricos" autocomplete="off" aria-label="Número de teléfono del usuario">

            <label for="contrasenya">Contraseña (dejar en blanco para no cambiarla):</label>
            <input type="password" id="contrasenya" name="contrasenya" autocomplete="new-password" aria-label="Nueva contraseña para el usuario" title="Introduce una nueva contraseña solo si deseas cambiarla">

            <label for="confirmar_contrasenya">Confirmar Contraseña:</label>
            <input type="password" id="confirmar_contrasenya" name="confirmar_contrasenya" autocomplete="new-password" aria-label="Confirmación de la nueva contraseña" title="Repite la nueva contraseña para confirmar">

            <button type="submit">Actualizar Datos</button>
        </form>
    </div>

    <script>
        // Validación en JavaScript para confirmar la contraseña
        function valFormUsuario() {
            var contrasenya = document.getElementById("contrasenya").value;
            var confirmarContrasenya = document.getElementById("confirmar_contrasenya").value;
            if (contrasenya !== confirmarContrasenya) {
                alert("Las contraseñas no coinciden. Por favor, revisa los campos de contraseña.");
                return false;
            }
            return true;
        }
    </script>

    <?php include 'includes/footer.php'; ?>
</body>