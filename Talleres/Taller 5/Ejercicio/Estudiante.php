<?php
declare(strict_types=1);

class Estudiante {
    private int $id;
    private string $nombre;
    private int $edad;
    private string $carrera;
    private array $materias = []; // materia => calificacion (float)
    private array $flags = [];    // flags activos

    public function __construct(int $id, string $nombre, int $edad, string $carrera, array $materias = []) {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->setEdad($edad);
        $this->carrera = $carrera;

        // Agregar materias iniciales (validadas)
        foreach ($materias as $m => $n) {
            $this->agregarMateria((string)$m, (float)$n);
        }

        $this->asignarFlags();
    }

    // ---------- Getters ----------
    public function getId(): int { return $this->id; }
    public function getNombre(): string { return $this->nombre; }
    public function getEdad(): int { return $this->edad; }
    public function getCarrera(): string { return $this->carrera; }
    public function getMaterias(): array { return $this->materias; }
    public function getFlags(): array { return $this->flags; }

    // ---------- Setters / Mutadores ----------
    public function setEdad(int $edad): void {
        if ($edad < 0 || $edad > 130) {
            throw new InvalidArgumentException("Edad inválida: $edad");
        }
        $this->edad = $edad;
    }

    /**
     * Añade o actualiza una materia con su calificación (0 - 100)
     */
    public function agregarMateria(string $materia, float $calificacion): void {
        if ($calificacion < 0 || $calificacion > 100) {
            throw new InvalidArgumentException("Calificación debe estar entre 0 y 100. Dada: $calificacion");
        }
        $this->materias[$materia] = $calificacion;
        $this->asignarFlags();
    }

    // Calcula el promedio de las calificaciones
    public function obtenerPromedio(): float {
        if (empty($this->materias)) return 0.0;
        return array_sum($this->materias) / count($this->materias);
    }

    // Retorna detalles en arreglo asociativo
    public function obtenerDetalles(): array {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'edad' => $this->edad,
            'carrera' => $this->carrera,
            'materias' => $this->materias,
            'promedio' => $this->obtenerPromedio(),
            'flags' => $this->flags
        ];
    }

    // Asigna flags automáticos basados en las notas/promedio
    private function asignarFlags(): void {
        $this->flags = [];
        $promedio = $this->obtenerPromedio();

        // Honor roll
        if ($promedio >= 90 && $promedio > 0) {
            $this->flags[] = 'Honor Roll';
        }

        // En riesgo académico
        if ($promedio > 0 && $promedio < 70) {
            $this->flags[] = 'En riesgo académico';
        }

        // Materias reprobadas (nota < 60)
        foreach ($this->materias as $nota) {
            if ($nota < 60) {
                $this->flags[] = 'Materias reprobadas';
                break;
            }
        }

        // Normalizar (eliminar duplicados)
        $this->flags = array_values(array_unique($this->flags));
    }

    public function __toString(): string {
        $prom = number_format($this->obtenerPromedio(), 2);
        $flags = empty($this->flags) ? 'Ninguno' : implode(', ', $this->flags);
        return "[{$this->id}] {$this->nombre} ({$this->carrera}) — Promedio: {$prom} — Flags: {$flags}";
    }

    // Convertir a array para persistencia
    public function toArray(): array {
        return $this->obtenerDetalles();
    }

    // Crear objeto Estudiante desde arreglo (usado en carga JSON)
    public static function fromArray(array $data): Estudiante {
        $id = (int)($data['id'] ?? 0);
        $nombre = (string)($data['nombre'] ?? '');
        $edad = (int)($data['edad'] ?? 0);
        $carrera = (string)($data['carrera'] ?? '');
        $materias = (array)($data['materias'] ?? []);
        return new Estudiante($id, $nombre, $edad, $carrera, $materias);
    }
}
