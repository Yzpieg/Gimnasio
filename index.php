<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gimnasio - Registro e Inicio de Sesión</title>
    <link rel="stylesheet" href="estilos.css">
</head>

<body>
    <!-- Mostrar mensaje de confirmación si existe -->
    <?php if (isset($_GET['mensaje'])): ?>
        <div class="mensaje-confirmacion">
            <p><?php echo htmlspecialchars($_GET['mensaje']); ?></p>
        </div>
    <?php endif; ?>

    <!-- Mostrar mensaje de error si existe -->
    <?php if (isset($_GET['error'])): ?>
        <div class="mensaje-error">
            <p><?php echo htmlspecialchars($_GET['error']); ?></p>
        </div>
    <?php endif; ?>

    <div class="form_container">
        <h2>Registro de Usuario</h2>
        <form action="registro.php" method="POST" onsubmit="return validarFormulario()">
            <label for="nombre">Nombre:</label>
            <input type="text" id="nombre" name="nombre" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>

            <label for="password">Contraseña:</label>
            <input type="password" id="password" name="password" required>

            <button type="submit">Registrarse</button>
        </form>
    </div>

    <div class="form_container">
        <h2>Inicio de Sesión</h2>
        <form action="login.php" method="POST">
            <label for="email_login">Email:</label>
            <input type="email" id="email_login" name="email" required>

            <label for="password_login">Contraseña:</label>
            <input type="password" id="password_login" name="password" required>

            <button type="submit">Iniciar Sesión</button>
        </form>
    </div>

    <script src="validacion.js"></script>
</body>

</html>