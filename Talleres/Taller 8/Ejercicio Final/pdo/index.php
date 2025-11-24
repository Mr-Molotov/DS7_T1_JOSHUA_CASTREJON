<?php
// pdo/index.php
?><!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Biblioteca (PDO)</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>body{font-family:sans-serif;max-width:900px;margin:20px auto}code{background:#f5f5f5;padding:2px 4px;border-radius:4px}</style>
</head>
<body>
  <h1>Biblioteca (PDO)</h1>
  <p>API mínima basada en endpoints:</p>
  <ul>
    <li><code>libros.php?action=list|create|search|update|delete</code></li>
    <li><code>usuarios.php?action=list|create|search|update|delete</code></li>
    <li><code>prestamos.php?action=list|create|return|historial</code></li>
  </ul>
  <p>Usa POST para crear/actualizar/eliminar y GET para listar/buscar.</p>
</body>
</html>
