<?php
require_once 'validaciones.php';
require_once 'sanitizacion.php';

// Iniciar sesión para persistencia de datos
session_start();

// Crear directorio de uploads si no existe
if (!file_exists('uploads')) {
    mkdir('uploads', 0777, true);
}

// Inicializar array para datos previos
$datos_previos = [];
$errores = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $datos = [];

    // Procesar y validar cada campo
    $campos = ['nombre', 'email', 'fecha_nacimiento', 'sitioWeb', 'genero', 'intereses', 'comentarios'];
    
    foreach ($campos as $campo) {
        if (isset($_POST[$campo])) {
            $valor = $_POST[$campo];
            
            // Sanitizar
            if ($campo === 'intereses' && is_array($valor)) {
                $valorSanitizado = sanitizarIntereses($valor);
            } else {
                $funcionSanitizacion = "sanitizar" . ucfirst($campo);
                if (function_exists($funcionSanitizacion)) {
                    $valorSanitizado = call_user_func($funcionSanitizacion, $valor);
                } else {
                    $valorSanitizado = trim(htmlspecialchars($valor));
                }
            }
            
            $datos[$campo] = $valorSanitizado;
            $datos_previos[$campo] = $valorSanitizado; // Guardar para persistencia

            // Validar
            $funcionValidacion = "validar" . ucfirst($campo);
            if (function_exists($funcionValidacion)) {
                if (!call_user_func($funcionValidacion, $valorSanitizado)) {
                    $errores[] = "El campo " . str_replace('_', ' ', $campo) . " no es válido.";
                }
            }
        }
    }

    // Procesar la foto de perfil
    if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] !== UPLOAD_ERR_NO_FILE) {
        if (!validarFotoPerfil($_FILES['foto_perfil'])) {
            $errores[] = "La foto de perfil no es válida.";
        } else {
            // Verificar y generar nombre único
            $nombreOriginal = basename($_FILES['foto_perfil']['name']);
            $nombreUnico = verificarNombreUnico($nombreOriginal);
            
            $rutaDestino = 'uploads/' . $nombreUnico;
            if (move_uploaded_file($_FILES['foto_perfil']['tmp_name'], $rutaDestino)) {
                $datos['foto_perfil'] = $rutaDestino;
            } else {
                $errores[] = "Hubo un error al subir la foto de perfil.";
            }
        }
    }

    // Calcular edad automáticamente
    if (isset($datos['fecha_nacimiento']) && validarFechaNacimiento($datos['fecha_nacimiento'])) {
        $datos['edad'] = calcularEdad($datos['fecha_nacimiento']);
    }

    // Si no hay errores, guardar datos y mostrar éxito
    if (empty($errores)) {
        // Guardar en archivo JSON
        guardarRegistro($datos);
        
        // Limpiar datos previos
        $datos_previos = [];
        
        // Mostrar resultados
        mostrarResultados($datos);
    } else {
        // Guardar datos previos en sesión para persistencia
        $_SESSION['datos_previos'] = $datos_previos;
        $_SESSION['errores'] = $errores;
        
        // Redirigir al formulario con errores
        header('Location: formulario.php');
        exit();
    }
    
} else {
    // Cargar datos previos de la sesión si existen
    if (isset($_SESSION['datos_previos'])) {
        $datos_previos = $_SESSION['datos_previos'];
        $errores = $_SESSION['errores'] ?? [];
        
        // Limpiar la sesión
        unset($_SESSION['datos_previos']);
        unset($_SESSION['errores']);
    }
    
    // Mostrar formulario
    include 'formulario.html';
}

function guardarRegistro($datos) {
    $archivo = 'registros.json';
    $registros = [];
    
    // Leer registros existentes
    if (file_exists($archivo)) {
        $contenido = file_get_contents($archivo);
        $registros = json_decode($contenido, true) ?: [];
    }
    
    // Añadir nuevo registro con timestamp
    $datos['timestamp'] = date('Y-m-d H:i:s');
    $registros[] = $datos;
    
    // Guardar
    file_put_contents($archivo, json_encode($registros, JSON_PRETTY_PRINT));
}

function mostrarResultados($datos) {
    echo "<h2>Datos Recibidos:</h2>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    
    $camposMostrar = [
        'nombre' => 'Nombre',
        'email' => 'Email', 
        'fecha_nacimiento' => 'Fecha de Nacimiento',
        'edad' => 'Edad',
        'sitioWeb' => 'Sitio Web',
        'genero' => 'Género',
        'intereses' => 'Intereses',
        'comentarios' => 'Comentarios',
        'foto_perfil' => 'Foto de Perfil'
    ];
    
    foreach ($camposMostrar as $campo => $titulo) {
        if (isset($datos[$campo])) {
            echo "<tr>";
            echo "<th style='padding: 8px; background: #f0f0f0;'>$titulo</th>";
            echo "<td style='padding: 8px;'>";
            
            if ($campo === 'intereses' && is_array($datos[$campo])) {
                echo implode(", ", $datos[$campo]);
            } elseif ($campo === 'foto_perfil') {
                echo "<img src='" . htmlspecialchars($datos[$campo]) . "' width='100' alt='Foto de perfil'>";
            } else {
                echo htmlspecialchars($datos[$campo]);
            }
            
            echo "</td>";
            echo "</tr>";
        }
    }
    
    echo "</table>";
    echo "<br><a href='formulario.php'>Volver al formulario</a> | ";
    echo "<a href='resumen.php'>Ver resumen de registros</a>";
}
?>