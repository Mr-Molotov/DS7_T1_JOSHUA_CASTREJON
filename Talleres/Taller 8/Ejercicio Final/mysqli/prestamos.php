<?php
// mysqli/prestamos.php
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

            $mysqli->begin_transaction();
            try {
                // Verificar disponibilidad
                $stmt = $mysqli->prepare('SELECT cantidad FROM libros WHERE id=? FOR UPDATE');
                $stmt->bind_param('i', $libro_id);
                $stmt->execute();
                $row = $stmt->get_result()->fetch_assoc();
                if (!$row || (int)$row['cantidad'] < 1) {
                    throw new Exception('Libro no disponible');
                }

                // Insertar préstamo
                $stmt = $mysqli->prepare('INSERT INTO prestamos (usuario_id, libro_id, fecha_prestamo) VALUES (?, ?, ?)');
                $stmt->bind_param('iis', $usuario_id, $libro_id, $fecha);
                if (!$stmt->execute()) { throw new Exception($stmt->error); }

                // Actualizar stock
                $stmt = $mysqli->prepare('UPDATE libros SET cantidad = cantidad - 1 WHERE id=?');
                $stmt->bind_param('i', $libro_id);
                if (!$stmt->execute()) { throw new Exception($stmt->error); }

                $mysqli->commit();
                echo json_encode(['prestamo_id' => $stmt->insert_id ?? null]);
            } catch (Throwable $txe) {
                $mysqli->rollback();
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
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param('ii', $limit, $offset);
            $stmt->execute();
            $res = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            echo json_encode(['data'=>$res]);
            break;

        case 'return':
            $prestamo_id = (int)($_POST['prestamo_id'] ?? 0);
            $fecha_dev = $_POST['fecha_devolucion'] ?? date('Y-m-d');
            if ($prestamo_id <= 0 || !is_valid_date($fecha_dev)) {
                http_response_code(422);
                echo json_encode(['error'=>'Datos inválidos']);
                exit;
            }

            $mysqli->begin_transaction();
            try {
                // Obtener libro asociado y bloquear fila de préstamo
                $stmt = $mysqli->prepare('SELECT libro_id, devuelto FROM prestamos WHERE id=? FOR UPDATE');
                $stmt->bind_param('i', $prestamo_id);
                $stmt->execute();
                $row = $stmt->get_result()->fetch_assoc();
                if (!$row) { throw new Exception('Préstamo no encontrado'); }
                if ((int)$row['devuelto'] === 1) { throw new Exception('Préstamo ya devuelto'); }
                $libro_id = (int)$row['libro_id'];

                // Marcar devolución
                $stmt = $mysqli->prepare('UPDATE prestamos SET devuelto=1, fecha_devolucion=? WHERE id=?');
                $stmt->bind_param('si', $fecha_dev, $prestamo_id);
                if (!$stmt->execute()) { throw new Exception($stmt->error); }

                // Incrementar stock
                $stmt = $mysqli->prepare('UPDATE libros SET cantidad = cantidad + 1 WHERE id=?');
                $stmt->bind_param('i', $libro_id);
                if (!$stmt->execute()) { throw new Exception($stmt->error); }

                $mysqli->commit();
                echo json_encode(['returned'=>true]);
            } catch (Throwable $txe) {
                $mysqli->rollback();
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
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param('iii', $usuario_id, $limit, $offset);
            $stmt->execute();
            $res = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            echo json_encode(['usuario_id'=>$usuario_id,'data'=>$res]);
            break;

        default:
            http_response_code(404);
            echo json_encode(['error'=>'Acción no válida']);
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error'=>'Error interno']);
}
