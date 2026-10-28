<?php
// VERSIÓN CORREGIDA. Mitiga R-09 / T-09: el pedido solo se entrega a su dueño.

function obtenerPedido(PDO $db, int $pedidoId, array $usuario): ?array
{
    $stmt = $db->prepare('SELECT id, cliente_id, total FROM pedidos WHERE id = ? AND cliente_id = ?');
    $stmt->execute([$pedidoId, (int) $usuario['sub']]);                 // el dueño sale del token verificado
    $pedido = $stmt->fetch(PDO::FETCH_ASSOC);
    return $pedido ?: null;
}
