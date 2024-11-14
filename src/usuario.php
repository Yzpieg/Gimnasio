<?php
session_start();
require_once('includes/user_functions.php');

$conn = obtenerConexion();

// Verificar si el usuario ha iniciado sesión, de lo contrario redirigir al inicio
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../index.php?error=Debes+iniciar+sesión+primero");
    exit();
}

$id_usuario = $_SESSION['id_usuario'];

// Obtener datos actuales del usuario
$datos_usuario = obtenerDatosUsuario($conn, $id_usuario);

// Procesar la actualización de datos cuando el formulario se envía (método POST)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nuevo_nombre = $_POST['nombre'];
    $nuevo_telefono = $_POST['telefono'];
    $nueva_contrasenya = $_POST['contrasenya'] ?: null;

    // Llamada a actualizarDatosUsuario con la página actual como parámetro de redirección
    actualizarDatosUsuario($conn, $id_usuario, $nuevo_nombre, $nuevo_telefono, $nueva_contrasenya, "usuario.php");
}

// Consulta para obtener las membresías con sus entrenamientos asociados
$query = "
    SELECT m.id_membresia, m.tipo, m.precio, m.duracion, m.beneficios, e.nombre AS entrenamiento
    FROM membresia m
    LEFT JOIN membresia_entrenamiento me ON m.id_membresia = me.id_membresia
    LEFT JOIN especialidad e ON me.id_entrenamiento = e.id_especialidad
    ORDER BY m.id_membresia
";

$result = $conn->query($query);
$membresias = [];

// Organizar los entrenamientos por membresía en un arreglo
while ($row = $result->fetch_assoc()) {
    $membresia_id = $row['id_membresia'];
    if (!isset($membresias[$membresia_id])) {
        $membresias[$membresia_id] = [
            'tipo' => $row['tipo'],
            'precio' => $row['precio'],
            'duracion' => $row['duracion'],
            'beneficios' => $row['beneficios'],
            'entrenamientos' => []
        ];
    }
    if ($row['entrenamiento']) {
        $membresias[$membresia_id]['entrenamientos'][] = $row['entrenamiento'];
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil del Usuario</title>
    <link rel="stylesheet" href="../assets/css/estilos.css">
</head>

<body>
    <h2>Perfil del Usuario</h2>

    <?php if (isset($_GET['mensaje'])): ?>
        <div class="mensaje-confirmacion">
            <p><?php echo htmlspecialchars($_GET['mensaje']); ?></p>
        </div>
    <?php endif; ?>

    <div class="form_container">
        <form action="usuario.php" method="POST" onsubmit="return valFormUsuario();">
            <label for="nombre">Nombre:</label>
            <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($datos_usuario['nombre']); ?>" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($datos_usuario['email']); ?>" disabled>

            <label for="telefono">Teléfono:</label>
            <input type="text" id="telefono" name="telefono" value="<?php echo htmlspecialchars($datos_usuario['telefono']); ?>" maxlength="9" pattern="\d{9}" title="Debe contener exactamente 9 dígitos numéricos" autocomplete="off">

            <label for="contrasenya">Contraseña (dejar en blanco para no cambiarla):</label>
            <input type="password" id="contrasenya" name="contrasenya" autocomplete="new-password">

            <label for="confirmar_contrasenya">Confirmar Contraseña:</label>
            <input type="password" id="confirmar_contrasenya" name="confirmar_contrasenya" autocomplete="new-password">

            <button type="submit">Actualizar Datos</button>
        </form>
    </div>

    <h1>Elige tu Membresía</h1>
    <div class="form_container">
        <?php foreach ($membresias as $id => $membresia): ?>
            <div class="membresia-card">
                <h2><?php echo htmlspecialchars($membresia['tipo']); ?></h2>
                <p>Precio: <?php echo htmlspecialchars($membresia['precio']); ?> €</p>
                <p>Duración: <?php echo htmlspecialchars($membresia['duracion']); ?> mes(es)</p>
                <p>Beneficios: <?php echo htmlspecialchars($membresia['beneficios']); ?></p>

                <h3>Entrenamientos Incluidos:</h3>
                <ul>
                    <?php if (!empty($membresia['entrenamientos'])): ?>
                        <?php foreach ($membresia['entrenamientos'] as $entrenamiento): ?>
                            <li><?php echo htmlspecialchars($entrenamiento); ?></li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li>No incluye entrenamientos específicos.</li>
                    <?php endif; ?>
                </ul>

                <form action="proceso_pago.php" method="POST">
                    <input type="hidden" name="id_membresia" value="<?php echo $id; ?>">
                    <label for="metodo_pago">Método de Pago:</label>
                    <select name="metodo_pago" id="metodo_pago" required>
                        <option value="tarjeta">Tarjeta</option>
                        <option value="efectivo">Efectivo</option>
                        <option value="transferencia">Transferencia</option>
                        <option value="Paypal">Paypal</option>
                        <option value="Bizum">Bizum</option>
                    </select>
                    <button type="submit">Elegir Membresía</button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="form_container">
        <form action="includes/general.php" method="post">
            <input type="hidden" name="accion" value="logout">
            <button type="submit">Cerrar Sesión</button>
        </form>
    </div>

    <script src="../assets/js/validacion.js"></script>
</body>

</html>