<?php
require 'config_session.php';
require 'data.php';

$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = $_POST['usuario'];
    $password = $_POST['password'];

    // Validaciones
    if (!preg_match("/^[a-zA-Z0-9]{3,}$/", $usuario)) {
        $errores[] = "El nombre de usuario debe tener al menos 3 caracteres y solo contener letras y números.";
    }

    if (strlen($password) < 5) {
        $errores[] = "La contraseña debe tener al menos 5 caracteres.";
    }

    // Verificación de credenciales
    if (empty($errores)) {
        if (isset($usuarios[$usuario]) && $usuarios[$usuario]['password'] === $password) {
            $_SESSION['usuario'] = $usuario;
            $_SESSION['role'] = $usuarios[$usuario]['role'];
            header('Location: dashboard.php');
            exit();
        } else {
            $errores[] = "Credenciales incorrectas.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
</head>
<body style="font-family: Arial, sans-serif; margin: 50px; text-align: center;">
    <h2>Inicio de sesión</h2>
    <?php if (!empty($errores)): ?>
        <ul>
            <?php foreach ($errores as $error): ?>
                <li><?php echo $error; ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
    
    <form method="POST" action="index.php">
        <label for="usuario" style="padding-left: 30px;">Usuario:</label>
        <input type="text" name="usuario" id="usuario" required><br><br>
        <label for="password">Contraseña:</label>
        <input type="password" name="password" id="password" required><br><br>
        <button type="submit" style="background-color: blue; color: azure; margin: 5px; border-radius: 5px;
                                     cursor: pointer; height:45px; width: 75px;">Iniciar Sesión</button>
    </form>
</body>
</html>