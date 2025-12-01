<?php
require_once "config_mysqli.php";

// 1. Productos que tienen un precio mayor al promedio de su categoría
$sql = "SELECT p.nombre, p.precio, c.nombre as categoria,
        (SELECT AVG(precio) FROM productos WHERE categoria_id = p.categoria_id) as promedio_categoria
        FROM productos p
        JOIN categorias c ON p.categoria_id = c.id
        WHERE p.precio > (
            SELECT AVG(precio)
            FROM productos p2
            WHERE p2.categoria_id = p.categoria_id
        )";

$result = mysqli_query($conn, $sql);

if ($result) {
    echo "<h3>Productos con precio mayor al promedio de su categoría:</h3>";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "Producto: {$row['nombre']}, Precio: {$row['precio']}, ";
        echo "Categoría: {$row['categoria']}, Promedio categoría: {$row['promedio_categoria']}<br>";
    }
    mysqli_free_result($result);
}

// 2. Clientes con compras superiores al promedio
$sql = "SELECT c.nombre, c.email,
        (SELECT SUM(total) FROM ventas WHERE cliente_id = c.id) as total_compras,
        (SELECT AVG(total) FROM ventas) as promedio_ventas
        FROM clientes c
        WHERE (
            SELECT SUM(total)
            FROM ventas
            WHERE cliente_id = c.id
        ) > (
            SELECT AVG(total)
            FROM ventas
        )";

$result = mysqli_query($conn, $sql);

if ($result) {
    echo "<h3>Clientes con compras superiores al promedio:</h3>";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "Cliente: {$row['nombre']}, Total compras: {$row['total_compras']}, ";
        echo "Promedio general: {$row['promedio_ventas']}<br>";
    }
    mysqli_free_result($result);
}

//3.Encontrar los productos que nunca se han vendido.
$sql = "SELECT p.nombre AS Producto, p.descripcion AS Descripción
          FROM productos p
          LEFT JOIN detalles_venta dv ON p.id = dv.producto_id
          WHERE dv.producto_id IS NULL";
$result = mysqli_query($conn, $sql);

if ($result) {
    echo "<h3>Productos que no se han vendido:</h3>";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "Producto: " . $row["Producto"] . " - Descripción: " . $row["Descripción"] . "<br>";
    }
    mysqli_free_result($result);
}

//4.Listar las categorías con el número de productos y el valor total del inventario.
$sql = "SELECT c.nombre, COUNT(p.id) as num_productos, SUM(p.precio * p.stock) as valor_inventario 
        FROM categorias c 
        JOIN productos p ON c.id = p.categoria_id 
        GROUP BY c.id";
$result = mysqli_query($conn, $sql);

if ($result) {
    echo "<h3>Lista de los productos por categorías:</h3>";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "Categoría: " . $row["nombre"] . " - Número de productos: " . $row["num_productos"] . " - Valor de inventario: $" . $row["valor_inventario"] . "<br>";
    }
    mysqli_free_result($result);
}

//5.Encontrar los clientes que han comprado todos los productos de una categoría específica.
$categoria_id = 1; // ID de la categoría a analizar (puede ser dinámico)
$sql = "SELECT DISTINCT c.nombre 
        FROM clientes c
        JOIN ventas v ON c.id = v.cliente_id
        JOIN detalles_venta dv ON v.id = dv.venta_id
        JOIN productos p ON dv.producto_id = p.id
        WHERE p.categoria_id = $categoria_id
        GROUP BY c.id
        HAVING COUNT(DISTINCT p.id) = (
            SELECT COUNT(*) 
            FROM productos 
            WHERE categoria_id = $categoria_id
        )";

$result = mysqli_query($conn, $sql);

if ($result) {
    echo "<h3>Clientes que han comprado todos los productos de la categoría ID {$categoria_id}:</h3>";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "Cliente: {$row['nombre']}<br>";
    }
    mysqli_free_result($result);
}

//6.Calcular el porcentaje de ventas de cada producto respecto al total de ventas.
$sql = "SELECT p.nombre AS producto,
            SUM(dv.subtotal) AS ventas_producto,
            (SUM(dv.subtotal) / (SELECT SUM(subtotal) FROM detalles_venta) * 100) AS porcentaje_ventas
        FROM productos p
        JOIN detalles_venta dv ON p.id = dv.producto_id
        GROUP BY p.id";
$result = mysqli_query($conn, $sql);

if ($result) {
    echo "<h3>Porcentaje de ventas de cada producto respecto al total de ventas:</h3>";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "Producto: {$row['producto']}, Total ventas: $" . "{$row['ventas_producto']}, Porcentaje: " . round($row['porcentaje_ventas'], 2) . "%<br>";
    }
    mysqli_free_result($result);
}
mysqli_close($conn);
?>
        