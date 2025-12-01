<?php
// mysqli/prestamos.php
require_once 'config.php';

$action = $_REQUEST['action'] ?? 'list';

try {
    if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $usuario_id = (int)($_POST['usuario_id'] ?? 0);
        $libro_id = (int)($_POST['libro_id'] ?? 0);

        if (!$usuario_id || !$libro_id) throw new Exception('Usuario y libro requeridos.');

        // START TRANSACTION
        $conn->begin_transaction();

        // Check disponible
        $stmt = mysqli_prepare_checked($conn, "SELECT cantidad_disponible FROM libros WHERE id = ? FOR UPDATE");
        $stmt->bind_param('i', $libro_id);
        $stmt->execute();
        $stmt->bind_result($cantidad);
        if (!$stmt->fetch()) {
            $stmt->close();
            throw new Exception('Libro no encontrado.');
        }
        $stmt->close();

        if ($cantidad <= 0) {
            $conn->rollback();
            throw new Exception('No hay ejemplares disponibles.');
        }

        // Insert prestamo
        $stmtIns = mysqli_prepare_checked($conn, "INSERT INTO prestamos (usuario_id, libro_id) VALUES (?, ?)");
        $stmtIns->bind_param('ii', $usuario_id, $libro_id);
        if (!$stmtIns->execute()) {
            $conn->rollback();
            throw new Exception('Error al crear préstamo: ' . $stmtIns->error);
        }

        // Decrement book
        $newCantidad = $cantidad - 1;
        $stmtUpd = mysqli_prepare_checked($conn, "UPDATE libros SET cantidad_disponible = ? WHERE id = ?");
        $stmtUpd->bind_param('ii', $newCantidad, $libro_id);
        if (!$stmtUpd->execute()) {
            $conn->rollback();
            throw new Exception('Error al actualizar cantidad: ' . $stmtUpd->error);
        }

        $conn->commit();
        echo "Préstamo registrado ID: " . $stmtIns->insert_id;
        exit;
    }

    if ($action === 'return' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $prestamo_id = (int)($_POST['prestamo_id'] ?? 0);
        if (!$prestamo_id) throw new Exception('ID préstamo inválido.');

        $conn->begin_transaction();

        // Obtener prestamo
        $stmt = mysqli_prepare_checked($conn, "SELECT libro_id, estado FROM prestamos WHERE id = ? FOR UPDATE");
        $stmt->bind_param('i', $prestamo_id);
        $stmt->execute();
        $stmt->bind_result($libro_id, $estado);
        if (!$stmt->fetch()) {
            $stmt->close();
            throw new Exception('Préstamo no encontrado.');
        }
        $stmt->close();

        if ($estado !== 'activo') {
            $conn->rollback();
            throw new Exception('Préstamo ya devuelto.');
        }

        // Update prestamo
        $now = date('Y-m-d H:i:s');
        $stmtUpd = mysqli_prepare_checked($conn, "UPDATE prestamos SET fecha_devolucion = ?, estado = 'devuelto' WHERE id = ?");
        $stmtUpd->bind_param('si', $now, $prestamo_id);
        if (!$stmtUpd->execute()) {
            $conn->rollback();
            throw new Exception('Error al registrar devolución: ' . $stmtUpd->error);
        }

        // Incrementar cantidad libro
        $stmtInc = mysqli_prepare_checked($conn, "UPDATE libros SET cantidad_disponible = cantidad_disponible + 1 WHERE id = ?");
        $stmtInc->bind_param('i', $libro_id);
        if (!$stmtInc->execute()) {
            $conn->rollback();
            throw new Exception('Error al actualizar libro: ' . $stmtInc->error);
        }

        $conn->commit();
        echo "Devolución registrada.";
        exit;
    }

    // Listar préstamos activos con JOIN (paginación)
    $page = max(1, (int)($_GET['page'] ?? 1));
    $limit = max(1, min(100, (int)($_GET['limit'] ?? 10)));
    $offset = ($page - 1) * $limit;

    $res = $conn->query("SELECT COUNT(*) as c FROM prestamos WHERE estado = 'activo'");
    $total = (int)$res->fetch_assoc()['c'];

    $sql = "SELECT p.id, p.usuario_id, p.libro_id, p.fecha_prestamo, u.nombre as usuario_nombre, l.titulo as libro_titulo
            FROM prestamos p
            JOIN usuarios u ON p.usuario_id = u.id
            JOIN libros l ON p.libro_id = l.id
            WHERE p.estado = 'activo'
            ORDER BY p.fecha_prestamo DESC
            LIMIT ? OFFSET ?";
    $stmt = mysqli_prepare_checked($conn, $sql);
    $stmt->bind_param('ii', $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();

    echo "<h2>Préstamos activos (Página {$page})</h2>";
    echo "<table border='1' cellpadding='6'><tr><th>ID</th><th>Usuario</th><th>Libro</th><th>Fecha</th><th>Acción</th></tr>";
    while ($row = $result->fetch_assoc()) {
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
    if ($conn->$in_transaction) $conn->rollback();
    http_response_code(400);
    echo "Error: " . htmlspecialchars($e->getMessage());
}
