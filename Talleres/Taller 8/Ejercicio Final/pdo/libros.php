<?php
// pdo/libros.php
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
                echo json_encode(['error'=>'Datos inválidos']);
                exit;
            }

            $stmt = $pdo->prepare('INSERT INTO libros (titulo, autor, isbn, anio_publicacion, cantidad) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$titulo, $autor, $isbn, $anio, $cantidad]);
            echo json_encode(['id' => (int)$pdo->lastInsertId()]);
            break;

        case 'list':
            [$page, $limit, $offset] = get_pagination();
            $total = (int)$pdo->query('SELECT COUNT(*) FROM libros')->fetchColumn();
            $stmt = $pdo->prepare('SELECT id, titulo, autor, isbn, anio_publicacion, cantidad FROM libros ORDER BY creado_en DESC LIMIT ? OFFSET ?');
            $stmt->bindValue(1, $limit, PDO::PARAM_INT);
            $stmt->bindValue(2, $offset, PDO::PARAM_INT);
            $stmt->execute();
            $res = $stmt->fetchAll();
            echo json_encode(['page'=>$page,'limit'=>$limit,'total'=>$total,'data'=>$res]);
            break;

        case 'search':
            $q = trim($_GET['q'] ?? '');
            [$page, $limit, $offset] = get_pagination();
            $like = '%' . $q . '%';
            $stmt = $pdo->prepare('SELECT id, titulo, autor, isbn, anio_publicacion, cantidad
                                   FROM libros
                                   WHERE titulo LIKE ? OR autor LIKE ? OR isbn LIKE ?
                                   ORDER BY titulo ASC
                                   LIMIT ? OFFSET ?');
            $stmt->bindValue(1, $like);
            $stmt->bindValue(2, $like);
            $stmt->bindValue(3, $like);
            $stmt->bindValue(4, $limit, PDO::PARAM_INT);
            $stmt->bindValue(5, $offset, PDO::PARAM_INT);
            $stmt->execute();
            echo json_encode(['query'=>$q,'data'=>$stmt->fetchAll()]);
            break;

        case 'update':
            $id = (int)($_POST['id'] ?? 0);
            $titulo = trim($_POST['titulo'] ?? '');
            $autor = trim($_POST['autor'] ?? '');
            $isbn = trim($_POST['isbn'] ?? '');
            $anio = (int)($_POST['anio_publicacion'] ?? 0);
            $cantidad = (int)($_POST['cantidad'] ?? 0);

            if ($id <= 0) { http_response_code(422); echo json_encode(['error'=>'ID inválido']); exit; }

            $stmt = $pdo->prepare('UPDATE libros SET titulo=?, autor=?, isbn=?, anio_publicacion=?, cantidad=? WHERE id=?');
            $stmt->execute([$titulo, $autor, $isbn, $anio, $cantidad, $id]);
            echo json_encode(['updated'=> $stmt->rowCount()]);
            break;

        case 'delete':
            $id = (int)($_POST['id'] ?? 0);
            if ($id <= 0) { http_response_code(422); echo json_encode(['error'=>'ID inválido']); exit; }
            $stmt = $pdo->prepare('DELETE FROM libros WHERE id=?');
            $stmt->execute([$id]);
            echo json_encode(['deleted'=> $stmt->rowCount()]);
            break;

        default:
            http_response_code(404);
            echo json_encode(['error'=>'Acción no válida']);
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error'=>'Error interno']);
}
