<?php
// mysqli/usuarios.php
require_once 'config.php';

function sanitize($s){ return trim($s); }

$action = $_REQUEST['action'] ?? 'list';

try {
    if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $nombre = sanitize($_POST['nombre'] ?? '');
        $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
        $password = $_POST['password'] ?? '';

        if (!$nombre || !$email || strlen($password) < 6) throw new Exception('Datos inválidos. Contraseña mínimo 6 caracteres.');

        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO usuarios (nombre, email, password_hash) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare_checked($conn, $sql);
        $stmt->bind_param('sss', $nombre, $email, $password_hash);
        if (!$stmt->execute()) throw new Exception('Error al crear usuario: ' . $stmt->error);
        echo "Usuario creado ID: " . $stmt->insert_id;
        exit;
    }

    if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) throw new Exception('ID inválido.');
        $nombre = sanitize($_POST['nombre'] ?? '');
        $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
        $password = $_POST['password'] ?? null;

        if (!$nombre || !$email) throw new Exception('Nombre y email obligatorios.');

        if ($password) {
            $ph = password_hash($password, PASSWORD_DEFAULT);
            $sql = "UPDATE usuarios SET nombre=?, email=?, password_hash=? WHERE id=?";
            $stmt = mysqli_prepare_checked($conn, $sql);
            $stmt->bind_param('sssi', $nombre, $email, $ph, $id);
        } else {
            $sql = "UPDATE usuarios SET nombre=?, email=? WHERE id=?";
            $stmt = mysqli_prepare_checked($conn, $sql);
            $stmt->bind_param('ssi', $nombre, $email, $id);
        }
        if (!$stmt->execute()) throw new Exception('Error al actualizar usuario: ' . $stmt->error);
        echo "Usuario actualizado.";
        exit;
    }

    if ($action === 'delete') {
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) throw new Exception('ID inválido.');
        $sql = "DELETE FROM usuarios WHERE id = ?";
        $stmt = mysqli_prepare_checked($conn, $sql);
        $stmt->bind_param('i', $id);
        if (!$stmt->execute()) throw new Exception('Error al eliminar usuario: ' . $stmt->error);
        echo "Usuario eliminado.";
        exit;
    }

    // listar / buscar con paginación
    $page = max(1, (int)($_GET['page'] ?? 1));
    $limit = max(1, min(100, (int)($_GET['limit'] ?? 10)));
    $offset = ($page - 1) * $limit;
    $search = trim($_GET['q'] ?? '');

    if ($search !== '') {
        $like = "%{$search}%";
        $countStmt = mysqli_prepare_checked($conn, "SELECT COUNT(*) FROM usuarios WHERE nombre LIKE ? OR email LIKE ?");
        $countStmt->bind_param('ss', $like, $like);
        $countStmt->execute();
        $countStmt->bind_result($total);
        $countStmt->fetch();
        $countStmt->close();

        $sql = "SELECT id, nombre, email, creado_en FROM usuarios WHERE nombre LIKE ? OR email LIKE ? ORDER BY id DESC LIMIT ? OFFSET ?";
        $stmt = mysqli_prepare_checked($conn, $sql);
        $stmt->bind_param('ssii', $like, $like, $limit, $offset);
    } else {
        $res = $conn->query("SELECT COUNT(*) as c FROM usuarios");
        $total = (int)$res->fetch_assoc()['c'];

        $sql = "SELECT id, nombre, email, creado_en FROM usuarios ORDER BY id DESC LIMIT ? OFFSET ?";
        $stmt = mysqli_prepare_checked($conn, $sql);
        $stmt->bind_param('ii', $limit, $offset);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    echo "<h2>Usuarios (Página {$page})</h2>";
    echo "<form method='get'><input type='text' name='q' placeholder='buscar nombre o email' value='".htmlspecialchars($search,ENT_QUOTES)."'><button>Buscar</button></form>";
    echo "<table border='1' cellpadding='6'><tr><th>ID</th><th>Nombre</th><th>Email</th><th>Creado</th><th>Acciones</th></tr>";
    while ($row = $result->fetch_assoc()) {
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
