<?php
session_start();

// Cargar datos previos de la sesión si existen
$datos_previos = [];
$errores = [];

if (isset($_SESSION['datos_previos'])) {
    $datos_previos = $_SESSION['datos_previos'];
    $errores = $_SESSION['errores'] ?? [];
    
    // Limpiar la sesión
    unset($_SESSION['datos_previos']);
    unset($_SESSION['errores']);
}

include 'formulario.html';
?>