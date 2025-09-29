<?php
class Empleado {
    protected $nombre;
    protected $idEmpleado;
    protected $salarioBase;

    public function __construct($nombre, $idEmpleado, $salarioBase) {
        $this->nombre = $nombre;
        $this->idEmpleado = $idEmpleado;
        $this->salarioBase = $salarioBase;
    }

    // Getters y Setters
    public function getNombre() {
        return $this->nombre;
    }

    public function setNombre($nombre) {
        $this->nombre = $nombre;
    }

    public function getIdEmpleado() {
        return $this->idEmpleado;
    }

    public function setIdEmpleado($idEmpleado) {
        $this->idEmpleado = $idEmpleado;
    }

    public function getSalarioBase() {
        return $this->salarioBase;
    }

    public function setSalarioBase($salarioBase) {
        $this->salarioBase = $salarioBase;
    }
}
