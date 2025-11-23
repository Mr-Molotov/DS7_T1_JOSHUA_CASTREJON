<?php
require_once "config_pdo.php";
require_once "log_helper.php";

try {

    $pdo->beginTransaction();

    // Insertar usuario
    $sql = "INSERT INTO usuarios (nombre, email) VALUES (:nombre, :email)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':nombre' => 'Nuevo Usuario',
        ':email' => 'nuevo@example.com'
    ]);
    $usuario_id = $pdo->lastInsertId();

    // Insertar publicación
    $sql = "INSERT INTO publicaciones (usuario_id, titulo, contenido) VALUES (:usuario_id, :titulo, :contenido)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':usuario_id' => $usuario_id,
        ':titulo' => 'Nueva Publicación',
        ':contenido' => 'Contenido de la nueva publicación'
    ]);

    $pdo->commit();
    echo "Transacción completada con éxito.";

} catch (Exception $e) {

    $pdo->rollBack();
    registrar_error("PDO - " . $e->getMessage());
    echo "Error en la transacción: " . $e->getMessage();
}
?>
