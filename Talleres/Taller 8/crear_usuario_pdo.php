<?php
require_once "config_pdo.php";
require_once "log_helper.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    try {
        $nombre = $_POST['nombre'];
        $email = $_POST['email'];

        $sql = "INSERT INTO usuarios (nombre, email) VALUES (:nombre, :email)";
        $stmt = $pdo->prepare($sql);

        if (!$stmt->execute([':nombre' => $nombre, ':email' => $email])) {
            $error = $stmt->errorInfo()[2];
            throw new Exception("Error ejecutando consulta: $error");
        }

        echo "Usuario creado con éxito.";

    } catch (Exception $e) {
        registrar_error("PDO - " . $e->getMessage());
        echo "Ocurrió un error: " . $e->getMessage();
    }

    unset($stmt);
}

unset($pdo);
?>

<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
    <div><label>Nombre</label><input type="text" name="nombre" required></div>
    <div><label>Email</label><input type="email" name="email" required></div>
    <input type="submit" value="Crear Usuario">
</form>
