<?php
require 'config_session.php';
require 'data.php';

if (!isset($_SESSION['usuario'])) {
    header('Location: index.php');
    exit();
}

$usuario = $_SESSION['usuario'];
$role = $_SESSION['role'];

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
</head>
<body style="font-family: Arial, sans-serif; margin: 50px; text-align: center;">
    <h2>Bienvenido, <?php echo ucfirst($usuario); ?></h2>

    <?php if ($role === 'profesor'): ?>
        <h3>Calificaciones de los estudiantes:</h3>
        <table border="5" align="center" cellmargin="5" cellpadding="5" style="border-color: blue;">
            <tr>
                <th>Estudiante</th>
                <th>Calificación</th>
            </tr>
            <?php foreach ($usuarios as $nombre => $info): ?>
                <?php if ($info['role'] === 'estudiante'): ?>
                    <tr>
                        <td><?php echo $nombre; ?></td>
                        <td><?php echo $info['grade']; ?></td>
                    </tr>
                <?php endif; ?>
            <?php endforeach; ?>
        </table><br><br>
    <?php elseif ($role === 'estudiante'): ?>
        <h3>Tu calificación es:</h3>
        <p><?php echo $usuarios[$usuario]['grade']; ?></p>
    <?php endif; ?>

    <a href="logout.php">Cerrar sesión</a><br><br>
</body>
</html>