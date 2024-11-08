<?php
session_start();
require_once('../src/db_connection.php');

// Verificar que el usuario esté logueado como administrador
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'admin') {
    header("Location: ../index.php?error=No+tienes+permisos+de+administrador");
    exit();
}

// Variables para manejar mensajes de éxito o error
$mensaje = '';
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Crear usuario manualmente con validación en el servidor
    if (isset($_POST['crear_usuario'])) {
        $nombre = $_POST['nombre'];
        $email = $_POST['email'];
        $contrasena = $_POST['contrasena'];
        $confirmarContrasena = $_POST['confirmar_contrasena'];
        $rol = $_POST['rol'];

        // Validación en el servidor
        if (!preg_match("/[a-zA-Z]/", $nombre)) {
            $error = 'Por favor, ingresa un nombre válido con al menos una letra.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Por favor, ingresa un correo electrónico válido.';
        } elseif (strlen($contrasena) < 6) {
            $error = 'La contraseña debe tener al menos 6 caracteres.';
        } elseif ($contrasena !== $confirmarContrasena) {
            $error = 'Las contraseñas no coinciden.';
        } else {
            // Si la validación pasa, se procede a crear el usuario
            $contrasenaHashed = password_hash($contrasena, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("INSERT INTO usuario (nombre, email, contrasenya, rol) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $nombre, $email, $contrasenaHashed, $rol);

            if ($stmt->execute()) {
                $mensaje = 'Usuario creado correctamente.';
            } else {
                $error = 'Error al crear el usuario.';
            }
            $stmt->close();
        }
    }

    // Aquí puedes agregar otras funcionalidades como eliminar o editar usuarios
}

// Obtener lista de usuarios para mostrar en la tabla
$usuarios = $conn->query("SELECT id_usuario, nombre, email, rol FROM usuario ORDER BY nombre");
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Gestión de Usuarios</title>
    <link rel="stylesheet" href="../assets/css/estilos.css">
    <script src="../assets/js/validacion.js"></script>
</head>

<body>
    <h2>Gestión de Usuarios</h2>

    <?php if ($mensaje): ?>
        <div class="mensaje-confirmacion"><?php echo htmlspecialchars($mensaje); ?></div>
    <?php elseif ($error): ?>
        <div class="mensaje-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <!-- Formulario para crear un nuevo usuario -->
    <section class="form_container">
        <h3>Agregar Usuario Manualmente</h3>
        <form method="POST" action="" onsubmit="return validarFormulario()">
            <label for="nombre">Nombre:</label>
            <input type="text" id="nombre" name="nombre" required>
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
            <label for="contrasena">Contraseña:</label>
            <input type="password" id="contrasena" name="contrasena" required>
            <label for="confirmar_contrasena">Confirmar Contraseña:</label>
            <input type="password" id="confirmar_contrasena" name="confirmar_contrasena" required>
            <label for="rol">Rol:</label>
            <select name="rol" id="rol" required>
                <option value="usuario">Usuario</option>
                <option value="miembro">Miembro</option>
                <option value="monitor">Monitor</option>
                <option value="admin">Administrador</option>
            </select>
            <button type="submit" name="crear_usuario">Crear Usuario</button>
        </form>
    </section>

    <!-- Tabla de usuarios con opciones de edición y eliminación -->
    <table class="table_container">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Email</th>
                <th>Rol</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($usuario = $usuarios->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($usuario['nombre']); ?></td>
                    <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                    <td><?php echo htmlspecialchars($usuario['rol']); ?></td>
                    <td>
                        <!-- Botón para eliminar usuario -->
                        <form method="POST" action="" style="display:inline;">
                            <input type="hidden" name="id_usuario" value="<?php echo $usuario['id_usuario']; ?>">
                            <button type="submit" name="eliminar_usuario" onclick="return confirm('¿Eliminar este usuario?')">Eliminar</button>
                        </form>

                        <!-- Botón para editar rol del usuario -->
                        <form method="POST" action="" style="display:inline;">
                            <input type="hidden" name="id_usuario" value="<?php echo $usuario['id_usuario']; ?>">
                            <select name="rol" required>
                                <option value="usuario" <?php if ($usuario['rol'] == 'usuario') echo 'selected'; ?>>Usuario</option>
                                <option value="miembro" <?php if ($usuario['rol'] == 'miembro') echo 'selected'; ?>>Miembro</option>
                                <option value="monitor" <?php if ($usuario['rol'] == 'monitor') echo 'selected'; ?>>Monitor</option>
                                <option value="admin" <?php if ($usuario['rol'] == 'admin') echo 'selected'; ?>>Administrador</option>
                            </select>
                            <button type="submit" name="editar_usuario">Actualizar Rol</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <a href="admin.php" class="btn-volver">Volver al inicio</a>
</body>

</html>