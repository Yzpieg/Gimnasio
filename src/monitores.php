<?php

require_once('includes/monitor_functions.php');
require_once('includes/user_functions.php'); // Para la función verificarAdmin

verificarAdmin();

$conn = obtenerConexion();

// Manejar acción de eliminación
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['eliminar_usuario']) && isset($_POST['id_usuario'])) {
    $id_usuario = $_POST['id_usuario'];
    $resultado = eliminarMonitor($conn, $id_usuario);

    // Redirigir con un mensaje de confirmación o error
    $mensaje = $resultado['message'];
    header("Location: monitores.php?mensaje=" . urlencode($mensaje));
    exit();
}

// Capturar el término de búsqueda, especialidad, disponibilidad y los parámetros de ordenamiento
$busqueda = $_GET['busqueda'] ?? '';
$especialidad_filtro = $_GET['especialidad'] ?? '';
$disponibilidad_filtro = $_GET['disponibilidad'] ?? '';
$orden_columna = $_GET['orden'] ?? 'nombre';
$orden_direccion = $_GET['direccion'] ?? 'ASC';

// Obtener los monitores usando la función en monitor_functions.php
$monitores = obtenerMonitores($conn, $busqueda, $orden_columna, $orden_direccion, $especialidad_filtro, $disponibilidad_filtro);

// Obtener la lista de especialidades
$especialidades = obtenerEspecialidades($conn);

$title = "Gestión de Monitores";
include 'includes/admin_header.php';

?>

<body>
    <main>
        <h2>Gestión de Monitores</h2>

        <!-- Mostrar mensaje de confirmación si existe -->
        <?php if (isset($_GET['mensaje'])): ?>
            <div class="mensaje-confirmacion">
                <p><?php echo htmlspecialchars($_GET['mensaje']); ?></p>
            </div>
        <?php endif; ?>

        <!-- Formulario de búsqueda -->
        <div class="form_container">
            <form method="GET" action="monitores.php">
                <input type="text" name="busqueda" placeholder="Buscar monitor..." value="<?php echo htmlspecialchars($busqueda); ?>">

                <!-- Menú desplegable para buscar por especialidad -->
                <select name="especialidad">
                    <option value="">Todas las especialidades</option>
                    <?php foreach ($especialidades as $especialidad): ?>
                        <option value="<?php echo htmlspecialchars($especialidad['id_especialidad']); ?>" <?php echo ($especialidad_filtro == $especialidad['id_especialidad']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($especialidad['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <!-- Menú desplegable para buscar por disponibilidad -->
                <label for="disponibilidad">Disponibilidad:</label>
                <select name="disponibilidad" id="disponibilidad">
                    <option value="">Cualquiera</option>
                    <option value="Disponible" <?php echo ($disponibilidad_filtro === 'Disponible') ? 'selected' : ''; ?>>Disponible</option>
                    <option value="No disponible" <?php echo ($disponibilidad_filtro === 'No disponible') ? 'selected' : ''; ?>>No disponible</option>
                </select>

                <button type="submit">Buscar</button>
            </form>
        </div>

        <!-- Tabla con lista de monitores y acciones -->
        <table>
            <tr>
                <th><a href="?orden=nombre&direccion=<?php echo ($orden_columna == 'nombre' && $orden_direccion == 'ASC') ? 'DESC' : 'ASC'; ?>">Nombre</a></th>
                <th><a href="?orden=email&direccion=<?php echo ($orden_columna == 'email' && $orden_direccion == 'ASC') ? 'DESC' : 'ASC'; ?>">Email</a></th>
                <th><a href="?orden=especialidades&direccion=<?php echo ($orden_columna == 'especialidades' && $orden_direccion == 'ASC') ? 'DESC' : 'ASC'; ?>">Especialidades</a></th>
                <th><a href="?orden=experiencia&direccion=<?php echo ($orden_columna == 'experiencia' && $orden_direccion == 'ASC') ? 'DESC' : 'ASC'; ?>">Experiencia</a></th>
                <th><a href="?orden=disponibilidad&direccion=<?php echo ($orden_columna == 'disponibilidad' && $orden_direccion == 'ASC') ? 'DESC' : 'ASC'; ?>">Disponibilidad</a></th>
                <th>Acciones</th>
            </tr>
            <?php foreach ($monitores as $monitor): ?>
                <tr>
                    <td><?php echo htmlspecialchars($monitor['nombre']); ?></td>
                    <td><?php echo htmlspecialchars($monitor['email']); ?></td>
                    <td><?php echo htmlspecialchars($monitor['especialidades']); ?></td>
                    <td><?php echo htmlspecialchars($monitor['experiencia']); ?></td>
                    <td><?php echo htmlspecialchars($monitor['disponibilidad']); ?></td>
                    <td class="acciones">
                        <div class="button-container">
                            <!-- Acción de eliminar -->
                            <form action="monitores.php" method="POST" style="display:inline;">
                                <input type="hidden" name="id_usuario" value="<?php echo $monitor['id_usuario']; ?>">
                                <button type="submit" name="eliminar_usuario" onclick="return confirm('¿Estás seguro de que deseas eliminar este monitor? Esta acción no se puede deshacer.')" title="Eliminar definitivamente este monitor">Eliminar</button>
                            </form>
                            <!-- Acción de editar -->
                            <form action="edit_monitor.php" method="GET" style="display:inline;">
                                <input type="hidden" name="id_usuario" value="<?php echo $monitor['id_usuario']; ?>">
                                <button type="submit" name="editar_usuario" title="Modificar el perfil de este monitor">Modificar Perfil</button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>

        <?php
        include 'includes/footer.php';
        $conn->close();
        ?>
    </main>
</body>

</html>