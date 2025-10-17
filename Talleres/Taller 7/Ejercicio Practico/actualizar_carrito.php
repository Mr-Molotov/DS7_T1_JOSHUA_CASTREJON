<?php
include 'config_sesion.php';

$productos = [
    1 => ['nombre' => 'Camisa', 'precio' => 20],
    2 => ['nombre' => 'Pantalón', 'precio' => 35],
    3 => ['nombre' => 'Zapatos', 'precio' => 50],
    4 => ['nombre' => 'Gorra', 'precio' => 15],
    5 => ['nombre' => 'Chaqueta', 'precio' => 60],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $cantidad = filter_input(INPUT_POST, 'cantidad', FILTER_VALIDATE_INT);
    if ($id && isset($productos[$id]) && $cantidad >= 0) {
        if ($cantidad == 0) {
            unset($_SESSION['carrito'][$id]);
        } else {
            $_SESSION['carrito'][$id] = $cantidad;
        }
    }
}

$total = 0;
if (!empty($_SESSION['carrito'])) {
    foreach ($_SESSION['carrito'] as $id => $cantidad) {
        $producto = $productos[$id];
        $subtotal = $producto['precio'] * $cantidad;
        $total += $subtotal;
        echo "<div class='carrito-item'>
                <strong>" . htmlspecialchars($producto['nombre']) . "</strong><br>
                Precio: $" . $producto['precio'] . "<br>
                Cantidad: <input type='number' value='$cantidad' onchange='actualizarCantidad($id, this.value)'><br>
                Subtotal: $$subtotal<br>
                <button class='boton' onclick='eliminarProducto($id)'>Eliminar</button>
              </div>";
    }
    echo "<p class='total'>Total: $$total</p>";
    echo "<a class='boton' href='checkout.php'>Finalizar Compra</a>";
} else {
    echo "<p>El carrito está vacío.</p>";
}
