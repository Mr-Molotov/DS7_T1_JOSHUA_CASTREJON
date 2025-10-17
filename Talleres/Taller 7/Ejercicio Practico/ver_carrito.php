<?php include 'config_sesion.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <title>Carrito</title>
    <link rel="stylesheet" href="estilos.css">
    <script>
    function actualizarCantidad(id, cantidad) {
        fetch('actualizar_carrito.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `id=${id}&cantidad=${cantidad}`
        })
        .then(response => response.text())
        .then(html => {
            document.getElementById('carrito').innerHTML = html;
        });
    }

    function eliminarProducto(id) {
        fetch('eliminar_del_carrito.php?id=' + id)
        .then(response => response.text())
        .then(html => {
            document.getElementById('carrito').innerHTML = html;
        });
    }
    </script>
</head>
<body>
<div class="container">
    <h2>Tu Carrito</h2>
    <div id="carrito">
        <?php include 'actualizar_carrito.php'; ?>
    </div>
    <a href="productos.php">← Volver a productos</a>
</div>
</body>
</html>
