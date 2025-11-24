<?php
// mysqli/libros.php
require_once __DIR__ . '/config.php';
header('Content-Type: application/json');

$action = $_GET['action'] ?? 'list';

try {
    switch ($action) {
        case 'create':
            $titulo = trim($_POST['titulo'] ?? '');
            $autor = trim($_POST['autor'] ?? '');
            $isbn = trim($_POST['isbn'] ?? '');
            $anio = (int)($_POST['anio_publicacion'] ?? 0);
            $cantidad = (int)($_POST['cantidad'] ?? 0);

            if ($titulo === '' || $autor === '' || $isbn === '' || $anio < 1000 || $cantidad < 0) {
                http_response_code(422);
                echo json_encode(['error' => 'Datos inválidos']);
                exit;
            }

            $stmt = $mysqli->prepare('INSERT INTO libros (titulo, autor, isbn, anio_publicacion, cantidad) VALUES (?, ?, ?, ?, ?)');
            $stmt->bind_param('sssii', $titulo, $autor, $isbn, $anio, $cantidad);
            if (!$stmt->execute()) {
                http_response_code(400);
                echo json_encode(['error' => $stmt->error]);
                exit;
            }
            echo json_encode(['id' => $stmt->insert_id]);
            break;

        case 'list':
            [$page, $limit, $offset] = get_pagination();
            $total = $mysqli->query('SELECT COUNT(*) AS c FROM libros')->fetch_assoc()['c'];
            $stmt = $mysqli->prepare('SELECT id, titulo, autor, isbn, anio_publicacion, cantidad FROM libros ORDER BY creado_en DESC LIMIT ? OFFSET ?');
            $stmt->bind_param('ii', $limit, $offset);
            $stmt->execute();
            $res = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            echo json_encode(['page'=>$page,'limit'=>$limit,'total'=>(int)$total,'data'=>$res]);
            break;

        case 'search':
            $q = trim($_GET['q'] ?? '');
            [$page, $limit, $offset] = get_pagination();
            $like = '%' . $q . '%';
            $stmt = $mysqli->prepare('SELECT id, titulo, autor, isbn, anio_publicacion, cantidad
                                      FROM libros
                                      WHERE titulo LIKE ? OR autor LIKE ? OR isbn LIKE ?
                                      ORDER BY titulo ASC
                                      LIMIT ? OFFSET ?');
            $stmt->bind_param('sssii', $like, $like, $like, $limit, $offset);
            $stmt->execute();
            $res = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            echo json_encode(['query'=>$q,'data'=>$res]);
            break;

        case 'update':
            $id = (int)($_POST['id'] ?? 0);
            $titulo = trim($_POST['titulo'] ?? '');
            $autor = trim($_POST['autor'] ?? '');
            $isbn = trim($_POST['isbn'] ?? '');
            $anio = (int)($_POST['anio_publicacion'] ?? 0);
            $cantidad = (int)($_POST['cantidad'] ?? 0);

            if ($id <= 0) { http_response_code(422); echo json_encode(['error'=>'ID inválido']); exit; }

            $stmt = $mysqli->prepare('UPDATE libros SET titulo=?, autor=?, isbn=?, anio_publicacion=?, cantidad=? WHERE id=?');
            $stmt->bind_param('sssiii', $titulo, $autor, $isbn, $anio, $cantidad, $id);
            if (!$stmt->execute()) { http_response_code(400); echo json_encode(['error'=>$stmt->error]); exit; }
            echo json_encode(['updated'=> $stmt->affected_rows]);
            break;

        case 'delete':
            $id = (int)($_POST['id'] ?? 0);
            if ($id <= 0) { http_response_code(422); echo json_encode(['error'=>'ID inválido']); exit; }
            $stmt = $mysqli->prepare('DELETE FROM libros WHERE id=?');
            $stmt->bind_param('i', $id);
            if (!$stmt->execute()) { http_response_code(400); echo json_encode(['error'=>$stmt->error]); exit; }
            echo json_encode(['deleted'=> $stmt->affected_rows]);
            break;

        default:
            http_response_code(404);
            echo json_encode(['error'=>'Acción no válida']);
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error interno']);
}
