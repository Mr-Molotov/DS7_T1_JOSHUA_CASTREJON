<?php
require_once "database.php";

$sql = "SELECT id, nombre, categoria, precio, cantidad FROM productos WHERE id=?";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $nombre = $_POST['nombre'];
    $categoria = $_POST['categoria'];
    $precio = $_POST['precio'];
    $cantidad = $_POST['cantidad'];

    $sql = "UPDATE productos SET nombre=?, categoria=?, precio=?, cantidad=? WHERE id=?";
    
    if($stmt = mysqli_prepare($conn, $sql)){
        mysqli_stmt_bind_param($stmt, "ssdii", $nombre, $categoria, $precio, $cantidad, $id);
        
        if(mysqli_stmt_execute($stmt)){
            echo "Producto actualizado exitosamente.";
        } else{
            echo "ERROR: No se pudo ejecutar la actualización. " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    } else{
        echo "ERROR: No se pudo preparar la consulta. " . mysqli_error($conn);
    }
    
    mysqli_close($conn);
} else{
    echo "Solicitud inválida.";
}
?>

<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
    <div>
        <label>ID</label>
        <input type="number" name="id" required></div>
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
    <input type="submit" value="Actualizar Usuario">
    <br>
    <a href="index.php">Regresar al inicio</a>