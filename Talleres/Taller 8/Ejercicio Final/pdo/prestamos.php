<?php
// pdo/prestamos.php
require_once 'config.php';

$action = $_REQUEST['action'] ?? 'list';

try {
    if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $usuario_id = (int)($_POST['usuario_id'] ?? 0);
        $libro_id = (int)($_POST['libro_id'] ?? 0);
        if (!$usuario_id || !$libro_id) throw new Exception('Usuario y libro requeridos.');

        $pdo->beginTransaction();

        // SELECT FOR UPDATE emulación: use SELECT ... FOR UPDATE within transaction
        $stmt = $pdo->prepare("SELECT cantidad_disponible FROM libros WHERE id = ? FOR UPDATE");
        $stmt->execute([$libro_id]);
        $row = $stmt->fetch();
        if (!$row) throw new Exception('Libro no encontrado.');
        $cantidad = (int)$row['cantidad_disponible'];
        if ($cantidad <= 0) {
            $pdo->rollBack();
            throw new Exception('No hay ejemplares disponibles.');
        }

        $ins = $pdo->prepare("INSERT INTO prestamos (usuario_id, libro_id) VALUES (?, ?)");
        $ins->execute([$usuario_id, $libro_id]);

        $upd = $pdo->prepare("UPDATE libros SET cantidad_disponible = cantidad_disponible - 1 WHERE id = ?");
        $upd->execute([$libro_id]);

        $pdo->commit();
        echo "Préstamo registrado ID: " . $pdo->lastInsertId();
        exit;
    }

    if ($action === 'return' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $prestamo_id = (int)($_POST['prestamo_id'] ?? 0);
        if (!$prestamo_id) throw new Exception('ID préstamo inválido.');

        $pdo->beginTransaction();

        $stmt = $pdo->prepare("SELECT libro_id, estado FROM prestamos WHERE id = ? FOR UPDATE");
        $stmt->execute([$prestamo_id]);
        $row = $stmt->fetch();
        if (!$row) throw new Exception('Préstamo no encontrado.');
        if ($row['estado'] !== 'activo') {
            $pdo->rollBack();
            throw new Exception('Préstamo ya devuelto.');
        }

        $now = date('Y-m-d H:i:s');
        $upd = $pdo->prepare("UPDATE prestamos SET fecha_devolucion = ?, estado = 'devuelto' WHERE id = ?");
        $upd->execute([$now, $prestamo_id]);

        $inc = $pdo->prepare("UPDATE libros SET cantidad_disponible = cantidad_disponible + 1 WHERE id = ?");
        $inc->execute([$row['libro_id']]);

        $pdo->commit();
        echo "Devolución registrada.";
        exit;
    }

    // Listar activos con JOIN
    $page = max(1, (int)($_GET['page'] ?? 1));
    $limit = max(1, min(100, (int)($_GET['limit'] ?? 10)));
    $offset = ($page - 1) * $limit;

    $total = (int)$pdo->query("SELECT COUNT(*) FROM prestamos WHERE estado = 'activo'")->fetchColumn();

    $stmt = $pdo->prepare("SELECT p.id, p.usuario_id, p.libro_id, p.fecha_prestamo, u.nombre AS usuario_nombre, l.titulo AS libro_titulo
        FROM prestamos p
        JOIN usuarios u ON p.usuario_id = u.id
        JOIN libros l ON p.libro_id = l.id
        WHERE p.estado = 'activo'
        ORDER BY p.fecha_prestamo DESC
        LIMIT ? OFFSET ?");
    $stmt->execute([$limit, $offset]);
    $rows = $stmt->fetchAll();

    echo "<h2>Préstamos activos (Página {$page})</h2>";
    echo "<table border='1' cellpadding='6'><tr><th>ID</th><th>Usuario</th><th>Libro</th><th>Fecha</th><th>Acción</th></tr>";
    foreach($rows as $row) {
        echo "<tr>
            <td>{$row['id']}</td>
            <td>".htmlspecialchars($row['usuario_nombre'])."</td>
            <td>".htmlspecialchars($row['libro_titulo'])."</td>
            <td>{$row['fecha_prestamo']}</td>
            <td>
              <form method='post' style='display:inline'>
                <input type='hidden' name='action' value='return'>
                <input type='hidden' name='prestamo_id' value='{$row['id']}'>
                <button type='submit'>Registrar devolución</button>
              </form>
            </td>
        </tr>";
    }
    echo "</table>";

    $totalPages = (int)ceil($total / $limit);
    echo "<p>Total: {$total} | Páginas: {$totalPages}</p>";
    for ($p=1;$p<=$totalPages;$p++){
        $link = "?page={$p}&limit={$limit}";
        echo ($p==$page) ? "<strong>{$p}</strong> " : "<a href='{$link}'>{$p}</a> ";
    }

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    http_response_code(400);
    echo "Error: " . htmlspecialchars($e->getMessage());
}
