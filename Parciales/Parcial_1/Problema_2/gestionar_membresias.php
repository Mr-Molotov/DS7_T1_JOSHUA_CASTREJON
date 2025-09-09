<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; padding: 20px; }
        .destacado { color: blue; font-weight: bold; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <?php
    include 'funciones_gimnasio.php';

    $titulos = ["Nombre", "Membresía","Antiguedad","Costo Base","Desc. Aplicado","Seguro Médico","Costo Total"];
    $membresias =['basica'=>80, 'premium'=>120, 'vip'=>180, 'familiar'=>250, 'corporativa'=>300];
    $miembros = ['Juan Perez'=>['tipo'=>'premium', 'antiguedad'=>'15'],
                'Ana García'=>['tipo'=>'basica', 'antiguedad'=>'2'],
                'Carlos López'=>['tipo'=>'vip', 'antiguedad'=>'30'],
                'María Rodríguez'=>['tipo'=>'familiar', 'antiguedad'=>'8'],
                'Juan Perez'=>['tipo'=>'corporativa', 'antiguedad'=>'18'],];
    ?>
    <table>
        <tr>
            <?php foreach ($titulos as $titulo):?>{
                <th><?= $titulo ?></th>
            <?php endforeach; ?>
        </tr>
    </table>
    
</body>
</html>