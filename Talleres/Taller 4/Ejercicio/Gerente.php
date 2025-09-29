<?php
require_once "Empleado.php";
require_once "Evaluable.php";

class Gerente extends Empleado implements Evaluable {
    private $departamento;
    private $bono = 0;

    public function __construct($nombre, $idEmpleado, $salarioBase, $departamento) {
        parent::__construct($nombre, $idEmpleado, $salarioBase);
        $this->departamento = $departamento;
    }

    public function asignarBono($monto) {
        $this->bono = $monto;
    }

    public function getDepartamento() {
        return $this->departamento;
    }

    public function evaluarDesempenio() {
        // Lógica simple: si tiene bono, se considera buen desempeño
        if ($this->bono > 0) {
            return "{$this->nombre} (Gerente) tuvo un excelente desempeño.";
        } else {
            return "{$this->nombre} (Gerente) necesita mejorar su desempeño.";
        }
    }

    public function getSalarioTotal() {
        return $this->salarioBase + $this->bono;
    }
}
