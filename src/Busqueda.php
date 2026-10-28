<?php
// VERSIÓN CORREGIDA. Mitiga R-05 / T-03 con consulta preparada y validación de entrada.

function buscarProductos(PDO $db, string $termino): array
{
    $termino = mb_substr(trim($termino), 0, 100);                    // longitud máxima
    $termino = addcslashes($termino, '%_\\');                          // comodines como texto
    $stmt = $db->prepare(
        "SELECT sku, nombre, precio FROM productos WHERE nombre LIKE :t ESCAPE '\\'"
    );
    $stmt->bindValue(':t', '%' . $termino . '%', PDO::PARAM_STR);    // el dato nunca se mezcla con el SQL
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
