<?php
session_start();
require_once('../src/db_connection.php'); // Cargar el archivo de conexión a la base de datos

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $conn->real_escape_string($_POST['nombre']);
    $email = $conn->real_escape_string($_POST['email']);
    $contrasenya = $_POST['contrasenya'];

    $_SESSION['form_data'] = $_POST;  // Guarda los datos en la sesión

    if (strlen($contrasenya) < 6) {
        $_SESSION['error'] = "La contraseña debe tener al menos 6 caracteres.";
        header("Location: /Gimnasio/src/reg.php");
        exit();
    }

    $contrasenyaHash = password_hash($contrasenya, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("SELECT id_usuario FROM usuario WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $_SESSION['error'] = "El correo electrónico ya está registrado.";
        header("Location: /Gimnasio/src/reg.php");
        exit();
    }

    $stmt->close();

    $stmt = $conn->prepare("INSERT INTO usuario (nombre, email, contrasenya) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $nombre, $email, $contrasenyaHash);

    if ($stmt->execute()) {
        $_SESSION['mensaje'] = "Registro exitoso.";
        unset($_SESSION['form_data']);  // Limpia los datos si el registro es exitoso
        header("Location: /Gimnasio/src/reg.php");
    } else {
        $_SESSION['error'] = "Error al registrarse.";
        header("Location: /Gimnasio/src/reg.php");
    }

    $stmt->close();
}

$conn->close();
