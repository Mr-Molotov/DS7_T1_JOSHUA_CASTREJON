<?php
function registrar_error($mensaje) {
    $fecha = date("Y-m-d H:i:s");
    $log = "[$fecha] $mensaje" . PHP_EOL;

    file_put_contents("errores.log", $log, FILE_APPEND);
}
?>
