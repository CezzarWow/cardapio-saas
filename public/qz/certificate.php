<?php
/**
 * QZ Tray - Endpoint para fornecer o certificado público
 * 
 * Este endpoint retorna o certificado digital público que o QZ Tray
 * usa para validar a origem das requisições.
 */

header('Content-Type: text/plain');
header('Cache-Control: no-cache, no-store, must-revalidate');

// Caminho do certificado público (gerado pelo qz-tray.exe --certgen)
$defaultCert = __DIR__ . '/../../storage/qz/keys/digital-certificate.txt';
$legacyCert = __DIR__ . '/keys/digital-certificate.txt';
$certPath = $_ENV['QZ_CERT_PATH'] ?? $defaultCert;
if (!file_exists($certPath) && file_exists($legacyCert)) {
    $certPath = $legacyCert;
}

if (!file_exists($certPath)) {
    http_response_code(500);
    exit('Certificate not found. Please generate it using: qz-tray.exe --certgen');
}

echo file_get_contents($certPath);
