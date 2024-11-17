<?php
require_once('includes/admin_functions.php');
verificarAdmin();
$conn = obtenerConexion();

// Consulta para obtener membresías con información del miembro y fechas
$sql = "
    SELECT 
        mm.id_miembro,
        u.nombre AS nombre_usuario,
        u.email,
        u.telefono,
        m.tipo AS tipo_membresia,
        m.precio,
        m.duracion,
        mm.fecha_inicio,
        mm.fecha_fin,
        mm.estado,
        mm.renovacion_automatica
    FROM 
        miembro_membresia mm
    INNER JOIN miembro mb ON mm.id_miembro = mb.id_miembro
    INNER JOIN usuario u ON mb.id_usuario = u.id_usuario
    INNER JOIN membresia m ON mm.id_membresia = m.id_membresia
    ORDER BY mm.fecha_inicio DESC
";
$result = $conn->query($sql);
$membresias_miembros = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $membresias_miembros[] = $row;
    }
}
$title = "Membresías y Miembros";
include 'includes/admin_header.php';
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Detalles de Membresías</title>
    <link rel="stylesheet" href="estilos.css">
</head>

<body>
    <main class="form_container">
        <h2>Detalles de Membresías por Miembro</h2>
        <div class="form_container">
            <a href="crear_membresia.php" class="btn-general">Crear Nueva Membresía</a>
        </div>
        <?php if (!empty($membresias_miembros)): ?>
            <table class="tabla">
                <thead>
                    <tr>
                        <th>Nombre Miembro</th>
                        <th>Email</th>
                        <th>Teléfono</th>
                        <th>Membresía</th>
                        <th>Precio</th>
                        <th>Duración</th>
                        <th>Fecha Inicio</th>
                        <th>Fecha Fin</th>
                        <th>Estado</th>
                        <th>Renovación Automática</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($membresias_miembros as $dato): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($dato['nombre_usuario']); ?></td>
                            <td><?php echo htmlspecialchars($dato['email']); ?></td>
                            <td><?php echo htmlspecialchars($dato['telefono'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($dato['tipo_membresia']); ?></td>
                            <td><?php echo htmlspecialchars($dato['precio']); ?> €</td>
                            <td><?php echo htmlspecialchars($dato['duracion']); ?> meses</td>
                            <td><?php echo htmlspecialchars($dato['fecha_inicio']); ?></td>
                            <td><?php echo htmlspecialchars($dato['fecha_fin']); ?></td>
                            <td><?php echo htmlspecialchars($dato['estado']); ?></td>
                            <td><?php echo $dato['renovacion_automatica'] ? 'Sí' : 'No'; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No hay membresías registradas para mostrar.</p>
        <?php endif; ?>
    </main>
</body>

</html>