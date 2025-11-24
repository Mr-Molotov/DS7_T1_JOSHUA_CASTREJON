<?php
// pdo/usuarios.php
require_once __DIR__ . '/config.php';
header('Content-Type: application/json');

$action = $_GET['action'] ?? 'list';

try {
    switch ($action) {
        case 'create':
            $nombre = trim($_POST['nombre'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            if ($nombre === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 6) {
                http_response_code(422);
                echo json_encode(['error'=>'Datos inválidos']);
                exit;
            }

            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO usuarios (nombre, email, password_hash) VALUES (?, ?, ?)');
            $stmt->execute([$nombre, $email, $hash]);
            echo json_encode(['id' => (int)$pdo->lastInsertId()]);
            break;

        case 'list':
            [$page, $limit, $offset] = get_pagination();
            $total = (int)$pdo->query('SELECT COUNT(*) FROM usuarios')->fetchColumn();
            $stmt = $pdo->prepare('SELECT id, nombre, email, creado_en FROM usuarios ORDER BY creado_en DESC LIMIT ? OFFSET ?');
            $stmt->bindValue(1, $limit, PDO::PARAM_INT);
            $stmt->bindValue(2, $offset, PDO::PARAM_INT);
            $stmt->execute();
            echo json_encode(['page'=>$page,'limit'=>$limit,'total'=>$total,'data'=>$stmt->fetchAll()]);
            break;

        case 'search':
            $q = trim($_GET['q'] ?? '');
            [$page, $limit, $offset] = get_pagination();
            $like = '%' . $q . '%';
            $stmt = $pdo->prepare('SELECT id, nombre, email, creado_en FROM usuarios WHERE nombre LIKE ? OR email LIKE ? ORDER BY nombre ASC LIMIT ? OFFSET ?');
            $stmt->bindValue(1, $like);
            $stmt->bindValue(2, $like);
            $stmt->bindValue(3, $limit, PDO::PARAM_INT);
            $stmt->bindValue(4, $offset, PDO::PARAM_INT);
            $stmt->execute();
            echo json_encode(['query'=>$q,'data'=>$stmt->fetchAll()]);
            break;

        case 'update':
            $id = (int)($_POST['id'] ?? 0);
            $nombre = trim($_POST['nombre'] ?? '');
            $email = trim($_POST['email'] ?? '');
            if ($id <= 0 || $nombre === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                http_response_code(422);
                echo json_encode(['error'=>'Datos inválidos']);
                exit;
            }
            $stmt = $pdo->prepare('UPDATE usuarios SET nombre=?, email=? WHERE id=?');
            $stmt->execute([$nombre, $email, $id]);
            echo json_encode(['updated'=> $stmt->rowCount()]);
            break;

        case 'delete':
            $id = (int)($_POST['id'] ?? 0);
            if ($id <= 0) { http_response_code(422); echo json_encode(['error'=>'ID inválido']); exit; }
            $stmt = $pdo->prepare('DELETE FROM usuarios WHERE id=?');
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
