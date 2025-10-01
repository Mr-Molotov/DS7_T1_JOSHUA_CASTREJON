<?php
// Definición de variables
$nombre_completo = "Joshua Castrejón"; 
$edad = 25; 
$correo = "joshua.castrejon@utp.ac.pa"; 
$telefono = "6392-2092"; 

// Definición de constante
define("OCUPACION", "Estudiante");

// Métodos de impresión
echo "<p><strong>Nombre Completo:</strong> " . $nombre_completo . "</p>";
print "<p><strong>Edad:</strong> " . $edad . "</p>";
printf("<p><strong>Correo:</strong> %s</p>", $correo);
echo "<p><strong>Teléfono:</strong> {$telefono}</p>";
print "<p><strong>Ocupación:</strong> " . OCUPACION . "</p>";

// Salto de línea para separar el var_dump
echo "<hr><h3>Detalles técnicos (var_dump)</h3>";

// var_dump de cada variable y la constante
var_dump($nombre_completo);
echo "<br>";
var_dump($edad);
echo "<br>";
var_dump($correo);
echo "<br>";
var_dump($telefono);
echo "<br>";
var_dump(OCUPACION);
?>
