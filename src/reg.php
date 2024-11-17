<?php
session_start();

if (isset($_SESSION['error'])) {
    echo "<p class='mensaje-error'>{$_SESSION['error']}</p>";
    unset($_SESSION['error']);
}

if (isset($_SESSION['mensaje'])) {
    echo "<p class='mensaje-confirmacion'>{$_SESSION['mensaje']}</p>";
    unset($_SESSION['mensaje']);
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gimnasio - Registro e Inicio de Sesión</title>
    <link rel="stylesheet" href="../assets/css/estilos.css">
</head>

<body>
    <!-- Mensaje de confirmación, mostrado si existe en la URL como parámetro 'mensaje' -->
    <?php if (isset($_GET['mensaje'])): ?>
        <div class="mensaje-confirmacion">
            <p><?php echo htmlspecialchars($_GET['mensaje']); ?></p>
        </div>
    <?php endif; ?>

    <!-- Mensaje de error, mostrado si existe en la URL como parámetro 'error' -->
    <?php if (isset($_GET['error'])): ?>
        <div class="mensaje-error">
            <p><?php echo htmlspecialchars($_GET['error']); ?></p>
        </div>
    <?php endif; ?>

    <!-- Contenedor del formulario de registro de usuario -->
    <div class="form_container">
        <h2>Registro de Usuario</h2>
        <form action="registro.php" method="POST" onsubmit="return validarFormulario()">
            <label for="nombre">Nombre:</label>
            <input type="text" id="nombre" name="nombre" required value="<?php echo isset($_SESSION['form_data']['nombre']) ? htmlspecialchars($_SESSION['form_data']['nombre']) : ''; ?>">

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required value="<?php echo isset($_SESSION['form_data']['email']) ? htmlspecialchars($_SESSION['form_data']['email']) : ''; ?>">

            <label for="contrasenya">Contraseña:</label>
            <input type="password" id="contrasenya" name="contrasenya" required>

            <label for="confirmar_contrasenya">Confirmar Contraseña:</label>
            <input type="password" id="confirmar_contrasenya" name="confirmar_contrasenya" required>

            <button type="submit" class="btn-general">Registrarse</button>
        </form>
    </div>
    <div class="button-container">
        <a href="../index.php" class="btn-general">Volver al inicio</a>
    </div>




    <script src="../assets/js/validacion.js"></script>
</body>

</html>