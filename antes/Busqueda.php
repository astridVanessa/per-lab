<?php
// VERSIÓN VULNERABLE (solo para laboratorio). Hallazgo R-05 / Amenaza T-03.
// Búsqueda de productos del catálogo armando la consulta SQL por concatenación.

function buscarProductos(PDO $db, string $termino): array
{
    $sql = "SELECT sku, nombre, precio FROM productos WHERE nombre LIKE '%" . $termino . "%'";
    return $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
}
