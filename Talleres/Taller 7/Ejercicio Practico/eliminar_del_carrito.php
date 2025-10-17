<?php
include 'config_sesion.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if ($id && isset($_SESSION['carrito'][$id])) {
    unset($_SESSION['carrito'][$id]);
}

// En lugar de redirigir, renderiza solo el contenido del carrito
include 'actualizar_carrito.php';
