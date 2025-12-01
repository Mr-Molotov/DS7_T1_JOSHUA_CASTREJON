<?php
// pdo/config.php
$dsn = 'mysql:host=127.0.0.1;dbname=biblioteca;charset=utf8mb4;port=3306';
$db_user = 'root';
$db_pass = '';

try {
    $pdo = new PDO($dsn, $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    die("Error de conexión PDO: " . $e->getMessage());
}
