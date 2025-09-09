<?php

function calcular_promocion($antiguedad_meses){
    $descuento = 0;
    if ($antiguedad_meses >= 24) {
        $descuento = $cuota_base  * 0.20;
        echo "Has recibido un 20% de descuento.<br>";
    } elseif ($antiguedad_meses >= 13) {
        $descuento = $cuota_base * 0.12;
        echo "Has recibido un 12% de descuento.<br>";
    } elseif ($antiguedad_meses >= 3) {
        $descuento = $cuota_base  * 0.08;
        echo "Has recibido un 8% de descuento.<br>";
    } else {
        echo "No ha recibido descuento.<br>";
    }
}
function calcular_seguro_medico($cuota_base){
    return $seguro_medico = $cuota_base * 0.05;
}

function calcular_cuota_final($cuota_base, $descuento, $seguro_medico){
    return $cuofi = $cuota_base - $descuento + $seguro_medico;
}

?>