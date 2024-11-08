<?php
session_start();
require 'db_connection.php'; // Cargar la conexión a la base de datos

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $contrasenya = $_POST['contrasenya'];

    $_SESSION['form_data'] = ['email' => $email]; // Guardar el email en la sesión

    $stmt = $conn->prepare("SELECT id_usuario, contrasenya, rol FROM usuario WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id_usuario, $hashedPassword, $rol);
        $stmt->fetch();

        if (password_verify($contrasenya, $hashedPassword)) {
            $_SESSION['id_usuario'] = $id_usuario;
            $_SESSION['email'] = $email;
            $_SESSION['rol'] = $rol;

            $stmtNombre = $conn->prepare("SELECT nombre FROM usuario WHERE id_usuario = ?");
            $stmtNombre->bind_param("i", $id_usuario);
            $stmtNombre->execute();
            $stmtNombre->bind_result($nombre);
            $stmtNombre->fetch();
            $_SESSION['nombre'] = $nombre;
            $stmtNombre->close();

            // Limpia los datos del formulario en la sesión al iniciar sesión correctamente
            unset($_SESSION['form_data']);

            switch ($rol) {
                case 'admin':
                    header("Location: /Gimnasio/src/admin.php");
                    break;
                case 'monitor':
                    header("Location: /Gimnasio/src/monitor.php");
                    break;
                case 'miembro':
                    header("Location: /Gimnasio/src/miembro.php");
                    break;
                default:
                    header("Location: /Gimnasio/src/usuario.php");
                    break;
            }
            exit();
        } else {
            $_SESSION['error'] = "Contraseña incorrecta";
            header("Location: /Gimnasio/src/log.php");
            exit();
        }
    } else {
        $_SESSION['error'] = "Usuario no encontrado";
        header("Location: /Gimnasio/src/log.php");
        exit();
    }
    $stmt->close();
}

$conn->close();
