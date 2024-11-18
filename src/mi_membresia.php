<?php
$title = "Acceso Miembros";
include 'includes/miembro_header.php';
require_once 'includes/member_functions.php';

$nombre = $_SESSION['nombre'];
$id_usuario = $_SESSION['id_usuario'];

// Llama a la función para obtener la información del miembro
$miembro = informacionMembresia($id_usuario);

if (!$miembro) {
    echo "No se encontró información para este miembro.";
    exit;
}
?>

<!-- Contenedor principal con clase form_container -->

<main class="form_container">
    <h1>EN CONSTRUCCIÓN</h1>
    <h2>Bienvenido, <?php echo htmlspecialchars($nombre); ?>!</h2>
    <h3>Información de tu Membresía</h3>

    <!-- Tabla de información usando solo las clases aplicables -->
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
            <th>Fecha de Registro:</th>
            <td><?php echo htmlspecialchars($miembro['fecha_registro']); ?></td>
        </tr>
        <tr>
            <th>Nombre de la Membresía:</th>
            <td><?php echo htmlspecialchars($miembro['nombre_membresia']); ?></td>
        </tr>
        <tr>
            <th>Fecha de Inicio de Membresía:</th>
            <td><?php echo htmlspecialchars($miembro['fecha_inicio']); ?></td>
        </tr>
        <tr>
            <th>Fecha de Fin de Membresía:</th>
            <td><?php echo htmlspecialchars($miembro['fecha_fin']); ?></td>
        </tr>
        <tr>
            <th>Estado de la Membresía:</th>
            <td><?php echo htmlspecialchars($miembro['estado']); ?></td>
        </tr>
        <tr>
            <th>Renovación Automática:</th>
            <td><?php echo $miembro['renovacion_automatica'] ? 'Sí' : 'No'; ?></td>
        </tr>
        <tr>
            <th>Monto del Pago:</th>
            <td><?php echo htmlspecialchars($miembro['monto_pago']); ?></td>
        </tr>
        <tr>
            <th>Fecha de Pago:</th>
            <td><?php echo htmlspecialchars($miembro['fecha_pago']); ?></td>
        </tr>
        <tr>
            <th>Método de Pago:</th>
            <td><?php echo htmlspecialchars($miembro['metodo_pago']); ?></td>
        </tr>
        <tr>
            <th>Entrenamientos/Especialidades:</th>
            <td>
                <?php
                if (!empty($miembro['especialidades'])) {
                    echo htmlspecialchars(implode(", ", $miembro['especialidades']));
                } else {
                    echo "No tiene entrenamientos asignados.";
                }
                ?>
            </td>
        </tr>

    </table>
</main>

<?php include 'includes/footer.php'; ?>