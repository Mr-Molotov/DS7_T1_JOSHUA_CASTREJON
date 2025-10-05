<?php
$paginaActual = 'contacto'; // Cambia esto según el archivo
require_once 'plantillas/funciones.php';
$tituloPagina = obtenerTituloPagina($paginaActual);
include 'plantillas/encabezado.php';
?>

<h2>Contenido de la Página de Contacto</h2>
<p>Mas información contactar a Joshua Castrejón</p>

<?php
include 'plantillas/pie_pagina.php';
?>