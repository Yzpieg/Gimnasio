<?php
session_start();

// Conexión a la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gimnasio_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];

    // Consulta para verificar el usuario y el rol
    $stmt = $conn->prepare("SELECT id_usuario, password, rol FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id_usuario, $hashed_password, $rol);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            // Guardar el rol en la sesión
            $_SESSION['id_usuario'] = $id_usuario;
            $_SESSION['rol'] = $rol;  // Aquí guardamos el rol

            if ($rol == 'admin') {
                header("Location: admin.php");
            } elseif ($rol == 'monitor') {
                header("Location: monitor.php");
            } else {
                header("Location: miembro.php");
            }
            exit();
        } else {
            header("Location: index.php?error=Contraseña+incorrecta");
            exit();
        }
    } else {
        header("Location: index.php?error=Usuario+no+encontrado");
        exit();
    }

    $stmt->close();
}

$conn->close();
