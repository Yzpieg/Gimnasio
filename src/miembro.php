<?php
$title = "Acceso Miembros";
include 'includes/miembro_header.php';
require_once 'includes/member_functions.php';

$nombre = $_SESSION['nombre'];
$id_usuario = $_SESSION['id_usuario'];

// Llama a la función para obtener la información del miembro
$miembro = obtenerInformacionMiembro($id_usuario);

if (!$miembro) {
    echo "No se encontró información para este miembro.";
    exit;
}
?>

<!-- Contenedor principal con clase form_container -->
<main class="form_container">
    <h1>Información del Miembro</h1>
    <h2>Bienvenido, <?php echo htmlspecialchars($nombre); ?>!</h2>

    <!-- Tabla de información -->
    <table>
        <tr>
            <th>Nombre de Usuario:</th>
            <td><?php echo htmlspecialchars($miembro['nombre_usuario']); ?></td>
        </tr>
        <tr>
            <th>Email:</th>
            <td><?php echo htmlspecialchars($miembro['email']); ?></td>
        </tr>
        <tr>
            <th>Teléfono:</th>
            <td><?php echo htmlspecialchars($miembro['telefono']); ?></td>
        </tr>
        <tr>
            <th>Fecha de Creación:</th>
            <td><?php echo htmlspecialchars($miembro['fecha_creacion']); ?></td>
        </tr>
        <tr>
            <th>Fecha de Registro como Miembro:</th>
            <td><?php echo htmlspecialchars($miembro['fecha_registro']); ?></td>
        </tr>
    </table>

    <!-- Botón para editar perfil -->
    <div class="button-container">
        <a href="editar_perfil.php" class="btn-general">Editar Perfil</a>
    </div>
</main>

<?php include 'includes/footer.php'; ?>