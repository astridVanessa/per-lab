<?php
// VERSIÓN VULNERABLE (solo para laboratorio). Hallazgo R-09 / Amenaza T-09 (BOLA/IDOR).
// Devuelve cualquier pedido por su número, sin revisar a quién pertenece.

function obtenerPedido(PDO $db, int $pedidoId, array $usuario): ?array
{
    $stmt = $db->prepare('SELECT id, cliente_id, total FROM pedidos WHERE id = ?');
    $stmt->execute([$pedidoId]);
    $pedido = $stmt->fetch(PDO::FETCH_ASSOC);
    return $pedido ?: null;
}
