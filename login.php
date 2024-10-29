<?php
session_start(); // Iniciar sesión para manejar datos de sesión

// Conexión a la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gimnasio_db";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar si la conexión tiene errores
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Procesar el formulario solo si se envió con el método POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Escapar el email para evitar inyecciones SQL
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];

    // Consulta para obtener los datos del usuario (id, contraseña y rol)
    $stmt = $conn->prepare("SELECT id_usuario, password, rol FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Vincular los resultados de la consulta a variables
        $stmt->bind_result($id_usuario, $hashed_password, $rol);
        $stmt->fetch();

        // Verificar si la contraseña ingresada coincide con la almacenada
        if (password_verify($password, $hashed_password)) {
            // Guardar el ID de usuario y el rol en la sesión
            $_SESSION['id_usuario'] = $id_usuario;
            $_SESSION['rol'] = $rol;

            // Redirigir según el rol del usuario
            if ($rol == 'admin') {
                header("Location: admin.php");
            } elseif ($rol == 'monitor') {
                header("Location: monitor.php");
            } else {
                header("Location: miembro.php");
            }
            exit();
        } else {
            // Redirigir a index con un mensaje de error si la contraseña es incorrecta
            header("Location: index.php?error=Contraseña+incorrecta");
            exit();
        }
    } else {
        // Redirigir a index con un mensaje de error si el usuario no se encuentra
        header("Location: index.php?error=Usuario+no+encontrado");
        exit();
    }

    $stmt->close(); // Cerrar la declaración preparada
}

$conn->close(); // Cerrar la conexión a la base de datos
