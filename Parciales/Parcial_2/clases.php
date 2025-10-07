<?php
// Archivo: clases.php

// Interfaz Inventariable
interface Inventariable {
    public function obtenerInformacionInventario(): string;
}

// Clase base Producto
class Producto {
    public $id;
    public $nombre;
    public $descripcion;
    public $estado;
    public $stock;
    public $fechaIngreso;
    public $categoria;

    public function __construct($datos) {
        foreach ($datos as $clave => $valor) {
            if (property_exists($this, $clave)) {
                $this->$clave = $valor;
            }
        }
    }
}

// ----- Producto Electrónico -----
class ProductoElectronico extends Producto implements Inventariable {
    public $garantiaMeses;

    public function __construct($datos) {
        parent::__construct($datos);
        $this->garantiaMeses = $datos['garantiaMeses'] ?? 0;
    }

    public function obtenerInformacionInventario(): string {
        return "Electrónico: {$this->nombre}, ID: {$this->id}, Stock: {$this->stock}, Garantía: {$this->garantiaMeses} meses";
    }
}

// ----- Producto Alimento -----
class ProductoAlimento extends Producto implements Inventariable {
    public $fechaVencimiento;

    public function __construct($datos) {
        parent::__construct($datos);
        $this->fechaVencimiento = $datos['fechaVencimiento'] ?? '';
    }

    public function obtenerInformacionInventario(): string {
        return "Alimento: {$this->nombre}, ID: {$this->id}, Stock: {$this->stock}, Vence: {$this->fechaVencimiento}";
    }
}

// ----- Producto Ropa -----
class ProductoRopa extends Producto implements Inventariable {
    public $talla;

    public function __construct($datos) {
        parent::__construct($datos);
        $this->talla = $datos['talla'] ?? '';
    }

    public function obtenerInformacionInventario(): string {
        return "Ropa: {$this->nombre}, ID: {$this->id}, Stock: {$this->stock}, Talla: {$this->talla}";
    }
}

// ----- Gestor de Inventario -----
class GestorInventario {
    private $items = [];
    private $rutaArchivo = 'productos.json';

    // Obtener todos los productos
    public function obtenerTodos() {
        if (empty($this->items)) {
            $this->cargarDesdeArchivo();
        }
        return $this->items;
    }

    // Cargar productos desde JSON
    private function cargarDesdeArchivo() {
        if (!file_exists($this->rutaArchivo)) {
            return;
        }
        $jsonContenido = file_get_contents($this->rutaArchivo);
        $arrayDatos = json_decode($jsonContenido, true);
        if ($arrayDatos === null) return;

        $this->items = []; // Reiniciamos items antes de cargar
        foreach ($arrayDatos as $datos) {
            switch (strtolower($datos['categoria'] ?? '')) {
                case 'electronico':
                    $producto = new ProductoElectronico($datos);
                    break;
                case 'alimento':
                    $producto = new ProductoAlimento($datos);
                    break;
                case 'ropa':
                    $producto = new ProductoRopa($datos);
                    break;
                default:
                    $producto = new Producto($datos);
                    break;
            }
            $this->items[] = $producto;
        }
    }

    // Guardar productos en JSON
    private function persistirEnArchivo() {
        $arrayParaGuardar = array_map(function($item) {
            return get_object_vars($item);
        }, $this->items);

        file_put_contents(
            $this->rutaArchivo,
            json_encode($arrayParaGuardar, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
    }

    // Obtener el máximo ID
    public function obtenerMaximoId() {
        if (empty($this->items)) return 0;
        $ids = array_map(fn($item) => intval($item->id), $this->items);
        return max($ids);
    }

    // Agregar producto (ya no sobrescribe)
    public function agregar($nuevoProducto) {
        // Cargar archivo antes de agregar si $items está vacío
        if (empty($this->items)) {
            $this->cargarDesdeArchivo();
        }

        $nuevoProducto->id = $this->obtenerMaximoId() + 1;
        $this->items[] = $nuevoProducto;
        $this->persistirEnArchivo();
    }

    // Eliminar producto por ID
    public function eliminar($idProducto) {
        $idProducto = intval($idProducto);
        foreach ($this->items as $indice => $item) {
            if (intval($item->id) === $idProducto) {
                array_splice($this->items, $indice, 1);
                $this->persistirEnArchivo();
                return true;
            }
        }
        return false;
    }

    // Actualizar producto
    public function actualizar($productoActualizado) {
        $idProducto = intval($productoActualizado->id);
        foreach ($this->items as $indice => $item) {
            if (intval($item->id) === $idProducto) {
                $this->items[$indice] = $productoActualizado;
                $this->persistirEnArchivo();
                return true;
            }
        }
        return false;
    }

    // Cambiar estado
    public function cambiarEstado($idProducto, $estadoNuevo) {
        $idProducto = intval($idProducto);
        foreach ($this->items as &$item) { // referencia &
            if (intval($item->id) === $idProducto) {
                $item->estado = $estadoNuevo;
                $this->persistirEnArchivo();
                return true;
            }
        }
        return false;
    }

    // Filtrar por estado
    public function filtrarPorEstado($estadoBuscado) {
        if (empty($estadoBuscado)) return $this->items;
        return array_filter($this->items, function($item) use ($estadoBuscado) {
            return isset($item->estado) && trim($item->estado) === trim($estadoBuscado);
        });
    }

    // Obtener por ID
    public function obtenerPorId($idBuscado) {
        $idBuscado = intval($idBuscado);
        foreach ($this->items as $item) {
            if (intval($item->id) === $idBuscado) return $item;
        }
        return null;
    }
}
