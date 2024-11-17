<?php

require_once('includes/admin_functions.php');
verificarAdmin();

$conn = obtenerConexion();

$title = "Panel de Administrador";
include 'includes/admin_header.php';

// Consultas para obtener datos del panel de administrador
$num_miembros = obtenerConteoMiembros($conn);
$num_clases = obtenerConteoClases($conn);
$num_monitores = obtenerConteoMonitores($conn);
$notificaciones = obtenerNotificaciones($conn);
?>

<body>
    <main id="admin-panel">
        <section id="bienvenida">
            <h2>Resumen del Gimnasio</h2>
            <p>Miembros registrados: <?php echo $num_miembros; ?></p>
            <p>Clases disponibles: <?php echo $num_clases; ?></p>
            <p>Monitores: <?php echo $num_monitores; ?></p>
        </section>

        <section id="notificaciones">
            <h2>Notificaciones</h2>
            <?php if ($notificaciones && $notificaciones->num_rows > 0): ?>
                <ul>
                    <?php while ($notificacion = $notificaciones->fetch_assoc()): ?>
                        <li><?php echo htmlspecialchars($notificacion['mensaje']); ?> - <em><?php echo $notificacion['fecha']; ?></em></li>
                    <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <p>No hay notificaciones pendientes.</p>
            <?php endif; ?>
        </section>
    </main>

    <?php
    // Cerrar conexión a la base de datos
    $conn->close();
    ?>

    <?php include 'includes/footer.php'; ?>
</body>

</html>