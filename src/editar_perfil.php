<?php
$title = "Editar Perfil";
include 'includes/miembro_header.php';
require_once 'includes/member_functions.php';

$conn = obtenerConexion();
$id_usuario = $_SESSION['id_usuario'];

// Procesar el formulario si se ha enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $fecha_registro = $_POST['fecha_registro'];
    $id_membresia = $_POST['id_membresia'] ?? null;

    // Llama a la función para actualizar los datos
    $resultado = actualizarMiembro($conn, $id_usuario, $nombre, $email, $fecha_registro, $id_membresia);

    if ($resultado['success']) {
        header('Location: miembro.php?mensaje=perfil_actualizado');
        exit;
    } else {
        $error = "Error: " . $resultado['message'];
    }
}

// Obtener la información del miembro para mostrar en el formulario
$miembro = obtenerMiembroPorID($conn, $id_usuario);

if (!$miembro) {
    echo "No se encontró información para este miembro.";
    exit;
}

// Obtener las membresías disponibles
$membresias = obtenerMembresias($conn);
?>

<!-- Contenedor principal -->
<main class="form_container">
    <h1>Editar Perfil</h1>

    <?php if (isset($error)): ?>
        <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <form action="editar_perfil.php" method="POST">
        <!-- Nombre -->
        <label for="nombre">Nombre Completo:</label>
        <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($miembro['nombre']); ?>" required>

        <!-- Email -->
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($miembro['email']); ?>" required>

        <!-- Fecha de Registro -->
        <label for="fecha_registro">Fecha de Registro:</label>
        <input type="date" id="fecha_registro" name="fecha_registro" value="<?php echo htmlspecialchars($miembro['fecha_registro']); ?>" required>

        <!-- Membresía Actual -->
        <label for="id_membresia">Membresía:</label>
        <select id="id_membresia" name="id_membresia">
            <option value="">Seleccionar...</option>
            <?php foreach ($membresias as $membresia): ?>
                <option value="<?php echo $membresia['id_membresia']; ?>"
                    <?php echo $membresia['id_membresia'] == $miembro['id_membresia'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($membresia['tipo']); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <!-- Botón de envío -->
        <button type="submit" class="btn-general">Guardar Cambios</button>
    </form>
</main>

<?php include 'includes/footer.php'; ?>