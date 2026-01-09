<?php
// public/csrf_debug.php

// Configurações de sessão iguais ao index.php
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.gc_maxlifetime', 86400);
ini_set('session.use_strict_mode', 1);

session_start();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$token = $_SESSION['csrf_token'];
$sessionId = session_id();

// Se for requisição POST AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $input = json_decode(file_get_contents('php://input'), true);
    
    $receivedToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($input['csrf_token'] ?? null);
    
    $success = hash_equals($_SESSION['csrf_token'], $receivedToken);
    
    echo json_encode([
        'success' => $success,
        'received_token' => $receivedToken,
        'session_token' => $_SESSION['csrf_token'],
        'session_id' => $sessionId,
        'cookie_sent' => $_COOKIE['PHPSESSID'] ?? 'NONE'
    ]);
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>CSRF Debug</title>
    <meta name="csrf-token" content="<?= $token ?>">
    <style>body { font-family: monospace; padding: 20px; }</style>
</head>
<body>
    <h1>CSRF Debug Tool</h1>
    <p>Session ID: <?= $sessionId ?></p>
    <p>Token (PHP): <?= $token ?></p>
    <button id="btnTest">Test AJAX Request</button>
    
    <h3>Result:</h3>
    <pre id="result">Waiting...</pre>

    <script>
        document.getElementById('btnTest').addEventListener('click', async () => {
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            try {
                const res = await fetch('csrf_debug.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token
                    },
                    credentials: 'same-origin'
                });
                const data = await res.json();
                document.getElementById('result').textContent = JSON.stringify(data, null, 2);
            } catch (e) {
                document.getElementById('result').textContent = "Error: " + e.message;
            }
        });
    </script>
</body>
</html>
