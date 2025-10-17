<?php include 'config_sesion.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <title>Productos</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
<div class="container">
<h2>Lista de Productos</h2>
<?php
$productos = [
    1 => ['nombre' => 'Camisa', 'precio' => 20],
    2 => ['nombre' => 'Pantalón', 'precio' => 35],
    3 => ['nombre' => 'Zapatos', 'precio' => 50],
    4 => ['nombre' => 'Gorra', 'precio' => 15],
    5 => ['nombre' => 'Chaqueta', 'precio' => 60],
];

foreach ($productos as $id => $producto) {
    echo "<div class='producto'>
            <strong>" . htmlspecialchars($producto['nombre']) . "</strong><br>
            Precio: $" . htmlspecialchars($producto['precio']) . "<br>
            <a class='boton' href='agregar_al_carrito.php?id=$id'>Agregar al carrito</a>
          </div>";
}
?>
<a href="ver_carrito.php">🛒 Ver Carrito</a>
</div>
</body>
</html>
