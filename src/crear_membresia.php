<?php
require_once('includes/admin_functions.php');
require_once('includes/member_functions.php');

verificarAdmin();
$conn = obtenerConexion();

// Manejar la inserción, edición o eliminación de membresías
$mensaje = '';
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $entrenamientos_seleccionados = $_POST['entrenamientos'] ?? [];

    if (isset($_POST['nueva_membresia'])) {
        $tipo = trim($_POST['tipo']);
        $precio = floatval($_POST['precio']);
        $duracion = intval($_POST['duracion']);
        $beneficios = trim($_POST['beneficios']);
        $mensaje = agregarMembresia($conn, $tipo, $precio, $duracion, $beneficios, $entrenamientos_seleccionados);
    } elseif (isset($_POST['editar_membresia'])) {
        $id_membresia = $_POST['id_membresia'];
        $tipo = trim($_POST['tipo']);
        $precio = floatval($_POST['precio']);
        $duracion = intval($_POST['duracion']);
        $beneficios = trim($_POST['beneficios']);
        $mensaje = editarMembresia($conn, $id_membresia, $tipo, $precio, $duracion, $beneficios, $entrenamientos_seleccionados);
    } elseif (isset($_POST['eliminar_membresia'])) {
        $id_membresia = $_POST['id_membresia'];
        $mensaje = eliminarMembresia($conn, $id_membresia);
    }
}

// Obtener todas las membresías y entrenamientos para mostrar en la página
$membresias = [];
$result = $conn->query("SELECT * FROM membresia ORDER BY tipo ASC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $id_membresia = $row['id_membresia'];
        $row['entrenamientos'] = [];

        // Cargar entrenamientos asociados a esta membresía
        $stmt = $conn->prepare("SELECT id_entrenamiento FROM membresia_entrenamiento WHERE id_membresia = ?");
        $stmt->bind_param("i", $id_membresia);
        $stmt->execute();
        $entrenamientos_result = $stmt->get_result();

        while ($entrenamiento = $entrenamientos_result->fetch_assoc()) {
            $row['entrenamientos'][] = $entrenamiento['id_entrenamiento'];
        }
        $stmt->close();

        $membresias[] = $row;
    }
}
$entrenamientos = obtenerEntrenamientos($conn); // Obtener entrenamientos disponibles
$title = "Membresías";
include 'includes/admin_header.php';
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Configuración de Membresías</title>
    <link rel="stylesheet" href="estilos.css">
</head>

<body>
    <main class="form_container form_container_large">
        <h2>Administración de Membresías</h2>
        <!-- Mensaje de confirmación o error -->
        <?php if (!empty($mensaje)): ?>
            <div class="<?php echo strpos($mensaje, 'Error') === false ? 'mensaje-confirmacion' : 'mensaje-error'; ?>">
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
        <?php endif; ?>

        <!-- Formulario para añadir una nueva membresía -->
        <form method="POST" action="crear_membresia.php" class="membresia-form">
            <h3>Añadir Nueva Membresía</h3>
            <div class="membresia-form-item">
                <label for="tipo">Tipo:</label>
                <input type="text" id="tipo" name="tipo" required>
            </div>
            <div class="membresia-form-item">
                <label for="precio">Precio:</label>
                <input type="number" id="precio" name="precio" step="0.01" required>
            </div>
            <div class="membresia-form-item">
                <label for="duracion">Duración (meses):</label>
                <input type="number" id="duracion" name="duracion" required>
            </div>
            <div class="membresia-form-item">
                <label for="beneficios">Beneficios:</label>
                <textarea id="beneficios" name="beneficios" rows="3"></textarea>
            </div>

            <!-- Checkboxes para asignar entrenamientos -->
            <div class="membresia-form-item">
                <label>Entrenamientos Disponibles:</label>
                <div class="checkbox-group">
                    <?php foreach ($entrenamientos as $entrenamiento): ?>
                        <label>
                            <input type="checkbox" name="entrenamientos[]" value="<?php echo $entrenamiento['id_especialidad']; ?>">
                            <?php echo htmlspecialchars($entrenamiento['nombre']); ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
            <button type="submit" name="nueva_membresia">Añadir Membresía</button>
        </form>

        <!-- Listado de membresías con opciones de edición y eliminación -->
        <h3>Membresías Disponibles</h3>
        <ul class="membresias-lista">
            <?php foreach ($membresias as $membresia): ?>
                <li>
                    <form method="POST" action="crear_membresia.php" class="membresia-item">
                        <input type="hidden" name="id_membresia" value="<?php echo $membresia['id_membresia']; ?>">

                        <!-- Campos de edición para la membresía -->
                        <label>
                            Tipo:
                            <input type="text" name="tipo" value="<?php echo htmlspecialchars($membresia['tipo']); ?>" required>
                        </label>

                        <label>
                            Precio:
                            <input type="number" name="precio" value="<?php echo htmlspecialchars($membresia['precio']); ?>" step="0.01" required>
                        </label>

                        <label>
                            Duración (meses):
                            <input type="number" name="duracion" value="<?php echo htmlspecialchars($membresia['duracion']); ?>" required>
                        </label>

                        <label>
                            Beneficios:
                            <textarea name="beneficios" rows="1"><?php echo htmlspecialchars($membresia['beneficios']); ?></textarea>
                        </label>

                        <!-- Checkboxes para asignar entrenamientos al editar -->
                        <div class="checkbox-group">
                            <?php foreach ($entrenamientos as $entrenamiento): ?>
                                <label>
                                    <?php echo htmlspecialchars($entrenamiento['nombre']); ?>
                                    <input type="checkbox" name="entrenamientos[]" value="<?php echo $entrenamiento['id_especialidad']; ?>"
                                        <?php echo in_array($entrenamiento['id_especialidad'], $membresia['entrenamientos']) ? 'checked' : ''; ?>>
                                </label>
                            <?php endforeach; ?>
                        </div>


                        <!-- Botones de edición y eliminación -->
                        <div class="membresia-botones">
                            <button type="submit" name="editar_membresia">Editar</button>
                            <button type="submit" name="eliminar_membresia"
                                class="<?php echo ($membresia['id_membresia'] == 1) ? 'btn-disabled' : ''; ?>"
                                onclick="return confirm('¿Estás seguro de que deseas eliminar esta membresía?')"
                                <?php echo ($membresia['id_membresia'] == 1) ? 'disabled title="Esta membresía no se puede eliminar."' : ''; ?>>
                                Eliminar
                            </button>
                        </div>


                    </form>
                </li>
            <?php endforeach; ?>
        </ul>
    </main>
</body>

</html>