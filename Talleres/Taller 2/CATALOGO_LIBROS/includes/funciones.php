<?php
// Devuelve un array de libros (simulando base de datos)
function obtenerLibros() {
    return [
        [
            "titulo" => "Cien Años de Soledad",
            "autor" => "Gabriel García Márquez",
            "anio" => 1967,
            "descripcion" => "Una de las obras más importantes de la literatura hispanoamericana."
        ],
        [
            "titulo" => "Don Quijote de la Mancha",
            "autor" => "Miguel de Cervantes",
            "anio" => 1605,
            "descripcion" => "La historia del ingenioso hidalgo y sus aventuras con Sancho Panza."
        ],
        [
            "titulo" => "La Sombra del Viento",
            "autor" => "Carlos Ruiz Zafón",
            "anio" => 2001,
            "descripcion" => "Un homenaje al amor por los libros, ambientado en la Barcelona de posguerra."
        ],
        [
            "titulo" => "El Principito",
            "autor" => "Antoine de Saint-Exupéry",
            "anio" => 1943,
            "descripcion" => "Un clásico de la literatura infantil y filosófica."
        ],
        [
            "titulo" => "1984",
            "autor" => "George Orwell",
            "anio" => 1949,
            "descripcion" => "Una novela distópica que reflexiona sobre la libertad y el control social."
        ]
    ];
}

// Retorna HTML con los detalles de un libro
function mostrarDetallesLibro($libro) {
    return "
        <div class='libro'>
            <h2>{$libro['titulo']}</h2>
            <p><strong>Autor:</strong> {$libro['autor']}</p>
            <p><strong>Año:</strong> {$libro['anio']}</p>
            <p>{$libro['descripcion']}</p>
        </div>
    ";
}

// Ordena los libros por título
function ordenarLibrosPorTitulo($libros) {
    usort($libros, function($a, $b) {
        return strcmp($a['titulo'], $b['titulo']);
    });
    return $libros;
}
?>
