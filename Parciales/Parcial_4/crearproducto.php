<?php
require_once "database.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    try {

        $nombre = mysqli_real_escape_string($conn, $_POST['nombre']);
        $categoria = mysqli_real_escape_string($conn, $_POST['categoria']);
        $precio = mysqli_real_escape_string($conn, $_POST['precio']);
        $cantidad = mysqli_real_escape_string($conn, $_POST['cantidad']);

        $sql = "INSERT INTO productos (nombre, precio, cantidad) VALUES (?, ?, ?)";

        if (!$stmt = mysqli_prepare($conn, $sql)) {
            throw new Exception("Error preparando consulta: " . mysqli_error($conn));
        }

        mysqli_stmt_bind_param($stmt, "sd", $nombre, $precio, $cantidad);

        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Error ejecutando consulta: " . mysqli_error($conn));
        }

        echo "Producto creado con éxito.";

    } catch (Exception $e) {
        echo "Ocurrió un error: " . $e->getMessage();
    }
    mysqli_stmt_close($stmt);
}

mysqli_close($conn);
?>

</form>
<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
    <div>
        <label for="nombre">Nombre del Producto:</label>
        <input type="text" name="nombre" id="nombre" required>
    </div>
    <div>
        <label for="categoria">Categoria:</label>
        <input type="text" name="categoria" id="categoria" required>
    </div>
    <div>
        <label for="precio">Precio del Producto:</label>
        <input type="number" step="0.01" name="precio" id="precio" required>
    </div>
    <div>
        <label for="cantidad">Cantidad disponible</label>
        <input type="number" name="cantidad" id="cantidad" required>
    </div>
    <input type="submit" value="Crear Producto">
    <br>
    <a href="index.php">Regresar al inicio</a>
