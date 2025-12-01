<?php
// mysqli/config.php
// Ajusta estos valores
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'biblioteca';
$db_port = 3306;

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name, $db_port);

if ($conn->connect_errno) {
    http_response_code(500);
    die('Error de conexión MySQLi: ' . $conn->connect_error);
}

// Set charset
$conn->set_charset('utf8mb4');

// Helper: prepare + check
function mysqli_prepare_checked($conn, $sql) {
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("MySQLi prepare error: " . $conn->error);
    }
    return $stmt;
}
