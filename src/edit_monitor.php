<?php
require_once('includes/monitor_functions.php');
require_once('includes/user_functions.php'); // Para verificar si es admin

verificarAdmin();

$conn = obtenerConexion();
$title = "Editar Monitor";

if (!isset($_GET['id_usuario'])) {
    die("ID de usuario no proporcionado.");
}

$id_usuario = $_GET['id_usuario'];
$monitor = obtenerMonitorPorID($conn, $id_usuario);

// Asegurarse de que el array 'entrenamientos' esté definido aunque esté vacío
$monitor['entrenamientos'] = $monitor['entrenamientos'] ?? [];

// Obtener entrenamientos y especialidades disponibles para los desplegables
$entrenamientos = obtenerEntrenamientos($conn);
$especialidades = obtenerEspecialidades($conn); // Esta función obtiene todas las especialidades

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'] ?? null;
    $email = $_POST['email'] ?? null;
    $experiencia = $_POST['experiencia'] ?? null;
    $disponibilidad = $_POST['disponibilidad'] ?? null;
    $entrenamientos_seleccionados = $_POST['entrenamiento'] ?? [];

    if (!$nombre || !$email || $experiencia === null || $disponibilidad === null) {
        $mensaje = "Error: Todos los campos son obligatorios.";
        header("Location: edit_monitor.php?id_usuario=$id_usuario&mensaje=" . urlencode($mensaje));
        exit();
    }


    // Actualizar el monitor en la base de datos
    $resultado = actualizarMonitor($conn, $id_usuario, $nombre, $email, $monitor['especialidad'], $experiencia, $disponibilidad);

    if ($resultado['success']) {
        // Obtener el id_monitor asociado al id_usuario
        $id_monitor = $monitor['id_monitor']; // Asegúrate de que este valor esté disponible en el array de datos del monitor

        if ($id_monitor) {
            actualizarEntrenamientosMonitor($conn, $id_monitor, $entrenamientos_seleccionados);
            $mensaje = "Monitor actualizado correctamente.";
        } else {
            $mensaje = "Error: Monitor no encontrado.";
        }

        // Volver a cargar los datos del monitor después de actualizar
        $monitor = obtenerMonitorPorID($conn, $id_usuario);
    } else {
        $mensaje = $resultado['message'];
    }
}

include 'includes/admin_header.php';
?>

<body>
    <main>
        <h2>Editar Monitor</h2>

        <?php if (isset($mensaje)): ?>
            <div class="mensaje-confirmacion">
                <p><?php echo htmlspecialchars($mensaje); ?></p>
            </div>
        <?php endif; ?>

        <div class="form_container">
            <?php if ($monitor): ?>
                <form method="POST" action="edit_monitor.php?id_usuario=<?php echo htmlspecialchars($id_usuario); ?>" onsubmit="return validarFormularioEdicion('monitor');">


                    <!-- Campo para editar el nombre -->
                    <label for="nombre">Nombre:</label>
                    <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($monitor['nombre']); ?>" required>

                    <!-- Campo para editar el email -->
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($monitor['email']); ?>" required>

                    <!-- Apartado para mostrar las especialidades del monitor -->
                    <label>Especialidades:</label>
                    <div class="especialidades-lista">
                        <?php if (!empty($monitor['especialidades'])): ?>
                            <ul>
                                <?php foreach ($monitor['especialidades'] as $especialidad): ?>
                                    <li><?php echo htmlspecialchars($especialidad['nombre']); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p>No hay especialidades asignadas.</p>
                        <?php endif; ?>
                    </div>


                    <!-- Campo para editar la experiencia -->
                    <label for="experiencia">Experiencia (años):</label>
                    <input type="number" id="experiencia" name="experiencia" value="<?php echo htmlspecialchars($monitor['experiencia']); ?>" required min="0">

                    <!-- Campo para editar la disponibilidad -->
                    <label for="disponibilidad">Disponibilidad:</label>
                    <select id="disponibilidad" name="disponibilidad" required>
                        <option value="disponible" <?php echo ($monitor['disponibilidad'] === 'disponible') ? 'selected' : ''; ?>>Disponible</option>
                        <option value="no disponible" <?php echo ($monitor['disponibilidad'] === 'no disponible') ? 'selected' : ''; ?>>No Disponible</option>
                    </select>

                    <!-- Campo para seleccionar múltiples entrenamientos con checkboxes -->
                    <label>Asignar especialidad:</label>
                    <div class="entrenamientos-checkboxes">
                        <?php foreach ($entrenamientos as $entrenamiento): ?>
                            <div class="entrenamiento-item">
                                <label for="entrenamiento_<?php echo htmlspecialchars($entrenamiento['id_especialidad']); ?>">
                                    <?php echo htmlspecialchars($entrenamiento['nombre']); ?>
                                </label>
                                <input
                                    type="checkbox"
                                    id="entrenamiento_<?php echo htmlspecialchars($entrenamiento['id_especialidad']); ?>"
                                    name="entrenamiento[]"
                                    value="<?php echo htmlspecialchars($entrenamiento['id_especialidad']); ?>"
                                    <?php echo isset($monitor['especialidades']) && in_array($entrenamiento['id_especialidad'], array_column($monitor['especialidades'], 'id_especialidad')) ? 'checked' : ''; ?>>
                            </div>
                        <?php endforeach; ?>
                    </div>



                    <!-- Botón para guardar los cambios -->
                    <button type="submit">Guardar Cambios</button>
                </form>
            <?php else: ?>
                <p>Monitor no encontrado.</p>
            <?php endif; ?>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
    <script src="../assets/js/validacion.js"></script>

</body>

</html>