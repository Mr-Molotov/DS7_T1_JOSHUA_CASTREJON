<?php
// mysqli/libros.php
require_once 'config.php';

// Helpers
function sanitize_text($s) {
    return trim($s);
}

$action = $_REQUEST['action'] ?? 'list';

try {
    if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $titulo = sanitize_text($_POST['titulo'] ?? '');
        $autor = sanitize_text($_POST['autor'] ?? '');
        $isbn = sanitize_text($_POST['isbn'] ?? null);
        $anio = isset($_POST['anio']) ? (int)$_POST['anio'] : null;
        $cantidad = max(0, (int)($_POST['cantidad'] ?? 0));

        if (!$titulo || !$autor) throw new Exception('Título y autor son obligatorios.');

        $sql = "INSERT INTO libros (titulo, autor, isbn, anio_publicacion, cantidad_disponible) VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare_checked($conn, $sql);
        $stmt->bind_param('sssii', $titulo, $autor, $isbn, $anio, $cantidad);
        if (!$stmt->execute()) throw new Exception('Error al insertar libro: ' . $stmt->error);
        echo "Libro creado con ID: " . $stmt->insert_id;
        exit;
    }

    if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) throw new Exception('ID inválido.');
        $titulo = sanitize_text($_POST['titulo'] ?? '');
        $autor = sanitize_text($_POST['autor'] ?? '');
        $isbn = sanitize_text($_POST['isbn'] ?? null);
        $anio = isset($_POST['anio']) ? (int)$_POST['anio'] : null;
        $cantidad = max(0, (int)($_POST['cantidad'] ?? 0));
        $sql = "UPDATE libros SET titulo=?, autor=?, isbn=?, anio_publicacion=?, cantidad_disponible=? WHERE id=?";
        $stmt = mysqli_prepare_checked($conn, $sql);
        $stmt->bind_param('sssiii', $titulo, $autor, $isbn, $anio, $cantidad, $id);
        if (!$stmt->execute()) throw new Exception('Error al actualizar libro: ' . $stmt->error);
        echo "Libro actualizado.";
        exit;
    }

    if ($action === 'delete') {
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) throw new Exception('ID inválido.');
        $sql = "DELETE FROM libros WHERE id = ?";
        $stmt = mysqli_prepare_checked($conn, $sql);
        $stmt->bind_param('i', $id);
        if (!$stmt->execute()) throw new Exception('Error al eliminar libro: ' . $stmt->error);
        echo "Libro eliminado.";
        exit;
    }

    // Buscar / listar con paginación
    $page = max(1, (int)($_GET['page'] ?? 1));
    $limit = max(1, min(100, (int)($_GET['limit'] ?? 10)));
    $offset = ($page - 1) * $limit;

    $search = trim($_GET['q'] ?? '');
    if ($search !== '') {
        $like = "%{$search}%";
        $countStmt = mysqli_prepare_checked($conn, "SELECT COUNT(*) FROM libros WHERE titulo LIKE ? OR autor LIKE ? OR isbn LIKE ?");
        $countStmt->bind_param('sss', $like, $like, $like);
        $countStmt->execute();
        $countStmt->bind_result($total);
        $countStmt->fetch();
        $countStmt->close();

        $sql = "SELECT id, titulo, autor, isbn, anio_publicacion, cantidad_disponible FROM libros WHERE titulo LIKE ? OR autor LIKE ? OR isbn LIKE ? ORDER BY id DESC LIMIT ? OFFSET ?";
        $stmt = mysqli_prepare_checked($conn, $sql);
        $stmt->bind_param('sssii', $like, $like, $like, $limit, $offset);
    } else {
        $res = $conn->query("SELECT COUNT(*) as c FROM libros");
        $total = (int)$res->fetch_assoc()['c'];
        $sql = "SELECT id, titulo, autor, isbn, anio_publicacion, cantidad_disponible FROM libros ORDER BY id DESC LIMIT ? OFFSET ?";
        $stmt = mysqli_prepare_checked($conn, $sql);
        $stmt->bind_param('ii', $limit, $offset);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    // Mostrar HTML simple
    echo "<h2>Libros (Página {$page})</h2>";
    echo "<form method='get'><input type='text' name='q' placeholder='buscar' value='".htmlspecialchars($search,ENT_QUOTES)."'><button>Buscar</button></form>";
    echo "<table border='1' cellpadding='6'><tr><th>ID</th><th>Título</th><th>Autor</th><th>ISBN</th><th>Año</th><th>Cantidad</th><th>Acciones</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>".htmlspecialchars($row['titulo'])."</td>";
        echo "<td>".htmlspecialchars($row['autor'])."</td>";
        echo "<td>".htmlspecialchars($row['isbn'])."</td>";
        echo "<td>".htmlspecialchars($row['anio_publicacion'])."</td>";
        echo "<td>".htmlspecialchars($row['cantidad_disponible'])."</td>";
        echo "<td>
            <a href='?action=delete&id={$row['id']}' onclick='return confirm(\"Eliminar?\")'>Eliminar</a>
            </td>";
        echo "</tr>";
    }
    echo "</table>";

    // Paginación
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
