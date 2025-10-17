<?php
// Verifica si la sesión ya está activa
if (session_status() === PHP_SESSION_NONE) {
    // Configura los parámetros de la cookie de sesión antes de iniciar la sesión
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => isset($_SERVER['HTTPS']), // Solo HTTPS si está activo
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
    session_start();
}
?>
