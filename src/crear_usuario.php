<?php

require_once('includes/user_functions.php');

verificarAdmin();

$conn = obtenerConexion();

$title = "crear usuario";
include 'includes/admin_header.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['crear_usuario'])) {
    // Capturar los datos del formulario
    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $contrasenya = $_POST['contrasenya'];
    $confirmar_contrasenya = $_POST['confirmar_contrasenya'];
    $rol = $_POST['rol'];

    // Llamar a la función para crear el usuario
    crearFormUsuario($conn, $nombre, $email, $contrasenya, $confirmar_contrasenya, $rol);
}

?>

<body>



    <main>
        <!-- Formulario para crear un nuevo usuario -->
        <section class="form_container">
            <h3>Agregar Usuario Manualmente</h3>
            <form action="crear_usuario.php" method="POST" onsubmit="return validarFormulario()">
                <label for="nombre">Nombre:</label>
                <input type="text" id="nombre" name="nombre" required value="<?php echo isset($_SESSION['form_data']['nombre']) ? htmlspecialchars($_SESSION['form_data']['nombre']) : ''; ?>">

                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required value="<?php echo isset($_SESSION['form_data']['email']) ? htmlspecialchars($_SESSION['form_data']['email']) : ''; ?>">

                <label for="contrasenya">Contraseña:</label>
                <input type="password" id="contrasenya" name="contrasenya" required>

                <label for="confirmar_contrasenya">Confirmar Contraseña:</label>
                <input type="password" id="confirmar_contrasenya" name="confirmar_contrasenya" required>

                <label for="rol">Rol:</label>
                <select name="rol" id="rol" required>
                    <option value="usuario">Usuario</option>
                    <option value="miembro">Miembro</option>
                    <option value="monitor">Monitor</option>
                    <option value="admin">Administrador</option>
                </select>
                <button type="submit" name="crear_usuario">Crear Usuario</button>
            </form>
        </section>
    </main>

    <script src="../assets/js/validacion.js"></script>
    <?php include 'includes/footer.php'; ?>
</body>

</html>