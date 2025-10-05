<?php

function validarNombre($nombre) {
    return !empty($nombre) && preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{2,50}$/', $nombre);
}

function validarEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function validarFechaNacimiento($fecha) {
    if (empty($fecha)) return false;
    
    // Verificar formato de fecha
    $fechaObj = DateTime::createFromFormat('Y-m-d', $fecha);
    if (!$fechaObj) return false;
    
    // Verificar que la fecha no sea futura
    $hoy = new DateTime();
    if ($fechaObj > $hoy) return false;
    
    // Verificar edad mínima (por ejemplo, 5 años)
    $edadMinima = $hoy->modify('-5 years');
    return $fechaObj <= $edadMinima;
}

function validarSitioWeb($sitioWeb) {
    if (empty($sitioWeb)) return true;
    return filter_var($sitioWeb, FILTER_VALIDATE_URL) !== false;
}

function validarGenero($genero) {
    $generosValidos = ['masculino', 'femenino', 'otro'];
    return in_array($genero, $generosValidos);
}

function validarIntereses($intereses) {
    if (empty($intereses)) return true;
    
    $interesesValidos = ['deportes', 'musica', 'lectura', 'tecnologia'];
    foreach ($intereses as $interes) {
        if (!in_array($interes, $interesesValidos)) {
            return false;
        }
    }
    return true;
}

function validarComentarios($comentarios) {
    if (empty($comentarios)) return true;
    return strlen($comentarios) <= 500;
}

function validarFotoPerfil($archivo) {
    if ($archivo['error'] === UPLOAD_ERR_NO_FILE) {
        return true; // No es obligatorio
    }
    
    if ($archivo['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    // Validar tipo de archivo
    $tiposPermitidos = ['image/jpeg', 'image/png', 'image/gif'];
    $tipoArchivo = mime_content_type($archivo['tmp_name']);
    if (!in_array($tipoArchivo, $tiposPermitidos)) {
        return false;
    }
    
    // Validar tamaño (máximo 2MB)
    if ($archivo['size'] > 2 * 1024 * 1024) {
        return false;
    }
    
    return true;
}

// Función para verificar nombre único de archivo
function verificarNombreUnico($nombreArchivo) {
    $directorio = 'uploads/';
    $rutaCompleta = $directorio . $nombreArchivo;
    
    // Si el archivo no existe, el nombre es único
    if (!file_exists($rutaCompleta)) {
        return true;
    }
    
    // Si existe, generar un nombre único
    $extension = pathinfo($nombreArchivo, PATHINFO_EXTENSION);
    $nombreBase = pathinfo($nombreArchivo, PATHINFO_FILENAME);
    $contador = 1;
    
    do {
        $nuevoNombre = $nombreBase . '_' . $contador . '.' . $extension;
        $rutaCompleta = $directorio . $nuevoNombre;
        $contador++;
    } while (file_exists($rutaCompleta));
    
    return $nuevoNombre;
}

// Función para calcular edad a partir de fecha de nacimiento
function calcularEdad($fechaNacimiento) {
    $nacimiento = new DateTime($fechaNacimiento);
    $hoy = new DateTime();
    $edad = $hoy->diff($nacimiento);
    return $edad->y;
}

?>