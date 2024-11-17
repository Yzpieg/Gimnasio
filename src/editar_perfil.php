<?php
$title = "Editar Perfil";
include 'includes/miembro_header.php'; // Verificación de sesión y encabezado
require_once 'includes/member_functions.php'; // Funciones necesarias

$conn = obtenerConexion();
$id_usuario = $_SESSION['id_usuario']; // ID del usuario en sesión
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
    <h1>EN CONSTRUCCIÓN</h1>
    <h2>Editar Perfil</h2>
    <form action="includes/procesar_editar_perfil.php" method="POST">
        <!-- Campo oculto para ID de usuario -->
        <input type="hidden" name="id_usuario" value="<?php echo htmlspecialchars($miembro['id_usuario']); ?>">

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