# TALLER_8 - Sistema de Gestión de Biblioteca

## Requisitos
- PHP 7.4+ (recomendado 8+)
- MySQL/MariaDB

## Instalación
1. Crear la base de datos y tablas: importar `db_schema.sql`.
2. Ajustar credenciales en `mysqli/config.php` y `pdo/config.php`.
3. Subir la carpeta `TALLER_8/` a tu servidor web.

## Endpoints (ejemplos)
- `mysqli/libros.php?action=list&page=1&limit=10` — lista libros
- `pdo/usuarios.php?action=add` (POST) — crear usuario
- `mysqli/prestamos.php?action=lend` (POST) — prestar (usuario_id, libro_id)
- `pdo/prestamos.php?action=return` (POST) — devolver (prestamo_id)

## Estructura del proyecto
- `mysqli/` — implementación usando MySQLi (procedural)
- `pdo/` — implementación usando PDO (orientada a excepciones)
- `helpers.php` — funciones compartidas (paginación, logging)

## Notas importantes
- Todas las queries usan consultas preparadas.
- Operaciones con múltiples pasos (prestar/devolver) usan transacciones y `SELECT ... FOR UPDATE` para evitar condiciones de carrera.
- Las contraseñas se almacenan con `password_hash()`.
- Entradas se sanitizan y validan de forma básica; se recomienda añadir validaciones adicionales según el caso.
- Paginación incluida con parámetros `page` y `limit`.

## Comparación MySQLi vs PDO (breve)
- PDO ofrece manejo de excepciones, binding por nombre y soporte multi-DB; su API es más moderna y menos propensa a errores.
- MySQLi (procedural) es directo y a veces más sencillo si solo se trabaja con MySQL; sin embargo, su manejo de errores suele ser más manual.
