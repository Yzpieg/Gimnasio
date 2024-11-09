<?php

require_once('includes/user_functions.php');

verificarAdmin();

$conn = obtenerConexion();

// Llamada a la función principal para manejar acciones
manejarAccionUsuario($conn);

$title = "Gestión de usuarios";
include 'includes/admin_header.php';

// Obtener todos los usuarios registrados excepto el admin actual
$result = $conn->query("SELECT id_usuario, nombre, email, rol FROM usuario WHERE id_usuario != {$_SESSION['id_usuario']}");
?>

<body>

    <main>
        <h2>Gestión de Usuarios</h2>

        <!-- Mostrar mensaje de confirmación si existe, recibido como parámetro en la URL -->
        <?php if (isset($_GET['mensaje'])): ?>
            <div class="mensaje-confirmacion">
                <p><?php echo htmlspecialchars($_GET['mensaje']); ?></p>
            </div>
        <?php endif; ?>

        <!-- Botón para crear un nuevo usuario -->
        <div class="form_container">
            <form action="crear_usuario.php" method="post">
                <button type="submit">Crear Usuario</button>
            </form>
        </div>

        <!-- Tabla con lista de usuarios y acciones -->
        <table>
            <tr>
                <th>Nombre</th>
                <th>Email</th>
                <th>Rol</th>
                <th>Acciones</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['nombre']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td><?php echo htmlspecialchars($row['rol']); ?></td>
                    <td>
                        <div class="button-container">
                            <form action="usuarios.php" method="POST" style="display:inline;">
                                <input type="hidden" name="id_usuario" value="<?php echo $row['id_usuario']; ?>">
                                <button type="submit" name="eliminar_usuario" onclick="return confirm('¿Estás seguro de eliminar este usuario?')">Eliminar Usuario</button>
                            </form>
                            <form action="usuarios.php" method="POST" style="display:inline;">
                                <input type="hidden" name="id_usuario" value="<?php echo $row['id_usuario']; ?>">
                                <button type="submit" name="crear_miembro" onclick="return confirm('¿Estás seguro de crear este miembro?')">Crear Miembro</button>
                            </form>
                            <form action="usuarios.php" method="POST" style="display:inline;">
                                <input type="hidden" name="id_usuario" value="<?php echo $row['id_usuario']; ?>">
                                <button type="submit" name="crear_monitor" onclick="return confirm('¿Estás seguro de crear este monitor?')">Crear Monitor</button>
                            </form>
                            <form action="usuarios.php" method="POST" style="display:inline;">
                                <input type="hidden" name="id_usuario" value="<?php echo $row['id_usuario']; ?>">
                                <button type="submit" name="restaurar_usuario" onclick="return confirm('¿Estás seguro de restaurar este usuario?')" title="Elimina el rol miembro o monitor, pero conserva el usuario">Restaurar Usuario</button>
                            </form>
                            <form action="edit_usuario.php" method="GET" style="display:inline;">
                                <input type="hidden" name="id_usuario" value="<?php echo $row['id_usuario']; ?>">
                                <button type="submit" name="editar_usuario">Editar Usuario</button>
                            </form>

                        </div>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    </main>

    <?php
    // Incluir el footer y luego cerrar la conexión
    include 'includes/footer.php';
    $conn->close();
    ?>
</body>

</html>