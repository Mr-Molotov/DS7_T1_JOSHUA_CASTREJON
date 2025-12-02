<?php
require_once "database.php";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $productoId = $_POST['producto_id'] ?? null;
    $sql = "DELETE FROM productos WHERE id = ?";

    if($stmt = mysqli_prepare($conn, $sql)){
        mysqli_stmt_bind_param($stmt, "i", $productoId);
        
        if(mysqli_stmt_execute($stmt)){
            echo "Producto eliminado exitosamente.";
        } else{
            echo "ERROR: No se pudo ejecutar la eliminación. " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    } else{
        echo "ERROR: No se pudo preparar la consulta. " . mysqli_error($conn);
    }
} else {
    echo "Método no permitido.";
}
?>
<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
    <div>
        <label for="producto_id">ID del Producto a eliminar:</label>
        <input type="number" name="producto_id" id="producto_id" required>
    </div>
    <input type="submit" value="Eliminar Producto">
    <br>
    <a href="index.php">Regresar al inicio</a>
</form>