<?php
if (!function_exists("doblar")) {
    function doblar($numero) {
        return $numero * 2;
    }
}

if (!function_exists("saludoPersonalizado")) {
    function saludoPersonalizado($nombre) {
        return "Hola, $nombre!";
    }
}
?>
