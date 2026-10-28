<?php
// VERSIÓN VULNERABLE (solo para laboratorio). Hallazgo R-06.
// Muestra las reseñas de un producto sin codificar la salida (XSS almacenado).

function mostrarResenas(PDO $db, string $sku): string
{
    $stmt = $db->prepare('SELECT autor, comentario FROM resenas WHERE sku = ?');
    $stmt->execute([$sku]);
    $html = '';
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $fila) {
        $html .= '<p><b>' . $fila['autor'] . '</b>: ' . $fila['comentario'] . '</p>';
    }
    echo $html;
    return $html;
}
