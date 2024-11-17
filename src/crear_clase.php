<?php
require_once 'includes/class_functions.php';
require_once('includes/admin_functions.php');
verificarAdmin();
$conn = obtenerConexion();

// Manejar el formulario de creación de clase
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $id_monitor = intval($_POST['id_monitor']);
    $id_especialidad = intval($_POST['id_especialidad']);
    $fecha = $_POST['fecha'];
    $horario = $_POST['horario'];
    $duracion = $_POST['duracion'];
    $capacidad = $_POST['capacidad'];

    // Validar campos obligatorios
    if (empty($nombre) || empty($id_monitor) || empty($id_especialidad) || empty($fecha) || empty($horario) || empty($duracion) || empty($capacidad)) {
        $error = "Todos los campos son obligatorios.";
    } else {
        // Validar la relación entre monitor y especialidad
        $validacion = $conn->query("
            SELECT 1 
            FROM monitor_especialidad 
            WHERE id_monitor = $id_monitor AND id_especialidad = $id_especialidad
        ");

        if ($validacion->num_rows === 0) {
            $error = "El monitor seleccionado no pertenece a la especialidad elegida.";
        } else {
            // Si pasa la validación, crear la clase
            crearClase($conn, $nombre, $id_monitor, $id_especialidad, $fecha, $horario, $duracion, $capacidad);
            $success = "Clase creada exitosamente.";
        }
    }
}

// Consulta para Monitores
$monitores = $conn->query("
    SELECT mo.id_monitor, u.nombre AS monitor_nombre, 
           GROUP_CONCAT(e.id_especialidad, ':', e.nombre SEPARATOR ',') AS especialidades
    FROM monitor mo
    JOIN usuario u ON mo.id_usuario = u.id_usuario
    LEFT JOIN monitor_especialidad me ON mo.id_monitor = me.id_monitor
    LEFT JOIN especialidad e ON me.id_especialidad = e.id_especialidad
    GROUP BY mo.id_monitor
");

// Consulta para Especialidades
$especialidades = $conn->query("
    SELECT e.id_especialidad, e.nombre AS especialidad_nombre, 
           GROUP_CONCAT(mo.id_monitor, ':', u.nombre, ':', mo.disponibilidad SEPARATOR ',') AS monitores
    FROM especialidad e
    LEFT JOIN monitor_especialidad me ON e.id_especialidad = me.id_especialidad
    LEFT JOIN monitor mo ON me.id_monitor = mo.id_monitor
    LEFT JOIN usuario u ON mo.id_usuario = u.id_usuario
    GROUP BY e.id_especialidad
");

$title = "Crear Nueva Clase";
include 'includes/admin_header.php';
?>

<body>
    <main>
        <h1>Crear Nueva Clase</h1>

        <!-- Mensajes de error o éxito -->
        <?php if (isset($success)): ?>
            <p class="mensaje-confirmacion"><?php echo htmlspecialchars($success); ?></p>
        <?php elseif (isset($error)): ?>
            <p class="mensaje-error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <!-- Formulario para crear clase -->
        <section class="form_container">
            <form method="POST">
                <label for="nombre">Nombre de la Clase:</label>
                <input type="text" id="nombre" name="nombre" required>
                <label for="id_especialidad">Especialidad:</label>
                <select id="id_especialidad" name="id_especialidad" required>
                    <option value="" disabled selected>Seleccionar especialidad</option>
                    <?php while ($especialidad = $especialidades->fetch_assoc()): ?>
                        <option value="<?= $especialidad['id_especialidad']; ?>"
                            data-monitores="<?= $especialidad['monitores']; ?>">
                            <?= htmlspecialchars($especialidad['especialidad_nombre']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>

                <label for="id_monitor">Monitor:</label>
                <select id="id_monitor" name="id_monitor" disabled required>
                    <option value="" disabled selected>Seleccionar monitor</option>
                </select>

                <label for="fecha">Fecha:</label>
                <input type="date" id="fecha" name="fecha" required>

                <label for="horario">Horario:</label>
                <input type="time" id="horario" name="horario" required>

                <label for="duracion">Duración (min):</label>
                <input type="number" id="duracion" name="duracion" required>

                <label for="capacidad">Capacidad Máxima:</label>
                <input type="number" id="capacidad" name="capacidad" required>

                <button type="submit" class="button-container">Crear Clase</button>
            </form>
        </section>
    </main>
    <script src="../assets/js/dinamica_especialidades.js"></script>
    <script>
        configurarMonitoresPorEspecialidad('id_especialidad', 'id_monitor');
    </script>
    <?php include 'includes/footer.php'; ?>
</body>