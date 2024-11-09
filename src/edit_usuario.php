<?php
require_once('includes/user_functions.php');

verificarAdmin();
$conn = obtenerConexion();

$title = "Editar Usuario";
include 'includes/admin_header.php';

// Obtener datos del usuario si se accede mediante GET (para cargar el formulario con datos actuales)
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['id_usuario'])) {
    $id_usuario = $_GET['id_usuario'];

    $stmt = $conn->prepare("SELECT nombre, email, telefono, rol FROM usuario WHERE id_usuario = ?");
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $result = $stmt->get_result();


    if ($result->num_rows > 0) {
        $datos_usuario = $result->fetch_assoc();
    } else {
        echo "Usuario no encontrado";
        exit;
    }

    $stmt->close();
}

// Procesar la actualización si se envía el formulario (POST)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_usuario = $_POST['id_usuario'];
    $nuevo_nombre = $_POST['nombre'];
    $nuevo_telefono = $_POST['telefono'];
    $nueva_contrasenya = $_POST['contrasenya'];

    // Actualizar los datos en la base de datos
    if (!empty($nueva_contrasenya)) {
        // Si se ha ingresado una nueva contraseña
        $hashedPassword = password_hash($nueva_contrasenya, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE usuario SET nombre = ?, telefono = ?, contrasenya = ? WHERE id_usuario = ?");
        $stmt->bind_param("sssi", $nuevo_nombre, $nuevo_telefono, $hashedPassword, $id_usuario);
    } else {
        // Actualizar sin cambiar la contraseña
        $stmt = $conn->prepare("UPDATE usuario SET nombre = ?, telefono = ? WHERE id_usuario = ?");
        $stmt->bind_param("ssi", $nuevo_nombre, $nuevo_telefono, $id_usuario);
    }

    if ($stmt->execute()) {
        header("Location: edit_usuario.php?mensaje=Usuario+actualizado+correctamente");
        exit();
    } else {
        header("Location: edit_usuario.php?mensaje=Error+al+actualizar+el+usuario");
    }
    $stmt->close();
}

$conn->close();
?>

<body>
    <h2>Editar Usuario</h2>

    <!-- Mensaje de confirmación si existe, se muestra tras una actualización exitosa -->
    <?php if (isset($_GET['mensaje'])): ?>
        <div class="mensaje-confirmacion">
            <p><?php echo htmlspecialchars($_GET['mensaje']); ?></p>
        </div>
    <?php endif; ?>

    <div class="form_container">
        <!-- Formulario de edición de usuario, llamando a `valFormUsuario()` en el evento onsubmit -->
        <form action="edit_usuario.php" method="POST" onsubmit="return valFormUsuario();">
            <!-- Campo oculto para almacenar el ID del usuario y enviarlo en la solicitud POST -->
            <input type="hidden" name="id_usuario" value="<?php echo htmlspecialchars($id_usuario); ?>">

            <!-- Campo para el nombre del usuario, precargado con el valor actual desde `$datos_usuario` -->
            <label for="nombre">Nombre:</label>
            <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($datos_usuario['nombre']); ?>" required>

            <!-- Campo para el correo electrónico del usuario (solo lectura) -->
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($datos_usuario['email']); ?>" disabled>

            <!-- Campo para el rol del usuario (solo lectura) -->
            <label for="rol">Rol:</label>
            <input type="text" id="rol" name="rol" value="<?php echo htmlspecialchars($datos_usuario['rol']); ?>" disabled>

            <!-- Campo para el número de teléfono del usuario, validado para que tenga exactamente 9 dígitos -->
            <label for="telefono">Teléfono:</label>
            <input type="text" id="telefono" name="telefono" value="<?php echo htmlspecialchars($datos_usuario['telefono']); ?>" maxlength="9" pattern="\d{9}" title="Debe contener exactamente 9 dígitos numéricos" autocomplete="off">

            <!-- Campo opcional para ingresar una nueva contraseña si se desea actualizar -->
            <label for="contrasenya">Contraseña (dejar en blanco para no cambiarla):</label>
            <input type="password" id="contrasenya" name="contrasenya" autocomplete="new-password">

            <!-- Campo para confirmar la nueva contraseña ingresada, si se desea actualizar -->
            <label for="confirmar_contrasenya">Confirmar Contraseña:</label>
            <input type="password" id="confirmar_contrasenya" name="confirmar_contrasenya" autocomplete="new-password">

            <!-- Botón para enviar el formulario y actualizar los datos -->
            <button type="submit">Actualizar Datos</button>
        </form>
    </div>


    <?php include 'includes/footer.php'; ?>
</body>


</html>