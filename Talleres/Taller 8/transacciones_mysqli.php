<?php
require_once "config_mysqli.php";
require_once "log_helper.php";

mysqli_begin_transaction($conn);

try {

    // Insertar usuario
    $sql = "INSERT INTO usuarios (nombre, email) VALUES (?, ?)";
    if(!$stmt = mysqli_prepare($conn, $sql)){
        throw new Exception("Error al preparar usuario: " . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, "ss", $nombre, $email);
    $nombre = "Nuevo Usuario";
    $email = "nuevo@example.com";

    if(!mysqli_stmt_execute($stmt)){
        throw new Exception("Error al insertar usuario: " . mysqli_error($conn));
    }

    $usuario_id = mysqli_insert_id($conn);

    // Insertar publicación
    $sql = "INSERT INTO publicaciones (usuario_id, titulo, contenido) VALUES (?, ?, ?)";
    if(!$stmt = mysqli_prepare($conn, $sql)){
        throw new Exception("Error al preparar publicación: " . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, "iss", $usuario_id, $titulo, $contenido);
    $titulo = "Nueva Publicación";
    $contenido = "Contenido de la nueva publicación";

    if(!mysqli_stmt_execute($stmt)){
        throw new Exception("Error al insertar publicación: " . mysqli_error($conn));
    }

    mysqli_commit($conn);
    echo "Transacción completada con éxito.";

} catch (Exception $e) {

    mysqli_rollback($conn);
    registrar_error("MySQLi - " . $e->getMessage());
    echo "Error en la transacción: " . $e->getMessage();
}

mysqli_close($conn);
?>
