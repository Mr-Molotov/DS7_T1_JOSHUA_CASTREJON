<?php
declare(strict_types=1);
require_once __DIR__ . '/SistemaGestionEstudiantes.php';

// Archivo de datos JSON (persistencia)
$dataFile = __DIR__ . DIRECTORY_SEPARATOR . 'data_estudiantes.json';

$sistema = new SistemaGestionEstudiantes();
// Cargar datos si existen
if (file_exists($dataFile)) {
    $sistema->cargarDesdeArchivo($dataFile);
}

// Procesar acciones (antes de imprimir HTML)
// 1) Agregar estudiante desde formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_student') {
    $id = (int)($_POST['id'] ?? 0);
    $nombre = trim((string)($_POST['nombre'] ?? ''));
    $edad = (int)($_POST['edad'] ?? 0);
    $carrera = trim((string)($_POST['carrera'] ?? ''));
    $materiasRaw = trim((string)($_POST['materias'] ?? ''));

    $materias = [];
    if ($materiasRaw !== '') {
        // Esperamos líneas con formato: Materia|Calificacion  (acepta : , |)
        $lines = preg_split('/\r?\n/', $materiasRaw);
        foreach ($lines as $ln) {
            $ln = trim($ln);
            if ($ln === '') continue;
            // separar por | o : o ,
            $parts = preg_split('/\s*[|:,]\s*/', $ln, 2);
            if (count($parts) === 2) {
                $mat = trim($parts[0]);
                $nota = (float)trim($parts[1]);
                $materias[$mat] = $nota;
            }
        }
    }

    try {
        $nuevo = new Estudiante($id, $nombre, $edad, $carrera, $materias);
        $sistema->agregarEstudiante($nuevo);
        $sistema->guardarAArchivo($dataFile);
        // Evitar reenvío de formulario
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    } catch (Exception $ex) {
        $errorMsg = $ex->getMessage();
    }
}

// 2) Graduar por parámetro GET
if (isset($_GET['graduar_id'])) {
    $gId = (int)$_GET['graduar_id'];
    try {
        $sistema->graduarEstudiante($gId);
        $sistema->guardarAArchivo($dataFile);
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    } catch (Exception $ex) {
        $errorMsg = $ex->getMessage();
    }
}

// Busqueda (GET)
$searchQuery = trim((string)($_GET['q'] ?? ''));
$searchResults = $searchQuery !== '' ? $sistema->buscarEstudiantes($searchQuery) : null;

// Datos para mostrar
$estudiantes = $sistema->listarEstudiantes();
$ranking = $sistema->generarRanking();
$reporte = $sistema->generarReporteRendimiento();
$statsCarrera = $sistema->estadisticasPorCarrera();
$mejor = $sistema->obtenerMejorEstudiante();

?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Sistema de Gestión de Estudiantes — TALLER_5</title>
<style>
    body{font-family: Arial, sans-serif; margin:20px;}
    table{width:100%;border-collapse:collapse;margin-bottom:20px}
    th,td{border:1px solid #ddd;padding:8px;text-align:left}
    th{background:#f4f4f4}
    .error{color:#e74c3c}
    .success{color:#27ae60}
    form textarea{width:100%;height:80px}
</style>
</head>
<body>
    <h1>📚 Sistema de Gestión de Estudiantes</h1>

    <?php if (!empty($errorMsg)): ?>
        <p class="error">Error: <?= htmlspecialchars($errorMsg) ?></p>
    <?php endif; ?>

    <h2>Agregar Estudiante</h2>
    <form method="post">
        <input type="hidden" name="action" value="add_student">
        <label>ID: <input type="number" name="id" required></label><br><br>
        <label>Nombre: <input type="text" name="nombre" required></label><br><br>
        <label>Edad: <input type="number" name="edad" required></label><br><br>
        <label>Carrera: <input type="text" name="carrera" required></label><br><br>
        <label>Materias (una por línea con formato <code>Materia|Calificación</code>):</label>
        <textarea name="materias" placeholder="Programación|95\nMatemáticas|88"></textarea>
        <br>
        <button type="submit">Agregar estudiante</button>
    </form>

    <h2>Buscar Estudiantes</h2>
    <form method="get">
        <input type="text" name="q" placeholder="Nombre o carrera (parcial)" value="<?= htmlspecialchars($searchQuery) ?>">
        <button type="submit">Buscar</button>
        <a href="<?= $_SERVER['PHP_SELF'] ?>">Limpiar</a>
    </form>

    <?php if ($searchResults !== null): ?>
        <h3>Resultados de búsqueda (<?= count($searchResults) ?>)</h3>
        <ul>
            <?php foreach ($searchResults as $r): ?>
                <li><?= htmlspecialchars((string)$r) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <h2>Lista de Estudiantes (<?= count($estudiantes) ?>)</h2>
    <table>
        <tr><th>ID</th><th>Nombre</th><th>Edad</th><th>Carrera</th><th>Promedio</th><th>Flags</th><th>Acciones</th></tr>
        <?php foreach ($estudiantes as $e): $d = $e->obtenerDetalles(); ?>
            <tr>
                <td><?= $d['id'] ?></td>
                <td><?= htmlspecialchars($d['nombre']) ?></td>
                <td><?= $d['edad'] ?></td>
                <td><?= htmlspecialchars($d['carrera']) ?></td>
                <td><?= number_format($d['promedio'],2) ?></td>
                <td><?= htmlspecialchars(implode(', ', $d['flags']) ?: 'Ninguno') ?></td>
                <td><a href="?graduar_id=<?= $d['id'] ?>" onclick="return confirm('Graduar estudiante ID <?= $d['id'] ?>?')">Graduar</a></td>
            </tr>
        <?php endforeach; ?>
    </table>

    <h2>🏆 Mejor Estudiante</h2>
    <p><?= $mejor ? htmlspecialchars((string)$mejor) : '—' ?></p>

    <h2>📊 Reporte de Rendimiento por Materia</h2>
    <table>
        <tr><th>Materia</th><th>Promedio</th><th>Máximo</th><th>Mínimo</th><th>Cantidad</th></tr>
        <?php foreach ($reporte as $mat => $datos): ?>
            <tr>
                <td><?= htmlspecialchars($mat) ?></td>
                <td><?= number_format($datos['promedio'],2) ?></td>
                <td><?= $datos['maximo'] ?></td>
                <td><?= $datos['minimo'] ?></td>
                <td><?= $datos['cantidad'] ?></td>
            </tr>
        <?php endforeach; ?>
    </table>

    <h2>🎓 Ranking</h2>
    <ol>
        <?php foreach ($ranking as $r): ?>
            <li><?= htmlspecialchars((string)$r) ?></li>
        <?php endforeach; ?>
    </ol>

    <h2>📚 Estadísticas por Carrera</h2>
    <table>
        <tr><th>Carrera</th><th>Cantidad</th><th>Promedio</th><th>Mejor estudiante</th></tr>
        <?php foreach ($statsCarrera as $c => $info): ?>
            <tr>
                <td><?= htmlspecialchars($c) ?></td>
                <td><?= $info['cantidad'] ?></td>
                <td><?= number_format($info['promedio'],2) ?></td>
                <td><?= $info['mejor'] instanceof Estudiante ? htmlspecialchars($info['mejor']->getNombre()) : '—' ?></td>
            </tr>
        <?php endforeach; ?>
    </table>

</body>
</html>
