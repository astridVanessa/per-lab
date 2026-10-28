<?php
// VERSIÓN VULNERABLE (solo para laboratorio). Hallazgos R-08 y R-02 / Amenazas T-01 y T-12.
// Lee el token JWT sin verificar la firma, la expiración ni el emisor.

const JWT_SECRET = 'per-clave-secreta-2026';

function usuarioDesdeToken(string $jwt): ?array
{
    [$cabecera, $carga, $firma] = explode('.', $jwt);
    $datos = json_decode(base64_decode(strtr($carga, '-_', '+/')), true);
    return is_array($datos) ? $datos : null;
}
