<?php include 'config_sesion.php';

$productos = [
    1 => ['nombre' => 'Camisa', 'precio' => 20],
    2 => ['nombre' => 'Pantalón', 'precio' => 35],
    3 => ['nombre' => 'Zapatos', 'precio' => 50],
    4 => ['nombre' => 'Gorra', 'precio' => 15],
    5 => ['nombre' => 'Chaqueta', 'precio' => 60],
];

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if ($id && isset($productos[$id])) {
    $_SESSION['carrito'][$id] = ($_SESSION['carrito'][$id] ?? 0) + 1;
}
header("Location: ver_carrito.php");
exit;
?>
