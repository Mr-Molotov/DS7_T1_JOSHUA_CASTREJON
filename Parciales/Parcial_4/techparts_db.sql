CREATE DATABASE IF NOT EXISTS techparts_db2 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE techparts_db2;

CREATE TABLE IF NOT EXISTS productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(120) NOT NULL,
    precio DECIMAL(10, 2) NOT NULL,
    cantidad INT NOT NULL,
    categoria VARCHAR(80),
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)