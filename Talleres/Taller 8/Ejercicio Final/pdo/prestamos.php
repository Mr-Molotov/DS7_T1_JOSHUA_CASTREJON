<?php
// pdo/prestamos.php
require_once __DIR__ . '/config.php';
header('Content-Type: application/json');

$action = $_GET['action'] ?? 'list';

try {
    switch ($action) {
        case 'create':
            $usuario_id = (int)($_POST['usuario_id'] ?? 0);
            $libro_id = (int)($_POST['libro_id'] ?? 0);
            $fecha = $_POST['fecha_prestamo'] ?? date('Y-m-d');

            if ($usuario_id <= 0 || $libro_id <= 0 || !is_valid_date($fecha)) {
                http_response_code(422);
                echo json_encode(['error'=>'Datos inválidos']);
                exit;
            }

            $pdo->beginTransaction();
            try {
                // Verificar disponibilidad y bloquear fila
                $stmt = $pdo->prepare('SELECT cantidad FROM libros WHERE id=? FOR UPDATE');
                $stmt->execute([$libro_id]);
                $row = $stmt->fetch();
                if (!$row || (int)$row['cantidad'] < 1) {
                    throw new Exception('Libro no disponible');
                }

                // Insertar préstamo
                $stmt = $pdo->prepare('INSERT INTO prestamos (usuario_id, libro_id, fecha_prestamo) VALUES (?, ?, ?)');
                $stmt->execute([$usuario_id, $libro_id, $fecha]);
                $prestamo_id = (int)$pdo->lastInsertId();

                // Actualizar stock
                $stmt = $pdo->prepare('UPDATE libros SET cantidad = cantidad - 1 WHERE id=?');
                $stmt->execute([$libro_id]);

                $pdo->commit();
                echo json_encode(['prestamo_id' => $prestamo_id]);
            } catch (Throwable $txe) {
                $pdo->rollBack();
                http_response_code(400);
                echo json_encode(['error' => $txe->getMessage()]);
            }
            break;

        case 'list':
            [$page, $limit, $offset] = get_pagination();
            $sql = 'SELECT p.id, p.usuario_id, u.nombre AS usuario, p.libro_id, l.titulo AS libro,
                           p.fecha_prestamo, p.fecha_devolucion, p.devuelto
                    FROM prestamos p
                    JOIN usuarios u ON u.id = p.usuario_id
                    JOIN libros l ON l.id = p.libro_id
                    WHERE p.devuelto = 0
                    ORDER BY p.fecha_prestamo DESC
                    LIMIT ? OFFSET ?';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(1, $limit, PDO::PARAM_INT);
            $stmt->bindValue(2, $offset, PDO::PARAM_INT);
            $stmt->execute();
            echo json_encode(['data'=>$stmt->fetchAll()]);
            break;

        case 'return':
            $prestamo_id = (int)($_POST['prestamo_id'] ?? 0);
            $fecha_dev = $_POST['fecha_devolucion'] ?? date('Y-m-d');
            if ($prestamo_id <= 0 || !is_valid_date($fecha_dev)) {
                http_response_code(422);
                echo json_encode(['error'=>'Datos inválidos']);
                exit;
            }

            $pdo->beginTransaction();
            try {
                // Obtener libro y bloqueo
                $stmt = $pdo->prepare('SELECT libro_id, devuelto FROM prestamos WHERE id=? FOR UPDATE');
                $stmt->execute([$prestamo_id]);
                $row = $stmt->fetch();
                if (!$row) { throw new Exception('Préstamo no encontrado'); }
                if ((int)$row['devuelto'] === 1) { throw new Exception('Préstamo ya devuelto'); }
                $libro_id = (int)$row['libro_id'];

                // Marcar devolución
                $stmt = $pdo->prepare('UPDATE prestamos SET devuelto=1, fecha_devolucion=? WHERE id=?');
                $stmt->execute([$fecha_dev, $prestamo_id]);

                // Incrementar stock
                $stmt = $pdo->prepare('UPDATE libros SET cantidad = cantidad + 1 WHERE id=?');
                $stmt->execute([$libro_id]);

                $pdo->commit();
                echo json_encode(['returned'=>true]);
            } catch (Throwable $txe) {
                $pdo->rollBack();
                http_response_code(400);
                echo json_encode(['error'=>$txe->getMessage()]);
            }
            break;

        case 'historial':
            $usuario_id = (int)($_GET['usuario_id'] ?? 0);
            [$page, $limit, $offset] = get_pagination();
            if ($usuario_id <= 0) { http_response_code(422); echo json_encode(['error'=>'Usuario inválido']); exit; }
            $sql = 'SELECT p.id, l.titulo AS libro, p.fecha_prestamo, p.fecha_devolucion, p.devuelto
                    FROM prestamos p
                    JOIN libros l ON l.id = p.libro_id
                    WHERE p.usuario_id = ?
                    ORDER BY p.creado_en DESC
                    LIMIT ? OFFSET ?';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(1, $usuario_id, PDO::PARAM_INT);
            $stmt->bindValue(2, $limit, PDO::PARAM_INT);
            $stmt->bindValue(3, $offset, PDO::PARAM_INT);
            $stmt->execute();
            echo json_encode(['usuario_id'=>$usuario_id,'data'=>$stmt->fetchAll()]);
            break;

        default:
            http_response_code(404);
            echo json_encode(['error'=>'Acción no válida']);
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error'=>'Error interno']);
}
