<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gimnasio - Registro e Inicio de Sesión</title>
    <!-- Enlace al archivo CSS para estilos de la página -->
    <link rel="stylesheet" href="estilos.css">
</head>

<body>
    <!-- Mostrar mensaje de confirmación si existe, recibido como parámetro en la URL -->
    <?php if (isset($_GET['mensaje'])): ?>
        <div class="mensaje-confirmacion">
            <p><?php echo htmlspecialchars($_GET['mensaje']); ?></p>
        </div>
    <?php endif; ?>

    <!-- Mostrar mensaje de error si existe, recibido como parámetro en la URL -->
    <?php if (isset($_GET['error'])): ?>
        <div class="mensaje-error">
            <p><?php echo htmlspecialchars($_GET['error']); ?></p>
        </div>
    <?php endif; ?>

    <!-- Contenedor del formulario de registro de usuario -->
    <div class="form_container">
        <h2>Registro de Usuario</h2>
        <form action="registro.php" method="POST" onsubmit="return validarFormulario()">
            <!-- Campo para el nombre del usuario -->
            <label for="nombre">Nombre:</label>
            <input type="text" id="nombre" name="nombre" required>

            <!-- Campo para el email del usuario -->
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>

            <!-- Campo para la contraseña del usuario -->
            <label for="password">Contraseña:</label>
            <input type="password" id="password" name="password" required>

            <!-- Botón para enviar el formulario de registro -->
            <button type="submit">Registrarse</button>
        </form>
    </div>

    <!-- Contenedor del formulario de inicio de sesión -->
    <div class="form_container">
        <h2>Inicio de Sesión</h2>
        <form action="login.php" method="POST">
            <!-- Campo para el email en el inicio de sesión -->
            <label for="email_login">Email:</label>
            <input type="email" id="email_login" name="email" required>

            <!-- Campo para la contraseña en el inicio de sesión -->
            <label for="password_login">Contraseña:</label>
            <input type="password" id="password_login" name="password" required>

            <!-- Botón para enviar el formulario de inicio de sesión -->
            <button type="submit">Iniciar Sesión</button>
        </form>
    </div>

    <!-- Enlace al archivo JavaScript para validación de formularios -->
    <script src="validacion.js"></script>
</body>

</html>