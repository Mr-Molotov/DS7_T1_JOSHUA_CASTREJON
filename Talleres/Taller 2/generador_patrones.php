<?php
// Archivo: TALLER_2/generador_patrones.php

// 1. Patrón de triángulo rectángulo con asteriscos (*)
echo "<h3>Patrón de triángulo rectángulo</h3>";
for ($i = 1; $i <= 5; $i++) {
    for ($j = 1; $j <= $i; $j++) {
        echo "* ";
    }
    echo "<br>";
}

// 2. Números impares del 1 al 20 usando while
echo "<h3>Números impares del 1 al 20</h3>";
$numero = 1;
while ($numero <= 20) {
    if ($numero % 2 != 0) { // condición para mostrar solo impares
        echo $numero . " ";
    }
    $numero++;
}
echo "<br>";

// 3. Contador regresivo del 10 al 1 usando do-while (saltando el 5)
echo "<h3>Contador regresivo (saltando el 5)</h3>";
$contador = 10;
do {
    if ($contador == 5) {
        $contador--; // disminuir el contador y saltar el 5
        continue;    // pasar a la siguiente iteración
    }
    echo $contador . " ";
    $contador--;
} while ($contador >= 1);
?>
