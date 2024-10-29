<?php
session_start();

// Verificar si el usuario es un administrador
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'admin') {
    header("Location: index.php?error=No+tienes+permisos+de+administrador");
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

// Actualizar rol si se ha enviado el formulario de cambio de rol
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id_usuario'])) {
    if (isset($_POST['nuevo_rol'])) {
        // Actualizar el rol del usuario
        $id_usuario = $_POST['id_usuario'];
        $nuevo_rol = $_POST['nuevo_rol'];
        $stmt = $conn->prepare("UPDATE usuarios SET rol = ? WHERE id_usuario = ?");
        $stmt->bind_param("si", $nuevo_rol, $id_usuario);
        $stmt->execute();
        $stmt->close();
        header("Location: admin.php?mensaje=Rol+actualizado+correctamente");
        exit();
    } elseif (isset($_POST['eliminar_usuario'])) {
        // Eliminar el usuario
        $id_usuario = $_POST['id_usuario'];
        $stmt = $conn->prepare("DELETE FROM usuarios WHERE id_usuario = ?");
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $stmt->close();
        header("Location: admin.php?mensaje=Usuario+eliminado+correctamente");
        exit();
    }
}

// Obtener la lista de usuarios
$result = $conn->query("SELECT id_usuario, nombre, email, rol FROM usuarios");
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración</title>
    <link rel="stylesheet" href="estilos.css">
</head>

<body>
    <h2>Gestión de Usuarios</h2>

    <!-- Mostrar mensaje de éxito si el rol fue actualizado o el usuario fue eliminado -->
    <?php if (isset($_GET['mensaje'])): ?>
        <div class="mensaje-confirmacion">
            <p><?php echo htmlspecialchars($_GET['mensaje']); ?></p>
        </div>
    <?php endif; ?>
    <div class="table_container">
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
                        <!-- Formulario para actualizar rol -->
                        <form action="admin.php" method="POST" style="display:inline;">
                            <input type="hidden" name="id_usuario" value="<?php echo $row['id_usuario']; ?>">
                            <select name="nuevo_rol">
                                <option value="miembro" <?php echo $row['rol'] == 'miembro' ? 'selected' : ''; ?>>Miembro</option>
                                <option value="monitor" <?php echo $row['rol'] == 'monitor' ? 'selected' : ''; ?>>Monitor</option>
                                <option value="admin" <?php echo $row['rol'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                            </select>
                            <button type="submit">Actualizar Rol</button>
                        </form>

                        <!-- Formulario para eliminar usuario -->
                        <form action="admin.php" method="POST" style="display:inline;">
                            <input type="hidden" name="id_usuario" value="<?php echo $row['id_usuario']; ?>">
                            <input type="hidden" name="eliminar_usuario" value="1">
                            <button type="submit" onclick="return confirm('¿Estás seguro de que deseas eliminar este usuario?');">Eliminar</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>
</body>

</html>

<?php
$conn->close();
?>