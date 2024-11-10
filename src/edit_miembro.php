<?php
require_once('includes/member_functions.php');

verificarAdmin();

$conn = obtenerConexion();
$title = "Editar Miembro";

if (!isset($_GET['id_usuario'])) {
    die("ID de usuario no proporcionado.");
}

$id_usuario = $_GET['id_usuario'];
$miembro = obtenerMiembroPorID($conn, $id_usuario);

// Asegurarse de que el array 'entrenamientos' esté definido aunque esté vacío
$miembro['entrenamientos'] = $miembro['entrenamientos'] ?? [];

// Obtener entrenamientos y membresías para los desplegables
$entrenamientos = obtenerEntrenamientos($conn);
$membresias = obtenerMembresias($conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'] ?? null;
    $email = $_POST['email'] ?? null;
    $fecha_registro = $_POST['fecha_registro'] ?? null;
    $id_membresia = $_POST['id_membresia'] ?? null;
    $entrenamientos_seleccionados = $_POST['entrenamiento'] ?? [];

    if (!$nombre || !$email || !$fecha_registro || !$id_membresia) {
        $mensaje = "Error: Todos los campos son obligatorios.";
        header("Location: edit_miembro.php?id_usuario=$id_usuario&mensaje=" . urlencode($mensaje));
        exit();
    }

    // Actualizar el miembro en la base de datos con la membresía seleccionada
    $resultado = actualizarMiembro($conn, $id_usuario, $nombre, $email, $fecha_registro, $id_membresia);

    if ($resultado['success']) {
        // Obtener el id_miembro usando id_usuario
        $id_miembro = obtenerIdMiembroPorUsuario($conn, $id_usuario);
        if ($id_miembro) {
            // Actualizar entrenamientos solo si el miembro existe
            actualizarEntrenamientosMiembro($conn, $id_miembro, $entrenamientos_seleccionados);
            $mensaje = "Miembro actualizado correctamente.";
        } else {
            $mensaje = "Error: Miembro no encontrado.";
        }
    } else {
        $mensaje = $resultado['message'];
    }

    header("Location: miembros.php?mensaje=" . urlencode($mensaje));
    exit();
}

include 'includes/admin_header.php';
?>


<body>
    <main>
        <h2>Editar Miembro</h2>

        <?php if (isset($_GET['mensaje'])): ?>
            <div class="mensaje-confirmacion">
                <p><?php echo htmlspecialchars($_GET['mensaje']); ?></p>
            </div>
        <?php endif; ?>

        <div class="form_container">
            <?php if ($miembro): ?>
                <form method="POST" action="edit_miembro.php?id_usuario=<?php echo htmlspecialchars($id_usuario); ?>" onsubmit="habilitarFechaRegistro(); return validarFormulario();">

                    <!-- Campo para editar el nombre -->
                    <label for="nombre">Nombre:</label>
                    <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($miembro['nombre']); ?>" required aria-label="Nombre completo del miembro">

                    <!-- Campo para editar el email -->
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($miembro['email']); ?>" required title="Introduce el email del miembro" aria-label="Correo electrónico del miembro">

                    <!-- Campo para editar la fecha de registro -->
                    <label for="fecha_registro">Fecha de Registro:</label>
                    <input type="date" id="fecha_registro" name="fecha_registro" value="<?php echo htmlspecialchars($miembro['fecha_registro']); ?>" required aria-label="Fecha de registro del miembro" title="Introduce la fecha de registro del miembro" disabled>

                    <!-- Checkbox para habilitar la edición de la fecha de registro -->
                    <div style="display: flex; align-items: center; gap: 5px;">
                        <input type="checkbox" id="editar_fecha" onclick="toggleFechaRegistro();">
                        <label for="editar_fecha">Editar Fecha de Registro</label>
                    </div>

                    <!-- Campo para editar el tipo de membresía -->
                    <label for="tipo_membresia">Tipo de Membresía:</label>
                    <select id="tipo_membresia" name="id_membresia" required>
                        <?php foreach ($membresias as $membresia): ?>
                            <option value="<?php echo htmlspecialchars($membresia['id_membresia']); ?>"
                                <?php echo (isset($miembro['id_membresia']) && $membresia['id_membresia'] === $miembro['id_membresia']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($membresia['tipo']); ?>
                                - <?php echo "$" . htmlspecialchars($membresia['precio']); ?>
                                (<?php echo htmlspecialchars($membresia['duracion']) . " meses"; ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <!-- Campo para seleccionar múltiples entrenamientos con checkboxes -->
                    <label>Entrenamientos:</label>
                    <div class="entrenamientos-checkboxes">
                        <?php foreach ($entrenamientos as $entrenamiento): ?>
                            <div>
                                <input
                                    type="checkbox"
                                    id="entrenamiento_<?php echo $entrenamiento['id_especialidad']; ?>"
                                    name="entrenamiento[]"
                                    value="<?php echo $entrenamiento['id_especialidad']; ?>"
                                    <?php echo in_array($entrenamiento['id_especialidad'], $miembro['entrenamientos']) ? 'checked' : ''; ?>>
                                <label for="entrenamiento_<?php echo $entrenamiento['id_especialidad']; ?>">
                                    <?php echo htmlspecialchars($entrenamiento['nombre']); ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Botón para guardar los cambios -->
                    <button type="submit">Guardar Cambios</button>
                </form>
            <?php else: ?>
                <p>Miembro no encontrado.</p>
            <?php endif; ?>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script>
        function toggleFechaRegistro() {
            const fechaRegistroInput = document.getElementById('fecha_registro');
            fechaRegistroInput.disabled = !fechaRegistroInput.disabled;
        }

        function habilitarFechaRegistro() {
            document.getElementById('fecha_registro').disabled = false;
        }
    </script>
</body>