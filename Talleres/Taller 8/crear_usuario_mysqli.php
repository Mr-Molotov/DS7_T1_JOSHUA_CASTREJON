<?php
require_once "config_mysqli.php";
require_once "log_helper.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    try {
        $nombre = mysqli_real_escape_string($conn, $_POST['nombre']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);

        $sql = "INSERT INTO usuarios (nombre, email) VALUES (?, ?)";

        if (!$stmt = mysqli_prepare($conn, $sql)) {
            throw new Exception("Error preparando consulta: " . mysqli_error($conn));
        }

        mysqli_stmt_bind_param($stmt, "ss", $nombre, $email);

        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Error ejecutando consulta: " . mysqli_error($conn));
        }

        echo "Usuario creado con éxito.";

    } catch (Exception $e) {
        registrar_error("MySQLi - " . $e->getMessage());
        echo "Ocurrió un error: " . $e->getMessage();
    }

    mysqli_stmt_close($stmt);
}

mysqli_close($conn);
?>

<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
    <div><label>Nombre</label><input type="text" name="nombre" required></div>
    <div><label>Email</label><input type="email" name="email" required></div>
    <input type="submit" value="Crear Usuario">
</form>
