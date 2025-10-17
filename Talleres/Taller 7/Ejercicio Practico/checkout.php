<?php include 'config_sesion.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <title>Resumen de Compra</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
<div class="container">
<h2>Resumen de tu Compra</h2>
<?php
$productos = [
    1 => ['nombre' => 'Camisa', 'precio' => 20],
    2 => ['nombre' => 'Pantalón', 'precio' => 35],
    3 => ['nombre' => 'Zapatos', 'precio' => 50],
    4 => ['nombre' => 'Gorra', 'precio' => 15],
    5 => ['nombre' => 'Chaqueta', 'precio' => 60],
];

$total = 0;
if (!empty($_SESSION['carrito'])) {
    echo "<ul>";
    foreach ($_SESSION['carrito'] as $id => $cantidad) {
        $producto = $productos[$id];
        $subtotal = $producto['precio'] * $cantidad;
        $total += $subtotal;
        echo "<li>" . htmlspecialchars($producto['nombre']) . " x $cantidad = $$subtotal</li>";
    }
    echo "</ul>";
    echo "<p class='total'>Total pagado: $$total</p>";

    setcookie('nombre_usuario', 'Cliente', time() + 86400, '/', '', true, true);
    unset($_SESSION['carrito']);
} else {
    echo "<p>No hay productos en el carrito.</p>";
}
?>
<a href="productos.php">← Volver a la tienda</a>
</div>
</body>
</html>
