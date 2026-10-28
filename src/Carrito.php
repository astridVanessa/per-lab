<?php
// VERSIÓN CORREGIDA. Mitiga R-10 / T-04: el precio siempre se toma del catálogo en el servidor.

function calcularTotal(PDO $db, array $peticion): float
{
    $cantidad = filter_var($peticion['cantidad'] ?? null, FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1, 'max_range' => 50]]);
    if ($cantidad === false) {
        throw new InvalidArgumentException('Cantidad inválida');
    }
    $stmt = $db->prepare('SELECT precio FROM productos WHERE sku = ?');
    $stmt->execute([(string) ($peticion['sku'] ?? '')]);
    $precio = $stmt->fetchColumn();
    if ($precio === false) {
        throw new InvalidArgumentException('Producto inexistente');
    }
    return round((float) $precio * $cantidad, 2);
}
