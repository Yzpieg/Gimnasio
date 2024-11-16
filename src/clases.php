<?php
require_once 'includes/class_functions.php';

$conn = obtenerConexion();

// Obtener todas las clases para mostrarlas
$clases = obtenerClases($conn);

$title = "Listado de Clases";
include 'includes/admin_header.php';
?>

<head>
    <link rel="stylesheet" href="../assets/css/estilos_clases.css">
</head>

<body>
    <main>
        <h1>Clases Existentes</h1>

        <!-- Botón para crear clase -->
        <div class="button-container">
            <a href="crear_clase.php" class="button">Crear Clase</a>
        </div>

        <!-- Tabla para mostrar clases -->
        <section class="form_container">
            <table id="tabla-clases" class="styled-table">
                <thead>
                    <tr>
                        <th onclick="ordenarTabla(0)" class="sortable">Nombre</th>
                        <th onclick="ordenarTabla(1)" class="sortable">Especialidad</th>
                        <th onclick="ordenarTabla(2)" class="sortable">Monitor</th>
                        <th onclick="ordenarTabla(3)" class="sortable">Fecha</th>
                        <th onclick="ordenarTabla(4)" class="sortable">Horario</th>
                        <th onclick="ordenarTabla(5)" class="sortable">Duración</th>
                        <th onclick="ordenarTabla(6)" class="sortable">Capacidad</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clases as $clase): ?>
                        <tr>
                            <td><?= htmlspecialchars($clase['nombre']); ?></td>
                            <td><?= htmlspecialchars($clase['especialidad']); ?></td>
                            <td><?= htmlspecialchars($clase['monitor']); ?></td>
                            <td><?= htmlspecialchars($clase['fecha']); ?></td>
                            <td><?= htmlspecialchars($clase['horario']); ?></td>
                            <td><?= htmlspecialchars($clase['duracion']); ?> min</td>
                            <td><?= htmlspecialchars($clase['capacidad_maxima']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </main>
    <?php include 'includes/footer.php'; ?>

    <!-- Incluir el archivo de JavaScript externo -->
    <script src="../assets/js/clases.js"></script>
</body>