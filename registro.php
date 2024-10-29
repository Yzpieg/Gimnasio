<?php
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
    $nombre = $conn->real_escape_string($_POST['nombre']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $rol = 'miembro';

    // Verificar si el usuario ya existe
    $stmt = $conn->prepare("SELECT id_usuario FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Redirigir con mensaje de error si el usuario ya existe
        header("Location: index.php?error=El+correo+electrónico+ya+está+registrado");
        exit();
    }

    $stmt->close();

    // Preparar la inserción si el usuario no existe
    $stmt = $conn->prepare("INSERT INTO usuarios (nombre, email, password, rol) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $nombre, $email, $password, $rol);

    if ($stmt->execute()) {
        // Redirigir con mensaje de éxito
        header("Location: index.php?mensaje=Registro+exitoso");
    } else {
        // Redirigir con mensaje de error en caso de fallo en la inserción
        header("Location: index.php?error=Error+al+registrarse");
    }

    $stmt->close();
}

$conn->close();
?>
