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
    $nuevo_email = $_POST['email']; // Asegúrate de capturar el email aquí
    $nuevo_telefono = $_POST['telefono'];
    $nuevo_rol = $_POST['rol'];
    $nueva_contrasenya = $_POST['contrasenya'] ?: null;

    // Llamada a modUsuario con todos los parámetros necesarios
    modUsuario($conn, $id_usuario, $nuevo_nombre, $nuevo_email, $nuevo_telefono, $nuevo_rol, $nueva_contrasenya, "edit_usuario.php?id_usuario=" . urlencode($id_usuario));

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

            <!-- Campo para editar el nombre -->
            <label for="nombre">Nombre:</label>
            <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($datos_usuario['nombre'] ?? ''); ?>" required aria-label="Nombre completo del usuario">

            <!-- Campo para editar el email -->
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($datos_usuario['email'] ?? ''); ?>" required title="Introduce el email del usuario" aria-label="Correo electrónico del usuario">

            <!-- Selector para editar el rol -->
            <label for="rol">Rol:</label>
            <select id="rol" name="rol" required title="Selecciona el rol del usuario" aria-label="Rol del usuario">
                <option value="usuario" <?php echo ($datos_usuario['rol'] ?? '') == 'usuario' ? 'selected' : ''; ?>>Usuario</option>
                <option value="miembro" <?php echo ($datos_usuario['rol'] ?? '') == 'miembro' ? 'selected' : ''; ?>>Miembro</option>
                <option value="monitor" <?php echo ($datos_usuario['rol'] ?? '') == 'monitor' ? 'selected' : ''; ?>>Monitor</option>
                <option value="admin" <?php echo ($datos_usuario['rol'] ?? '') == 'admin' ? 'selected' : ''; ?>>Administrador</option>
            </select>

            <!-- Campo para editar el teléfono -->
            <label for="telefono">Teléfono:</label>
            <input type="text" id="telefono" name="telefono" value="<?php echo htmlspecialchars($datos_usuario['telefono'] ?? ''); ?>" maxlength="9" pattern="\d{9}" title="Debe contener exactamente 9 dígitos numéricos" autocomplete="off" aria-label="Número de teléfono del usuario">

            <!-- Campo para editar la contraseña -->
            <label for="contrasenya">Contraseña (dejar en blanco para no cambiarla):</label>
            <input type="password" id="contrasenya" name="contrasenya" autocomplete="new-password" aria-label="Nueva contraseña para el usuario" title="Introduce una nueva contraseña solo si deseas cambiarla">

            <!-- Campo para confirmar la contraseña -->
            <label for="confirmar_contrasenya">Confirmar Contraseña:</label>
            <input type="password" id="confirmar_contrasenya" name="confirmar_contrasenya" autocomplete="new-password" aria-label="Confirmación de la nueva contraseña" title="Repite la nueva contraseña para confirmar">

            <!-- Botón para actualizar los datos -->
            <button type="submit">Actualizar Datos</button>
        </form>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>