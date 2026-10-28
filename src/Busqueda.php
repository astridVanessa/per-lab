<?php
// Prueba de la compuerta: versión vulnerable (R-05)

function buscarProductos(PDO $db, string $termino): array
{
    $sql = "SELECT sku, nombre, precio FROM productos WHERE nombre LIKE '%" . $termino . "%'";
    return $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
}
