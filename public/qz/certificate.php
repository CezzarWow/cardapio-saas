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
$certPath = __DIR__ . '/keys/digital-certificate.txt';

if (!file_exists($certPath)) {
    http_response_code(500);
    exit('Certificate not found. Please generate it using: qz-tray.exe --certgen');
}

echo file_get_contents($certPath);
