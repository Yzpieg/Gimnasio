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
    $rol = 'miembro'; // Asignar siempre el rol "miembro"

    $stmt = $conn->prepare("INSERT INTO usuarios (nombre, email, password, rol) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $nombre, $email, $password, $rol);

    if ($stmt->execute()) {
        header("Location: index.html?mensaje=Registro+exitoso");
    } else {
        header("Location: index.html?mensaje=Error+al+registrarse");
    }

    $stmt->close();
}

$conn->close();
?>
