<?php
// Función para leer registros del archivo JSON
function leerRegistros() {
    $archivo = 'registros.json';
    if (file_exists($archivo)) {
        $contenido = file_get_contents($archivo);
        return json_decode($contenido, true) ?: [];
    }
    return [];
}

$registros = leerRegistros();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resumen de Registros</title>
    <style>
        table { border-collapse: collapse; width: 100%; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background-color: #f2f2f2; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .foto { max-width: 80px; max-height: 80px; }
        .sin-registros { text-align: center; padding: 20px; color: #666; }
    </style>
</head>
<body>
    <h1>Resumen de Registros</h1>
    
    <?php if (empty($registros)): ?>
        <div class="sin-registros">
            <p>No hay registros disponibles.</p>
        </div>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Fecha Nacimiento</th>
                    <th>Edad</th>
                    <th>Género</th>
                    <th>Intereses</th>
                    <th>Foto</th>
                    <th>Fecha Registro</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($registros as $registro): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($registro['nombre'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($registro['email'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($registro['fecha_nacimiento'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($registro['edad'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($registro['genero'] ?? ''); ?></td>
                        <td>
                            <?php 
                            if (isset($registro['intereses']) && is_array($registro['intereses'])) {
                                echo htmlspecialchars(implode(', ', $registro['intereses']));
                            }
                            ?>
                        </td>
                        <td>
                            <?php if (isset($registro['foto_perfil'])): ?>
                                <img src="<?php echo htmlspecialchars($registro['foto_perfil']); ?>" 
                                     alt="Foto" class="foto">
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($registro['timestamp'] ?? ''); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <p>Total de registros: <?php echo count($registros); ?></p>
    <?php endif; ?>
    
    <br>
    <a href="formulario.php">Volver al formulario</a>
</body>
</html>