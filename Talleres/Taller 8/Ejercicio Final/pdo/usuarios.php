<?php
// pdo/usuarios.php
require_once 'config.php';

function sanitize($s){ return trim($s); }

$action = $_REQUEST['action'] ?? 'list';

try {
    if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $nombre = sanitize($_POST['nombre'] ?? '');
        $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
        $password = $_POST['password'] ?? '';

        if (!$nombre || !$email || strlen($password) < 6) throw new Exception('Datos inválidos.');

        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email, password_hash) VALUES (?, ?, ?)");
        $stmt->execute([$nombre, $email, $password_hash]);
        echo "Usuario creado ID: " . $pdo->lastInsertId();
        exit;
    }

    if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) throw new Exception('ID inválido.');
        $nombre = sanitize($_POST['nombre'] ?? '');
        $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
        $password = $_POST['password'] ?? null;

        if ($password) {
            $ph = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE usuarios SET nombre=?, email=?, password_hash=? WHERE id=?");
            $stmt->execute([$nombre, $email, $ph, $id]);
        } else {
            $stmt = $pdo->prepare("UPDATE usuarios SET nombre=?, email=? WHERE id=?");
            $stmt->execute([$nombre, $email, $id]);
        }
        echo "Usuario actualizado.";
        exit;
    }

    if ($action === 'delete') {
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) throw new Exception('ID inválido.');
        $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);
        echo "Usuario eliminado.";
        exit;
    }

    // listar
    $page = max(1, (int)($_GET['page'] ?? 1));
    $limit = max(1, min(100, (int)($_GET['limit'] ?? 10)));
    $offset = ($page - 1) * $limit;
    $search = trim($_GET['q'] ?? '');

    if ($search !== '') {
        $like = "%{$search}%";
        $total = (int)$pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE nombre LIKE ? OR email LIKE ?")->execute([$like, $like]);
        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE nombre LIKE ? OR email LIKE ?");
        $countStmt->execute([$like, $like]);
        $total = (int)$countStmt->fetchColumn();

        $stmt = $pdo->prepare("SELECT id, nombre, email, creado_en FROM usuarios WHERE nombre LIKE ? OR email LIKE ? ORDER BY id DESC LIMIT ? OFFSET ?");
        $stmt->execute([$like, $like, $limit, $offset]);
    } else {
        $total = (int)$pdo->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
        $stmt = $pdo->prepare("SELECT id, nombre, email, creado_en FROM usuarios ORDER BY id DESC LIMIT ? OFFSET ?");
        $stmt->execute([$limit, $offset]);
    }

    $rows = $stmt->fetchAll();

    echo "<h2>Usuarios (Página {$page})</h2>";
    echo "<form method='get'><input type='text' name='q' placeholder='buscar' value='".htmlspecialchars($search,ENT_QUOTES)."'><button>Buscar</button></form>";
    echo "<table border='1' cellpadding='6'><tr><th>ID</th><th>Nombre</th><th>Email</th><th>Creado</th><th>Acciones</th></tr>";
    foreach($rows as $row) {
        echo "<tr>
            <td>{$row['id']}</td>
            <td>".htmlspecialchars($row['nombre'])."</td>
            <td>".htmlspecialchars($row['email'])."</td>
            <td>".htmlspecialchars($row['creado_en'])."</td>
            <td><a href='?action=delete&id={$row['id']}' onclick='return confirm(\"Eliminar?\")'>Eliminar</a></td>
        </tr>";
    }
    echo "</table>";

    $totalPages = (int)ceil($total / $limit);
    echo "<p>Total: {$total} | Páginas: {$totalPages}</p>";
    for ($p=1;$p<=$totalPages;$p++){
        $link = "?page={$p}&limit={$limit}";
        if ($search) $link .= "&q=" . urlencode($search);
        echo ($p==$page) ? "<strong>{$p}</strong> " : "<a href='{$link}'>{$p}</a> ";
    }

} catch (Exception $e) {
    http_response_code(400);
    echo "Error: " . htmlspecialchars($e->getMessage());
}
