<?php
// VERSIÓN VULNERABLE (solo para laboratorio). Hallazgo R-10 / Amenaza T-04.
// Calcula el total con el precio que envía el navegador.

function calcularTotal(PDO $db, array $peticion): float
{
    $precio = (float) $_POST['precio'];
    $cantidad = (int) $peticion['cantidad'];
    return $precio * $cantidad;
}
