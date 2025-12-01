<?php
// pdo/libros.php
require_once 'config.php';

function sanitize($s){ return trim($s); }

$action = $_REQUEST['action'] ?? 'list';

try {
    if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $titulo = sanitize($_POST['titulo'] ?? '');
        $autor = sanitize($_POST['autor'] ?? '');
        $isbn = sanitize($_POST['isbn'] ?? null);
        $anio = isset($_POST['anio']) ? (int)$_POST['anio'] : null;
        $cantidad = max(0, (int)($_POST['cantidad'] ?? 0));

        if (!$titulo || !$autor) throw new Exception('Título y autor son obligatorios.');

        $stmt = $pdo->prepare("INSERT INTO libros (titulo, autor, isbn, anio_publicacion, cantidad_disponible) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$titulo, $autor, $isbn, $anio, $cantidad]);
        echo "Libro creado ID: " . $pdo->lastInsertId();
        exit;
    }

    if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) throw new Exception('ID inválido.');
        $titulo = sanitize($_POST['titulo'] ?? '');
        $autor = sanitize($_POST['autor'] ?? '');
        $isbn = sanitize($_POST['isbn'] ?? null);
        $anio = isset($_POST['anio']) ? (int)$_POST['anio'] : null;
        $cantidad = max(0, (int)($_POST['cantidad'] ?? 0));

        $stmt = $pdo->prepare("UPDATE libros SET titulo=?, autor=?, isbn=?, anio_publicacion=?, cantidad_disponible=? WHERE id=?");
        $stmt->execute([$titulo, $autor, $isbn, $anio, $cantidad, $id]);
        echo "Libro actualizado.";
        exit;
    }

    if ($action === 'delete') {
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) throw new Exception('ID inválido.');
        $stmt = $pdo->prepare("DELETE FROM libros WHERE id = ?");
        $stmt->execute([$id]);
        echo "Libro eliminado.";
        exit;
    }

    // listar / buscar paginación
    $page = max(1, (int)($_GET['page'] ?? 1));
    $limit = max(1, min(100, (int)($_GET['limit'] ?? 10)));
    $offset = ($page - 1) * $limit;
    $search = trim($_GET['q'] ?? '');

    if ($search !== '') {
        $like = "%{$search}%";
        $total = $pdo->prepare("SELECT COUNT(*) FROM libros WHERE titulo LIKE ? OR autor LIKE ? OR isbn LIKE ?");
        $total->execute([$like, $like, $like]);
        $total = (int)$total->fetchColumn();

        $stmt = $pdo->prepare("SELECT id, titulo, autor, isbn, anio_publicacion, cantidad_disponible FROM libros WHERE titulo LIKE ? OR autor LIKE ? OR isbn LIKE ? ORDER BY id DESC LIMIT ? OFFSET ?");
        $stmt->execute([$like, $like, $like, $limit, $offset]);
    } else {
        $total = (int)$pdo->query("SELECT COUNT(*) FROM libros")->fetchColumn();
        $stmt = $pdo->prepare("SELECT id, titulo, autor, isbn, anio_publicacion, cantidad_disponible FROM libros ORDER BY id DESC LIMIT ? OFFSET ?");
        $stmt->execute([$limit, $offset]);
    }

    $rows = $stmt->fetchAll();

    echo "<h2>Libros (Página {$page})</h2>";
    echo "<form method='get'><input type='text' name='q' placeholder='buscar' value='".htmlspecialchars($search,ENT_QUOTES)."'><button>Buscar</button></form>";
    echo "<table border='1' cellpadding='6'><tr><th>ID</th><th>Título</th><th>Autor</th><th>ISBN</th><th>Año</th><th>Cantidad</th><th>Acciones</th></tr>";
    foreach($rows as $row) {
        echo "<tr>
            <td>{$row['id']}</td>
            <td>".htmlspecialchars($row['titulo'])."</td>
            <td>".htmlspecialchars($row['autor'])."</td>
            <td>".htmlspecialchars($row['isbn'])."</td>
            <td>".htmlspecialchars($row['anio_publicacion'])."</td>
            <td>".htmlspecialchars($row['cantidad_disponible'])."</td>
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
