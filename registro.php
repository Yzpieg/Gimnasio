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
    // Obtener y sanitizar los datos del formulario
    $nombre = $conn->real_escape_string($_POST['nombre']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $rol = 'miembro'; // Asignar siempre el rol "miembro"

    // Preparar la consulta SQL
    $stmt = $conn->prepare("INSERT INTO usuarios (nombre, email, password, rol) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $nombre, $email, $password, $rol);

    if ($stmt->execute()) {
        // Redirigir con mensaje de éxito
        header("Location: http://localhost/gimnasio/index.php?mensaje=Registro+exitoso");
        exit();
    } else {
        // Redirigir con mensaje de error
        header("Location: http://localhost/gimnasio/index.php?mensaje=Error+al+registrarse");
        exit();
    }

    $stmt->close();
}

$conn->close();
?>
