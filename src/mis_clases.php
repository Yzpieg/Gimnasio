<?php
$title = "Mis Clases";
include 'includes/miembro_header.php';
require_once 'includes/mi_clase_functions.php';

$conn = obtenerConexion();
$id_usuario = $_SESSION['id_usuario'];

try {
    $id_miembro = obtenerIdMiembro($conn, $id_usuario);

    // Obtener las clases a las que el miembro está inscrito
    $sqlClasesInscritas = "
        SELECT c.id_clase, c.nombre
        FROM asistencia a
        INNER JOIN clase c ON a.id_clase = c.id_clase
        WHERE a.id_miembro = ?
    ";
    $stmtClasesInscritas = $conn->prepare($sqlClasesInscritas);
    $stmtClasesInscritas->bind_param("i", $id_miembro);
    $stmtClasesInscritas->execute();
    $resultadoClasesInscritas = $stmtClasesInscritas->get_result();

    // Procesar formulario para apuntarse o borrarse
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $id_clase = $_POST['id_clase'];
        $accion = $_POST['accion']; // 'apuntarse' o 'borrarse'

        if ($accion === 'apuntarse') {
            $mensaje = apuntarseClase($conn, $id_clase, $id_miembro);
        } elseif ($accion === 'borrarse') {
            $mensaje = borrarseClase($conn, $id_clase, $id_miembro);
        }

        header("Location: mis_clases.php?mensaje=$mensaje");
        exit;
    }

    // Obtener las especialidades del miembro
    $sqlEspecialidades = "
        SELECT e.id_especialidad, e.nombre 
        FROM miembro_entrenamiento me
        INNER JOIN especialidad e ON me.id_especialidad = e.id_especialidad
        WHERE me.id_miembro = ?
    ";
    $stmtEspecialidades = $conn->prepare($sqlEspecialidades);
    $stmtEspecialidades->bind_param("i", $id_miembro);
    $stmtEspecialidades->execute();
    $resultadoEspecialidades = $stmtEspecialidades->get_result();

    $especialidades = [];
    while ($row = $resultadoEspecialidades->fetch_assoc()) {
        $especialidades[] = $row['id_especialidad'];
    }

    // Obtener las clases que coincidan con las especialidades del miembro
    if (!empty($especialidades)) {
        $especialidadesStr = implode(',', $especialidades);
        $sqlClases = "
            SELECT 
                c.id_clase, 
                c.nombre, 
                c.fecha, 
                c.horario, 
                c.duracion, 
                c.capacidad_maxima, 
                e.nombre AS especialidad,
                CASE 
                    WHEN a.id_miembro IS NOT NULL THEN 1 
                    ELSE 0 
                END AS inscrito
            FROM clase c
            INNER JOIN especialidad e ON c.id_especialidad = e.id_especialidad
            LEFT JOIN asistencia a ON c.id_clase = a.id_clase AND a.id_miembro = ?
            WHERE c.id_especialidad IN ($especialidadesStr)
            ORDER BY c.fecha, c.horario
        ";
        $stmtClases = $conn->prepare($sqlClases);
        $stmtClases->bind_param("i", $id_miembro);
        $stmtClases->execute();
        $resultadoClases = $stmtClases->get_result();
    } else {
        $resultadoClases = false;
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
    exit;
}
?>


<main class="form_container">
    <h1>Mis Clases</h1>

    <?php if (isset($_GET['mensaje'])): ?>
        <p style="color: green;">
            <?php if ($_GET['mensaje'] === 'apuntado'): ?>
                ¡Te has inscrito correctamente en la clase!
            <?php elseif ($_GET['mensaje'] === 'ya_inscrito'): ?>
                Ya estás inscrito en esta clase.
            <?php elseif ($_GET['mensaje'] === 'borrado'): ?>
                Te has dado de baja de la clase correctamente.
            <?php elseif ($_GET['mensaje'] === 'no_borrado'): ?>
                No se pudo borrar tu inscripción. Inténtalo de nuevo.
            <?php endif; ?>
        </p>
    <?php endif; ?>
    <?php if ($resultadoClasesInscritas && $resultadoClasesInscritas->num_rows > 0): ?>
        <h2>Clases Inscritas</h2>
        <table>
            <thead>
                <tr>
                    <th>Nombre de la Clase</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($claseInscrita = $resultadoClasesInscritas->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($claseInscrita['nombre']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No estás inscrito en ninguna clase.</p>
    <?php endif; ?>


    <?php if ($resultadoClases && $resultadoClases->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Nombre de la Clase</th>
                    <th>Especialidad</th>
                    <th>Fecha</th>
                    <th>Horario</th>
                    <th>Duración</th>
                    <th>Capacidad Máxima</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($clase = $resultadoClases->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($clase['nombre']); ?></td>
                        <td><?php echo htmlspecialchars($clase['especialidad']); ?></td>
                        <td><?php echo htmlspecialchars($clase['fecha']); ?></td>
                        <td><?php echo htmlspecialchars($clase['horario']); ?></td>
                        <td><?php echo htmlspecialchars($clase['duracion']); ?> minutos</td>
                        <td><?php echo htmlspecialchars($clase['capacidad_maxima']); ?></td>
                        <td>
                            <?php if ($clase['inscrito']): ?>
                                <span style="color: green;">Ya inscrito</span>
                            <?php else: ?>
                                <!-- Botón para apuntarse -->
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="id_clase" value="<?php echo $clase['id_clase']; ?>">
                                    <input type="hidden" name="accion" value="apuntarse">
                                    <button type="submit" class="btn-general">Apuntarme</button>
                                </form>
                            <?php endif; ?>
                            <!-- Botón para borrarse -->
                            <?php if ($clase['inscrito']): ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="id_clase" value="<?php echo $clase['id_clase']; ?>">
                                    <input type="hidden" name="accion" value="borrarse">
                                    <button type="submit" class="btn-general btn-danger">Borrarme</button>
                                </form>
                            <?php endif; ?>
                        </td>

                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No hay clases disponibles para tus especialidades.</p>
    <?php endif; ?>
</main>