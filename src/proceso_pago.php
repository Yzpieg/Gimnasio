<?php
session_start();
include 'includes/general.php';

$conn = obtenerConexion();

if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../index.php?error=Debes+iniciar+sesión+primero");
    exit();
}

$id_usuario = $_SESSION['id_usuario'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_membresia = $_POST['id_membresia'];
    $metodo_pago = $_POST['metodo_pago'];

    // Verificar o crear el id_miembro correspondiente al usuario
    $query_miembro = "SELECT id_miembro FROM miembro WHERE id_usuario = ?";
    $stmt_miembro = $conn->prepare($query_miembro);
    $stmt_miembro->bind_param("i", $id_usuario);
    $stmt_miembro->execute();
    $result_miembro = $stmt_miembro->get_result();

    if ($result_miembro->num_rows === 0) {
        // Si el id_miembro no existe, crea un nuevo registro en miembro con id_membresia
        $fecha_registro = date('Y-m-d');
        $insert_miembro = "INSERT INTO miembro (id_usuario, fecha_registro, id_membresia) VALUES (?, ?, ?)";
        $stmt_insert_miembro = $conn->prepare($insert_miembro);
        $stmt_insert_miembro->bind_param("isi", $id_usuario, $fecha_registro, $id_membresia);
        $stmt_insert_miembro->execute();
        $id_miembro = $stmt_insert_miembro->insert_id; // Obtener el id_miembro recién creado
        $stmt_insert_miembro->close();
    } else {
        // Si ya existe, obtén el id_miembro y actualiza id_membresia
        $row_miembro = $result_miembro->fetch_assoc();
        $id_miembro = $row_miembro['id_miembro'];

        // Actualizar la membresía en la tabla miembro
        $update_membresia_miembro = "UPDATE miembro SET id_membresia = ? WHERE id_miembro = ?";
        $stmt_update_membresia = $conn->prepare($update_membresia_miembro);
        $stmt_update_membresia->bind_param("ii", $id_membresia, $id_miembro);
        $stmt_update_membresia->execute();
        $stmt_update_membresia->close();
    }

    // Continuar con el proceso de pago y registro de membresía
    $query = "SELECT precio, duracion FROM membresia WHERE id_membresia = ?";
    $stmt = $conn->prepare($query);

    if (!$stmt) {
        header("Location: usuario.php?error=Error+al+preparar+consulta.");
        exit();
    }

    $stmt->bind_param("i", $id_membresia);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $monto_pagado = $row['precio'];
        $duracion_meses = $row['duracion'];
        $fecha_inicio = date('Y-m-d');
        $fecha_fin = date('Y-m-d', strtotime("+$duracion_meses months"));

        // Insertar el pago en la tabla 'pago'
        $insert_pago = "INSERT INTO pago (id_miembro, monto, fecha_pago, metodo_pago) VALUES (?, ?, NOW(), ?)";
        $stmt_pago = $conn->prepare($insert_pago);

        if (!$stmt_pago) {
            header("Location: usuario.php?error=Error+al+preparar+inserción+de+pago.");
            exit();
        }

        $stmt_pago->bind_param("ids", $id_miembro, $monto_pagado, $metodo_pago);

        if ($stmt_pago->execute()) {
            // Si el pago fue exitoso, insertar en la tabla miembro_membresia
            $estado = 'activa';
            $renovacion_automatica = 0;

            $insert_membresia = "INSERT INTO miembro_membresia (id_miembro, id_membresia, monto_pagado, fecha_inicio, fecha_fin, estado, renovacion_automatica) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt_membresia = $conn->prepare($insert_membresia);

            if (!$stmt_membresia) {
                header("Location: usuario.php?error=Error+al+preparar+inserción+de+membresía.");
                exit();
            }

            $stmt_membresia->bind_param("iissssi", $id_miembro, $id_membresia, $monto_pagado, $fecha_inicio, $fecha_fin, $estado, $renovacion_automatica);

            if ($stmt_membresia->execute()) {
                // Actualizar el rol del usuario a 'miembro'
                $update_rol = "UPDATE usuario SET rol = 'miembro' WHERE id_usuario = ?";
                $stmt_rol = $conn->prepare($update_rol);

                if (!$stmt_rol) {
                    header("Location: usuario.php?error=Error+al+actualizar+el+rol.");
                    exit();
                }

                $stmt_rol->bind_param("i", $id_usuario);

                if ($stmt_rol->execute()) {
                    $_SESSION['rol'] = 'miembro';

                    // Obtener los entrenamientos asociados a la membresía
                    $query_entrenamientos = "SELECT id_entrenamiento FROM membresia_entrenamiento WHERE id_membresia = ?";
                    $stmt_entrenamientos = $conn->prepare($query_entrenamientos);

                    if ($stmt_entrenamientos) {
                        $stmt_entrenamientos->bind_param("i", $id_membresia);
                        $stmt_entrenamientos->execute();
                        $result_entrenamientos = $stmt_entrenamientos->get_result();

                        // Insertar los entrenamientos en miembro_entrenamiento
                        while ($entrenamiento = $result_entrenamientos->fetch_assoc()) {
                            $id_especialidad = $entrenamiento['id_entrenamiento'];
                            $insert_entrenamiento = "INSERT INTO miembro_entrenamiento (id_miembro, id_especialidad) VALUES (?, ?)";
                            $stmt_insert_entrenamiento = $conn->prepare($insert_entrenamiento);

                            if ($stmt_insert_entrenamiento) {
                                $stmt_insert_entrenamiento->bind_param("ii", $id_miembro, $id_especialidad);
                                $stmt_insert_entrenamiento->execute();
                                $stmt_insert_entrenamiento->close();
                            }
                        }

                        $stmt_entrenamientos->close();
                    }

                    // Redirigir a la página de miembro con un mensaje de confirmación
                    header("Location: miembro.php?mensaje=Membresía adquirida correctamente. Bienvenido como miembro.");
                    exit();
                } else {
                    header("Location: usuario.php?error=Error+al+actualizar+el+rol+de+usuario.");
                    exit();
                }

                $stmt_rol->close();
            } else {
                header("Location: usuario.php?error=Error+al+guardar+la+membresía.");
                exit();
            }
            $stmt_membresia->close();
        } else {
            header("Location: usuario.php?error=Error+al+procesar+el+pago.");
            exit();
        }
        $stmt_pago->close();
    } else {
        header("Location: usuario.php?error=Membresía+no+encontrada.");
        exit();
    }

    $stmt->close();
    $conn->close();
}
