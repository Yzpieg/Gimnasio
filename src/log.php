<?php
session_start();
require_once('includes/general.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $contrasenya = $_POST['contrasenya'];
    iniciarSesionUsuario($email, $contrasenya);
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gimnasio - Registro e Inicio de Sesión</title>
    <link rel="stylesheet" href="/Gimnasio/assets/css/estilos.css">
</head>

<body>
    <?php if (isset($_GET['mensaje'])): ?>
        <div class="mensaje-confirmacion">
            <p><?php echo htmlspecialchars($_GET['mensaje']); ?></p>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error']) || isset($_SESSION['error'])): ?>
        <div class="mensaje-error">
            <p><?php echo isset($_GET['error']) ? htmlspecialchars($_GET['error']) : htmlspecialchars($_SESSION['error']); ?></p>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <div class="form_container">
        <h2>Inicio de Sesión</h2>
        <form action="log.php" method="POST">
            <label for="email_login">Email:</label>
            <input type="email" id="email_login" name="email" required value="<?php echo isset($_SESSION['form_data']['email']) ? htmlspecialchars($_SESSION['form_data']['email']) : ''; ?>">

            <label for="contrasenya_login">Contraseña:</label>
            <input type="password" id="contrasenya_login" name="contrasenya" required>

            <button type="submit">Iniciar Sesión</button>
        </form>
    </div>

    <div class="button-container">
        <button onclick="window.location.href='../index.php'">Volver a la Página Principal</button>
    </div>

    <script src="../assets/js/validacion.js"></script>
</body>

</html>