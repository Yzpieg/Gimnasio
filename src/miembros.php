<?php

require_once('includes/member_functions.php');
require_once('includes/user_functions.php'); // Para la función verificarAdmin

verificarAdmin();

$conn = obtenerConexion();

// Manejar acción de eliminación
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['eliminar_usuario']) && isset($_POST['id_usuario'])) {
    $id_usuario = $_POST['id_usuario'];
    $resultado = eliminarMiembro($conn, $id_usuario);

    // Redirigir con un mensaje de confirmación o error
    $mensaje = $resultado['message'];
    header("Location: miembros.php?mensaje=" . urlencode($mensaje));
    exit();
}


// Capturar el término de búsqueda y los parámetros de ordenamiento
$busqueda = $_GET['busqueda'] ?? '';
$orden_columna = $_GET['orden'] ?? 'nombre';
$orden_direccion = $_GET['direccion'] ?? 'ASC';

// Obtener los miembros usando la función en member_functions.php
$miembros = obtenerMiembros($conn, $busqueda, $orden_columna, $orden_direccion);

$title = "Gestión de Miembros";
include 'includes/admin_header.php';

?>

<body>
    <main>
        <h2>Gestión de Miembros</h2>

        <!-- Mostrar mensaje de confirmación si existe -->
        <?php if (isset($_GET['mensaje'])): ?>
            <div class="mensaje-confirmacion">
                <p><?php echo htmlspecialchars($_GET['mensaje']); ?></p>
            </div>
        <?php endif; ?>

        <!-- Formulario de búsqueda -->
        <div class="form_container">
            <form method="GET" action="miembros.php">
                <input type="text" name="busqueda" placeholder="Buscar miembro..." value="<?php echo htmlspecialchars($busqueda); ?>">
                <button type="submit">Buscar</button>
            </form>
        </div>

        <!-- Tabla con lista de miembros y acciones -->
        <table>
            <tr>
                <th><a href="?orden=nombre&direccion=<?php echo ($orden_columna == 'nombre' && $orden_direccion == 'ASC') ? 'DESC' : 'ASC'; ?>">Nombre</a></th>
                <th><a href="?orden=email&direccion=<?php echo ($orden_columna == 'email' && $orden_direccion == 'ASC') ? 'DESC' : 'ASC'; ?>">Email</a></th>
                <th><a href="?orden=rol&direccion=<?php echo ($orden_columna == 'rol' && $orden_direccion == 'ASC') ? 'DESC' : 'ASC'; ?>">Rol</a></th>
                <th>Acciones</th>
            </tr>
            <?php foreach ($miembros as $miembro): ?>
                <tr>
                    <td><?php echo htmlspecialchars($miembro['nombre']); ?></td>
                    <td><?php echo htmlspecialchars($miembro['email']); ?></td>
                    <td><?php echo htmlspecialchars($miembro['rol']); ?></td>
                    <td class="acciones">
                        <div class="button-container">
                            <!-- Acción de eliminar -->
                            <form action="miembros.php" method="POST" style="display:inline;">
                                <input type="hidden" name="id_usuario" value="<?php echo $miembro['id_usuario']; ?>">
                                <button type="submit" name="eliminar_usuario" onclick="return confirm('¿Estás seguro de que deseas eliminar este miembro? Esta acción no se puede deshacer.')" title="Eliminar definitivamente este miembro">Eliminar</button>
                            </form>
                            <!-- Acción de editar -->
                            <form action="edit_usuario.php" method="GET" style="display:inline;">
                                <input type="hidden" name="id_usuario" value="<?php echo $miembro['id_usuario']; ?>">
                                <button type="submit" name="editar_usuario" title="Modificar el perfil de este miembro">Modificar Perfil</button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </main>

    <?php
    include 'includes/footer.php';
    $conn->close();
    ?>
</body>

</html>