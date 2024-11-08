<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gimnasio - Bienvenido</title>
    <link rel="stylesheet" href="/Gimnasio/assets/css/estilos.css">
</head>

<body>
    <?php
    if (isset($_GET['error'])) {
        echo "<p class='mensaje-error'>" . htmlspecialchars($_GET['error']) . "</p>";
    }
    if (isset($_GET['mensaje'])) {
        echo "<p class='mensaje-confirmacion'>" . htmlspecialchars($_GET['mensaje']) . "</p>";
    }
    ?>
    <!-- Imagen de portada del gimnasio -->
    <div class="image-container">
        <img src="assets/imgs/gym.webp" alt="Gimnasio" class="gym-image">
    </div>

    <h2>Bienvenido al Gimnasio</h2>
    <p>Elige una opción para continuar:</p>

    <div class="button-container">
        <!-- Botón para redirigir a la página de registro -->
        <a href="src/reg.php">
            <button>Registrarse</button>
        </a>

        <!-- Botón para redirigir a la página de inicio de sesión -->
        <a href="src/log.php">
            <button>Iniciar Sesión</button>
        </a>
    </div>
</body>

</html>