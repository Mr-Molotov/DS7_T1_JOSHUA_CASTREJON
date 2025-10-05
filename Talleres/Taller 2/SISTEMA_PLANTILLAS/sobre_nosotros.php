<?php
$paginaActual = 'sobre_nosotros'; // Cambia esto según el archivo
require_once 'plantillas/funciones.php';
$tituloPagina = obtenerTituloPagina($paginaActual);
include 'plantillas/encabezado.php';
?>

<h2>Contenido de la Página de Información</h2>
<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Inventore reprehenderit error consequatur veritatis minus numquam, assumenda sit maxime, sequi dolorem veniam voluptas voluptate dolores accusamus quae labore doloribus mollitia quam?</p>

<?php
include 'plantillas/pie_pagina.php';
?>