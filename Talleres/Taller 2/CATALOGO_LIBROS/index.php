<?php
require_once "includes/funciones.php";
include_once "includes/header.php";

// Obtener y ordenar libros
$libros = obtenerLibros();
$libros = ordenarLibrosPorTitulo($libros);

// Mostrar lista de libros
foreach ($libros as $libro) {
    echo mostrarDetallesLibro($libro);
}

include_once "includes/footer.php";
?>
