<?php
// VERSIÓN CORREGIDA. Mitiga R-08 y R-02 / T-01 y T-12.
// Verifica firma RS256, expiración, emisor y audiencia con la librería firebase/php-jwt 6.x.
// La llave pública se obtiene del entorno (en la nube: AWS Secrets Manager), nunca del código.

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

const JWT_EMISOR    = 'https://cognito-idp.us-east-1.amazonaws.com/per-usuarios';
const JWT_AUDIENCIA = 'per-tienda-web';

function usuarioDesdeToken(string $jwt, string $llavePublica): ?array
{
    try {
        JWT::$leeway = 30;                                               // tolerancia de reloj: 30 s
        $datos = (array) JWT::decode($jwt, new Key($llavePublica, 'RS256')); // solo se acepta RS256
    } catch (Throwable $e) {
        return null;                                                     // firma, formato o fecha inválidos
    }
    if (($datos['iss'] ?? '') !== JWT_EMISOR || ($datos['aud'] ?? '') !== JWT_AUDIENCIA) {
        return null;
    }
    return $datos;
}
