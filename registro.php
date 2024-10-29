<?php
// Conexión a la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gimnasio_db";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar si hay errores de conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Procesar el formulario solo si el método de solicitud es POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Escapar los datos de entrada para evitar inyecciones SQL
    $nombre = $conn->real_escape_string($_POST['nombre']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Encriptar la contraseña
    $rol = 'miembro'; // Asignar el rol por defecto "miembro"

    // Verificar si el correo electrónico ya está registrado
    $stmt = $conn->prepare("SELECT id_usuario FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Redirigir con mensaje de error si el correo ya está registrado
        header("Location: index.php?error=El+correo+electrónico+ya+está+registrado");
        exit();
    }

    $stmt->close();

    // Preparar la inserción del nuevo usuario en la base de datos
    $stmt = $conn->prepare("INSERT INTO usuarios (nombre, email, password, rol) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $nombre, $email, $password, $rol);

    if ($stmt->execute()) {
        // Redirigir a la página de inicio con un mensaje de registro exitoso
        header("Location: index.php?mensaje=Registro+exitoso");
    } else {
        // Redirigir con mensaje de error si la inserción falla
        header("Location: index.php?error=Error+al+registrarse");
    }

    $stmt->close(); // Cerrar la declaración preparada
}

$conn->close(); // Cerrar la conexión a la base de datos
