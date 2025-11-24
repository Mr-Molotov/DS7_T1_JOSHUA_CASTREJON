<?php
// mysqli/usuarios.php
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
                echo json_encode(['error' => 'Datos inválidos']);
                exit;
            }

            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $mysqli->prepare('INSERT INTO usuarios (nombre, email, password_hash) VALUES (?, ?, ?)');
            $stmt->bind_param('sss', $nombre, $email, $hash);
            if (!$stmt->execute()) { http_response_code(400); echo json_encode(['error'=>$stmt->error]); exit; }
            echo json_encode(['id' => $stmt->insert_id]);
            break;

        case 'list':
            [$page, $limit, $offset] = get_pagination();
            $total = $mysqli->query('SELECT COUNT(*) AS c FROM usuarios')->fetch_assoc()['c'];
            $stmt = $mysqli->prepare('SELECT id, nombre, email, creado_en FROM usuarios ORDER BY creado_en DESC LIMIT ? OFFSET ?');
            $stmt->bind_param('ii', $limit, $offset);
            $stmt->execute();
            $res = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            echo json_encode(['page'=>$page,'limit'=>$limit,'total'=>(int)$total,'data'=>$res]);
            break;

        case 'search':
            $q = trim($_GET['q'] ?? '');
            [$page, $limit, $offset] = get_pagination();
            $like = '%' . $q . '%';
            $stmt = $mysqli->prepare('SELECT id, nombre, email, creado_en FROM usuarios WHERE nombre LIKE ? OR email LIKE ? ORDER BY nombre ASC LIMIT ? OFFSET ?');
            $stmt->bind_param('ssii', $like, $like, $limit, $offset);
            $stmt->execute();
            $res = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            echo json_encode(['query'=>$q,'data'=>$res]);
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
            $stmt = $mysqli->prepare('UPDATE usuarios SET nombre=?, email=? WHERE id=?');
            $stmt->bind_param('ssi', $nombre, $email, $id);
            if (!$stmt->execute()) { http_response_code(400); echo json_encode(['error'=>$stmt->error]); exit; }
            echo json_encode(['updated'=> $stmt->affected_rows]);
            break;

        case 'delete':
            $id = (int)($_POST['id'] ?? 0);
            if ($id <= 0) { http_response_code(422); echo json_encode(['error'=>'ID inválido']); exit; }
            $stmt = $mysqli->prepare('DELETE FROM usuarios WHERE id=?');
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
    echo json_encode(['error'=>'Error interno']);
}
