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
// Obtener la membresía activa y sus fechas para el miembro
$id_miembro = obtenerIdMiembroPorUsuario($conn, $id_usuario);
$fechas_membresia = obtenerFechasMembresiaActiva($conn, $id_miembro);

$fecha_inicio = $fechas_membresia['fecha_inicio'] ?? null;
$fecha_fin = $fechas_membresia['fecha_fin'] ?? null;


// Asegurarse de que el array 'entrenamientos' esté definido aunque esté vacío
$miembro['entrenamientos'] = $miembro['entrenamientos'] ?? [];

// Obtener entrenamientos y membresías para los desplegables
$entrenamientos = obtenerEntrenamientos($conn);
$membresias = obtenerMembresias($conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'] ?? null;
    $email = $_POST['email'] ?? null;
    $fecha_registro = $_POST['fecha_registro'] ?? null;
    $id_membresia_nueva = $_POST['id_membresia'] ?? null;
    $entrenamientos_seleccionados = $_POST['entrenamiento'] ?? [];
    $fecha_inicio_nueva = $_POST['fecha_inicio'] ?? null;
    $fecha_fin_nueva = $_POST['fecha_fin'] ?? null;

    if (!$nombre || !$email || !$fecha_registro || !$id_membresia_nueva) {
        $mensaje = "Error: Todos los campos son obligatorios.";
        header("Location: edit_miembro.php?id_usuario=$id_usuario&mensaje=" . urlencode($mensaje));
        exit();
    }

    // Actualizar el miembro en la base de datos con la nueva membresía seleccionada
    $resultado = actualizarMiembro($conn, $id_usuario, $nombre, $email, $fecha_registro, $id_membresia_nueva);

    if ($resultado['success']) {
        $id_miembro = obtenerIdMiembroPorUsuario($conn, $id_usuario);
        if ($id_miembro) {
            actualizarEntrenamientosMiembro($conn, $id_miembro, $entrenamientos_seleccionados);

            // Comprobar si el ID de membresía ha cambiado
            if ($miembro['id_membresia'] !== $id_membresia_nueva) {
                $stmt = $conn->prepare("SELECT precio, duracion FROM membresia WHERE id_membresia = ?");
                $stmt->bind_param("i", $id_membresia_nueva);
                $stmt->execute();
                $stmt->bind_result($precio, $duracion);

                if ($stmt->fetch()) {
                    $fecha_inicio = date("Y-m-d");
                    $fecha_fin = date("Y-m-d", strtotime("+$duracion months"));
                    $stmt->close();

                    // Insertar en miembro_membresía
                    $insert_stmt = $conn->prepare("INSERT INTO miembro_membresia (id_miembro, id_membresia, monto_pagado, fecha_inicio, fecha_fin, estado) VALUES (?, ?, ?, ?, ?, 'activa')");
                    $insert_stmt->bind_param("iisss", $id_miembro, $id_membresia_nueva, $precio, $fecha_inicio, $fecha_fin);

                    if ($insert_stmt->execute()) {
                        $mensaje = "Miembro actualizado correctamente y nueva membresía registrada.";
                    } else {
                        $mensaje = "Miembro actualizado, pero hubo un error al registrar la membresía: " . $insert_stmt->error;
                    }
                    $insert_stmt->close();
                } else {
                    $stmt->close();
                    $mensaje = "Error: No se pudo encontrar la información de la membresía seleccionada.";
                }
            } else {
                $mensaje = "Miembro actualizado correctamente.";
            }

            // Actualizar las fechas de inicio y fin de la membresía activa si fueron modificadas
            if ($fecha_inicio_nueva && $fecha_fin_nueva) {
                $query_update_fechas = "UPDATE miembro_membresia SET fecha_inicio = ?, fecha_fin = ? WHERE id_miembro = ? AND estado = 'activa'";
                $stmt_update_fechas = $conn->prepare($query_update_fechas);
                $stmt_update_fechas->bind_param("ssi", $fecha_inicio_nueva, $fecha_fin_nueva, $id_miembro);

                if ($stmt_update_fechas->execute()) {
                    $mensaje .= " Fechas de la membresía actualizadas correctamente.";
                } else {
                    $mensaje .= " Error al actualizar las fechas de la membresía: " . $stmt_update_fechas->error;
                }
                $stmt_update_fechas->close();
            }

            // Recargar las fechas de inicio y fin actualizadas
            $fechas_membresia = obtenerFechasMembresiaActiva($conn, $id_miembro);
            $fecha_inicio = $fechas_membresia['fecha_inicio'] ?? null;
            $fecha_fin = $fechas_membresia['fecha_fin'] ?? null;

            $miembro = obtenerMiembroPorID($conn, $id_usuario);
        } else {
            $mensaje = "Error: Miembro no encontrado.";
        }
    } else {
        $mensaje = $resultado['message'];
    }
}



include 'includes/admin_header.php';
?>



<body>
    <main>
        <h2>Editar Miembro</h2>

        <?php if (isset($mensaje)): ?>
            <div class="mensaje-confirmacion">
                <p><?php echo htmlspecialchars($mensaje); ?></p>
            </div>
        <?php endif; ?>


        <div class="form_container">
            <?php if ($miembro): ?>
                <form method="POST" action="edit_miembro.php?id_usuario=<?php echo htmlspecialchars($id_usuario); ?>" onsubmit="habilitarFechaRegistro(); return validarFormularioEdicion('miembro');">

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
                                <?php echo (isset($miembro['id_membresia']) && $membresia['id_membresia'] == $miembro['id_membresia']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($membresia['tipo']); ?>
                                - <?php echo "$" . htmlspecialchars($membresia['precio']); ?>
                                (<?php echo htmlspecialchars($membresia['duracion']) . " meses"; ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <!-- Campo para editar la fecha de inicio de la membresía -->
                    <label for="fecha_inicio">Fecha de Inicio de la Membresía:</label>
                    <input type="date" id="fecha_inicio" name="fecha_inicio" value="<?php echo htmlspecialchars($fecha_inicio); ?>" required aria-label="Fecha de inicio de la membresía">

                    <!-- Campo para editar la fecha de fin de la membresía -->
                    <label for="fecha_fin">Fecha de Fin de la Membresía:</label>
                    <input type="date" id="fecha_fin" name="fecha_fin" value="<?php echo htmlspecialchars($fecha_fin); ?>" required aria-label="Fecha de fin de la membresía">

                    <!-- Campo para seleccionar múltiples entrenamientos con checkboxes -->
                    <label>Entrenamientos:</label>
                    <div class="entrenamientos-checkboxes">
                        <?php foreach ($entrenamientos as $entrenamiento): ?>
                            <div class="entrenamiento-item">
                                <label for="entrenamiento_<?php echo $entrenamiento['id_especialidad']; ?>">
                                    <?php echo htmlspecialchars($entrenamiento['nombre']); ?>
                                </label>
                                <input
                                    type="checkbox"
                                    id="entrenamiento_<?php echo $entrenamiento['id_especialidad']; ?>"
                                    name="entrenamiento[]"
                                    value="<?php echo $entrenamiento['id_especialidad']; ?>"
                                    <?php echo in_array($entrenamiento['id_especialidad'], $miembro['entrenamientos']) ? 'checked' : ''; ?>>
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
    <script src="../assets/js/validacion.js"></script>
</body>