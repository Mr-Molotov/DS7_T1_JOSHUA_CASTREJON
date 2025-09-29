<?php
declare(strict_types=1);
require_once __DIR__ . '/Estudiante.php';

class SistemaGestionEstudiantes {
    /** @var Estudiante[] keyed by id */
    private array $estudiantes = [];
    /** @var Estudiante[] keyed by id (graduados) */
    private array $graduados = [];

    public function agregarEstudiante(Estudiante $estudiante): void {
        $id = $estudiante->getId();
        if (isset($this->estudiantes[$id])) {
            throw new InvalidArgumentException("Ya existe un estudiante con ID $id");
        }
        $this->estudiantes[$id] = $estudiante;
    }

    public function obtenerEstudiante(int $id): Estudiante {
        if (!isset($this->estudiantes[$id])) {
            throw new RuntimeException("Estudiante con ID $id no encontrado.");
        }
        return $this->estudiantes[$id];
    }

    /**
     * Retorna lista indexada (no asociativa) de Estudiante
     * @return Estudiante[]
     */
    public function listarEstudiantes(): array {
        return array_values($this->estudiantes);
    }

    public function calcularPromedioGeneral(): float {
        $arr = $this->listarEstudiantes();
        if (empty($arr)) return 0.0;
        $promedios = array_map(fn(Estudiante $e) => $e->obtenerPromedio(), $arr);
        $suma = array_reduce($promedios, fn($c, $i) => $c + $i, 0.0);
        return $suma / count($promedios);
    }

    public function obtenerEstudiantesPorCarrera(string $carrera): array {
        $c = mb_strtolower($carrera);
        return array_values(array_filter($this->estudiantes, function(Estudiante $e) use ($c) {
            return mb_strpos(mb_strtolower($e->getCarrera()), $c) !== false;
        }));
    }

    public function obtenerMejorEstudiante(): ?Estudiante {
        if (empty($this->estudiantes)) return null;
        return array_reduce($this->estudiantes, function($mejor, Estudiante $actual) {
            if ($mejor === null) return $actual;
            return $actual->obtenerPromedio() > $mejor->obtenerPromedio() ? $actual : $mejor;
        }, null);
    }

    /**
     * Genera reporte por materia: devuelve materia => ['promedio','maximo','minimo','cantidad']
     * @return array
     */
    public function generarReporteRendimiento(): array {
        $agregado = []; // materia => [notas...]
        foreach ($this->estudiantes as $est) {
            foreach ($est->getMaterias() as $mat => $nota) {
                $agregado[$mat][] = $nota;
            }
        }
        $reporte = [];
        foreach ($agregado as $mat => $notas) {
            $reporte[$mat] = [
                'promedio' => array_sum($notas) / count($notas),
                'maximo' => max($notas),
                'minimo' => min($notas),
                'cantidad' => count($notas),
            ];
        }
        return $reporte;
    }

    public function graduarEstudiante(int $id): void {
        if (!isset($this->estudiantes[$id])) {
            throw new RuntimeException("No se puede graduar: estudiante con ID $id no existe.");
        }
        $this->graduados[$id] = $this->estudiantes[$id];
        unset($this->estudiantes[$id]);
    }

    public function generarRanking(): array {
        $lista = $this->listarEstudiantes();
        usort($lista, fn(Estudiante $a, Estudiante $b) => $b->obtenerPromedio() <=> $a->obtenerPromedio());
        return $lista;
    }

    public function buscarEstudiantes(string $termino): array {
        $t = mb_strtolower($termino);
        return array_values(array_filter($this->estudiantes, function(Estudiante $e) use ($t) {
            return (mb_strpos(mb_strtolower($e->getNombre()), $t) !== false) ||
                   (mb_strpos(mb_strtolower($e->getCarrera()), $t) !== false);
        }));
    }

    public function estadisticasPorCarrera(): array {
        $porCarrera = [];
        foreach ($this->estudiantes as $est) {
            $c = $est->getCarrera();
            $porCarrera[$c][] = $est;
        }
        $resultado = [];
        foreach ($porCarrera as $carrera => $arr) {
            $prom = array_sum(array_map(fn(Estudiante $e) => $e->obtenerPromedio(), $arr)) / count($arr);
            $mejor = array_reduce($arr, fn($m, $a) => ($m === null || $a->obtenerPromedio() > $m->obtenerPromedio()) ? $a : $m, null);
            $resultado[$carrera] = [
                'cantidad' => count($arr),
                'promedio' => $prom,
                'mejor' => $mejor
            ];
        }
        return $resultado;
    }

    // ---------------- Persistence (JSON) ----------------
    public function guardarAArchivo(string $ruta): bool {
        $data = [
            'estudiantes' => array_map(fn(Estudiante $e) => $e->toArray(), array_values($this->estudiantes)),
            'graduados' => array_map(fn(Estudiante $e) => $e->toArray(), array_values($this->graduados)),
        ];
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        if ($json === false) return false;
        return file_put_contents($ruta, $json) !== false;
    }

    public function cargarDesdeArchivo(string $ruta): bool {
        if (!file_exists($ruta)) return false;
        $json = file_get_contents($ruta);
        if ($json === false) return false;
        $data = json_decode($json, true);
        if (!is_array($data)) return false;

        $this->estudiantes = [];
        $this->graduados = [];

        foreach ($data['estudiantes'] ?? [] as $arr) {
            $e = Estudiante::fromArray($arr);
            $this->estudiantes[$e->getId()] = $e;
        }
        foreach ($data['graduados'] ?? [] as $arr) {
            $e = Estudiante::fromArray($arr);
            $this->graduados[$e->getId()] = $e;
        }
        return true;
    }

    public function listarGraduados(): array {
        return array_values($this->graduados);
    }
}