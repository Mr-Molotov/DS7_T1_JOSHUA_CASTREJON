<?php
function sanitizarNombre($nombre) {
    return htmlspecialchars(trim($nombre), ENT_QUOTES, 'UTF-8');
}

function sanitizarEmail($email) {
    $email = filter_var(trim($email), FILTER_SANITIZE_EMAIL);
    return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
}

function sanitizarEdad($edad) {
    return filter_var($edad, FILTER_SANITIZE_NUMBER_INT);
}

function sanitizarSitioWeb($sitioWeb) {
    $sitioWeb = filter_var(trim($sitioWeb), FILTER_SANITIZE_URL);
    return filter_var($sitioWeb, FILTER_VALIDATE_URL) ? $sitioWeb : null;
}

function sanitizarGenero($genero) {
    return htmlspecialchars(trim($genero), ENT_QUOTES, 'UTF-8');
}

function sanitizarIntereses($intereses) {
    return array_map(function($interes) {
        return htmlspecialchars(trim($interes), ENT_QUOTES, 'UTF-8');
    }, $intereses);
}

function sanitizarComentarios($comentarios) {
    return htmlspecialchars(trim($comentarios), ENT_QUOTES, 'UTF-8');
}
?>
