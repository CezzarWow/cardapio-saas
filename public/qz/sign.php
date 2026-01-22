<?php
/**
 * QZ Tray - Endpoint para assinar mensagens
 * Baseado na documentação oficial: https://qz.io/wiki/signing-messages
 */

$KEY = __DIR__ . '/keys/private-key.pem';

// Aceita GET (padrão QZ) ou POST
$req = $_GET['request'] ?? null;
if (!$req) {
    // Fallback para POST JSON
    $input = json_decode(file_get_contents('php://input'), true);
    $req = $input['data'] ?? null;
}

if (!$req) {
    http_response_code(400);
    exit('No request provided');
}

// Converte para string se necessário
if (!is_string($req)) {
    $req = json_encode($req, JSON_UNESCAPED_UNICODE);
}

$privateKey = openssl_get_privatekey(file_get_contents($KEY));

if (!$privateKey) {
    http_response_code(500);
    exit('Invalid private key');
}

$signature = null;
openssl_sign($req, $signature, $privateKey, "sha512");

if ($signature) {
    header("Content-type: text/plain");
    echo base64_encode($signature);
    exit(0);
}

echo '<h1>Error signing message</h1>';
exit(1);
