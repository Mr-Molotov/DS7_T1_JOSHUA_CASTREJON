<?php
require_once "Gerente.php";
require_once "Desarrollador.php";
require_once "Empresa.php";

// Crear empresa
$empresa = new Empresa();

// Crear empleados
$gerente = new Gerente("Ana López", 101, 5000, "Ventas");
$gerente->asignarBono(1000);

$desarrollador1 = new Desarrollador("Carlos Pérez", 201, 3000, "PHP", "Senior");
$desarrollador2 = new Desarrollador("Laura Gómez", 202, 2500, "JavaScript", "Junior");

// Agregar empleados a la empresa
$empresa->agregarEmpleado($gerente);
$empresa->agregarEmpleado($desarrollador1);
$empresa->agregarEmpleado($desarrollador2);

// Listar empleados
echo "<h3>Lista de empleados:</h3>";
$empresa->listarEmpleados();

// Calcular nómina
echo "<h3>Nómina total:</h3>";
echo "Total: $" . $empresa->calcularNominaTotal() . "<br>";

// Evaluaciones de desempeño
echo "<h3>Evaluaciones de desempeño:</h3>";
$empresa->evaluarDesempenioEmpleados();
