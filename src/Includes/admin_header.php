<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?></title>
    <link rel="stylesheet" href="../assets/css/estilos.css"> <!-- Enlace al archivo CSS -->
    <script src="../assets/js/validacion.js"></script>
</head>

<body>
    <header>
        <h1>Panel de Administración</h1>
        <nav id="navegacion-rapida">
            <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin'): ?>
                <a href="admin.php">Panel Principal</a>
                <a href="usuarios.php">Usuarios</a>
                <a href="monitores.php">Monitores</a>
                <a href="clases.php">Clases</a>
                <a href="membresias.php">Membresías</a>
                <a href="configuracion.php">Administración</a>
            <?php endif; ?>

            <!-- Formulario para cerrar sesión como enlace -->
            <form action="includes/general.php" method="post" style="display: inline;">
                <input type="hidden" name="accion" value="logout">
                <button type="submit" class="logout-link">Cerrar Sesión</button>
            </form>
        </nav>
    </header>

</body>

</html>