<?php
// VERSIÓN CORREGIDA. Mitiga R-06 codificando la salida según el contexto HTML.

function e(string $valor): string
{
    return htmlspecialchars($valor, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8');
}

function mostrarResenas(PDO $db, string $sku): string
{
    $stmt = $db->prepare('SELECT autor, comentario FROM resenas WHERE sku = ?');
    $stmt->execute([$sku]);
    $html = '';
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $fila) {
        $html .= '<p><b>' . e($fila['autor']) . '</b>: ' . e($fila['comentario']) . '</p>';
    }
    echo $html;
    return $html;
}
