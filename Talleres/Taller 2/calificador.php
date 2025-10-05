<?php

// Declarar la variable con un valor entre 0 y 100
$calificacion = 95; // Puedes cambiar este valor para probar

// Determinar la letra con if-elseif-else
if ($calificacion >= 90 && $calificacion <= 100) {
    $letra = "A";
} elseif ($calificacion >= 80 && $calificacion <= 89) {
    $letra = "B";
} elseif ($calificacion >= 70 && $calificacion <= 79) {
    $letra = "C";
} elseif ($calificacion >= 60 && $calificacion <= 69) {
    $letra = "D";
} elseif ($calificacion >= 0 && $calificacion <= 59) {
    $letra = "F";
} else {
    $letra = "Valor inválido"; // Por si se coloca un número fuera de rango
}

// Imprimir el resultado con el mensaje
if ($letra !== "Valor inválido") {
    echo "Tu calificación es $letra<br>";

    // Usar operador ternario para Aprobado/Reprobado
    $estado = ($letra == "F") ? "Reprobado" : "Aprobado";
    echo "$estado<br>";

    // Switch para imprimir mensaje adicional
    switch ($letra) {
        case "A":
            echo "Excelente trabajo";
            break;
        case "B":
            echo "Buen trabajo";
            break;
        case "C":
            echo "Trabajo aceptable";
            break;
        case "D":
            echo "Necesitas mejorar";
            break;
        case "F":
            echo "Debes esforzarte más";
            break;
    }
} else {
    echo "La calificación ingresada no es válida.";
}
?>
