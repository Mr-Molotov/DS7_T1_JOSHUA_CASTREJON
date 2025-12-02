<?php
require_once "database.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <h1>TechParts</h1>
    <h2>Lista de Productos</h2>
    <?php
        $sql = "SELECT id, nombre, categoria, precio, cantidad, fecha_registro FROM productos";
        $result = mysqli_query($conn, $sql);

    if($result){
        if(mysqli_num_rows($result) > 0){
            echo "<table>";
                    echo "<tr>";
                    echo "<th>ID</th>";
                    echo "<th>Nombre</th>";
                    echo "<th>Categoría</th>";
                    echo "<th>Precio</th>";
                    echo "<th>Cantidad</th>";
                    echo "<th>Fecha de Registro</th>";
                echo "</tr>";
        while($row = mysqli_fetch_array($result)){
            echo "<tr>";
                echo "<td>" . $row['id'] . "</td>";
                echo "<td>" . $row['nombre'] . "</td>";
                echo "<td>" . $row['categoria'] . "</td>";
                echo "<td>" . $row['precio'] . "</td>";
                echo "<td>" . $row['cantidad'] . "</td>";
                echo "<td>" . $row['fecha_registro'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        mysqli_free_result($result);
        } else{
        echo "No se encontraron registros.";
        }
    } else{
        echo "ERROR: No se pudo ejecutar $sql. " . mysqli_error($conn);
    }
    mysqli_close($conn);
    ?>
    <br>
    <input type="button" value="Refrescar Página" onclick="window.location.reload();">
    <h2>Operaciones</h2>
    <ul>
        <li><a href="crearproducto.php">Añadir producto</a></li>
        <li><a href="editarproducto.php">Editar producto</a></li>
        <li><a href="eliminarproducto.php">Eliminar producto</a></li>
    </ul>
</body>
</html>