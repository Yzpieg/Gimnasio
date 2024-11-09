<?php

require_once('includes/user_functions.php');

verificarAdmin();

$conn = obtenerConexion();

// Llamada a la función principal para manejar acciones
manejarAccionUsuario($conn,);

$title = "Gestión de usuarios";
include 'includes/admin_header.php';

// Capturar el término de búsqueda si está presente
$busqueda = $_GET['busqueda'] ?? '';

// Construir la consulta SQL con el filtro de búsqueda
$sql = "SELECT id_usuario, nombre, email, rol FROM usuario WHERE id_usuario != ?";

// Agregar filtro de búsqueda a la consulta si hay un término
if ($busqueda) {
    $sql .= " AND (nombre LIKE ? OR email LIKE ?)";
}

// Preparar y ejecutar la consulta
$stmt = $conn->prepare($sql);
if ($busqueda) {
    $busqueda_param = '%' . $busqueda . '%';
    $stmt->bind_param("iss", $_SESSION['id_usuario'], $busqueda_param, $busqueda_param);
} else {
    $stmt->bind_param("i", $_SESSION['id_usuario']);
}
$stmt->execute();
$result = $stmt->get_result();

?>

<body>
    <main>
        <h2>Gestión de Usuarios</h2>

        <!-- Mostrar mensaje de confirmación si existe -->
        <?php if (isset($_GET['mensaje'])): ?>
            <div class="mensaje-confirmacion">
                <p><?php echo htmlspecialchars($_GET['mensaje']); ?></p>
            </div>
        <?php endif; ?>

        <!-- Formulario de búsqueda -->
        <div class="form_container">
            <form method="GET" action="usuarios.php">
                <input type="text" name="busqueda" placeholder="Buscar usuario..." value="<?php echo htmlspecialchars($busqueda); ?>">
                <button type="submit">Buscar</button>
            </form>
            <form action="crear_usuario.php" method="post" style="margin-top: 10px;">
                <button type="submit" title="Crea una nueva cuenta de usuario">Crear Usuario</button>
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
                    <td class="acciones">
                        <div class="button-container">
                            <form action="usuarios.php" method="POST" style="display:inline;">
                                <input type="hidden" name="id_usuario" value="<?php echo $row['id_usuario']; ?>">
                                <button type="submit" name="eliminar_usuario" onclick="return confirm('¿Estás seguro de que deseas eliminar esta cuenta? Esta acción no se puede deshacer.')" title="Eliminar definitivamente esta cuenta de usuario">Eliminar Cuenta</button>
                            </form>
                            <form action="usuarios.php" method="POST" style="display:inline;">
                                <input type="hidden" name="id_usuario" value="<?php echo $row['id_usuario']; ?>">
                                <button type="submit" name="crear_miembro" onclick="return confirm('¿Deseas asignar el rol de miembro a este usuario? Cualquier rol anterior será reemplazado.')" title="Asignar el rol de miembro a este usuario">Asignar Rol Miembro</button>
                            </form>
                            <form action="usuarios.php" method="POST" style="display:inline;">
                                <input type="hidden" name="id_usuario" value="<?php echo $row['id_usuario']; ?>">
                                <button type="submit" name="crear_monitor" onclick="return confirm('¿Estás seguro de que quieres asignar el rol de monitor a este usuario? Cualquier rol anterior será reemplazado.')" title="Asignar el rol de monitor a este usuario">Asignar Rol Monitor</button>
                            </form>
                            <form action="usuarios.php" method="POST" style="display:inline;">
                                <input type="hidden" name="id_usuario" value="<?php echo $row['id_usuario']; ?>">
                                <button type="submit" name="restaurar_usuario" onclick="return confirm('¿Estás seguro de que deseas quitar el rol especial de este usuario? El usuario mantendrá su cuenta básica.')" title="Quitar cualquier rol especial de este usuario">Quitar Rol Especial</button>
                            </form>
                            <form action="edit_usuario.php" method="GET" style="display:inline;">
                                <input type="hidden" name="id_usuario" value="<?php echo $row['id_usuario']; ?>">
                                <button type="submit" name="editar_usuario" title="Modificar el perfil de este usuario">Modificar Perfil</button>
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
    $stmt->close();
    $conn->close();
    ?>
</body>

</html>