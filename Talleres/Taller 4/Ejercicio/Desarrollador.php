<?php
require_once "Empleado.php";
require_once "Evaluable.php";

class Desarrollador extends Empleado implements Evaluable {
    private $lenguajePrincipal;
    private $nivelExperiencia;

    public function __construct($nombre, $idEmpleado, $salarioBase, $lenguajePrincipal, $nivelExperiencia) {
        parent::__construct($nombre, $idEmpleado, $salarioBase);
        $this->lenguajePrincipal = $lenguajePrincipal;
        $this->nivelExperiencia = $nivelExperiencia;
    }

    public function getLenguajePrincipal() {
        return $this->lenguajePrincipal;
    }

    public function getNivelExperiencia() {
        return $this->nivelExperiencia;
    }

    public function evaluarDesempenio() {
        // Lógica simple: si es Senior se considera excelente
        if (strtolower($this->nivelExperiencia) === "senior") {
            return "{$this->nombre} (Desarrollador) tuvo un excelente desempeño.";
        } else {
            return "{$this->nombre} (Desarrollador) está en proceso de mejorar.";
        }
    }

    public function getSalarioTotal() {
        return $this->salarioBase;
    }
}
