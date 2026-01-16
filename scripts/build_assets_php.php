<?php
// build_assets_php.php
// Concatena e minifica JS/CSS sem Node
$root = dirname(__DIR__);
$projectRoot = $root . DIRECTORY_SEPARATOR;

$jsEntries = [
    'public/js/cardapio/utils.js',
    'public/js/cardapio/modules/cart-state.js',
    'public/js/cardapio/modules/cart-view.js',
    'public/js/cardapio/cart.js',
    'public/js/cardapio/modals.js',
    'public/js/cardapio/modals-product.js',
    'public/js/cardapio/modules/combo-validator.js',
    'public/js/cardapio/modules/combo-view.js',
    'public/js/cardapio/modules/combo-controller.js',
    'public/js/cardapio/modals-combo.js',
    'public/js/cardapio/checkout-order.js',
    'public/js/cardapio/checkout-fields.js',
    'public/js/cardapio/checkout-modals.js',
    'public/js/cardapio/checkout.js',
    'public/js/cardapio.js'
];

$cssEntries = [
    'public/css/base.css',
    'public/css/cards.css',
    'public/css/modals/index.css',
    'public/css/form.css',
    'public/css/publico/index.css',
    'public/css/checkout.css',
    'public/css/cardapio-layout.css',
    'public/css/cardapio-badges.css'
];

$outDir = $projectRoot . 'public' . DIRECTORY_SEPARATOR . 'dist';
if (!is_dir($outDir)) mkdir($outDir, 0755, true);

function readFiles(array $entries, string $projectRoot): string {
    $combined = '';
    foreach ($entries as $entry) {
        $path = $projectRoot . $entry;
        if (!file_exists($path)) {
            fwrite(STDERR, "Warning: file not found: $path\n");
            continue;
        }
        $combined .= "\n/* ---- FILE: $entry ---- */\n";
        $combined .= file_get_contents($path) . "\n";
    }
    return $combined;
}

function minifyJs(string $src): string {
    // Remove block comments
    $src = preg_replace('#/\*.*?\*/#s', '', $src);
    // Remove line comments safely: remove lines that start with // (ignores inline // in URLs)
    $lines = preg_split('/\r?\n/', $src);
    $out = [];
    foreach ($lines as $line) {
        $trim = ltrim($line);
        if (strpos($trim, '//') === 0) continue; // full-line comment
        $out[] = $line;
    }
    $src = implode("\n", $out);
    // Collapse whitespace
    $src = preg_replace('/\s+/', ' ', $src);
    return trim($src);
}

function minifyCss(string $src): string {
    // Remove comments
    $src = preg_replace('#/\*.*?\*/#s', '', $src);
    // Collapse whitespace
    $src = preg_replace('/\s+/', ' ', $src);
    // Remove unnecessary spaces
    $src = str_replace([' {', '{ '], '{', $src);
    $src = str_replace([' }', '} '], '}', $src);
    $src = str_replace(['; ', ' ;'], ';', $src);
    $src = str_replace([': ', ' :'], ':', $src);
    return trim($src);
}

// Build JS
$combinedJs = readFiles($jsEntries, $projectRoot);
$minJs = minifyJs($combinedJs);
$jsHash = substr(md5($minJs), 0, 10);
$jsFileName = "cardapio.$jsHash.js";
file_put_contents($outDir . DIRECTORY_SEPARATOR . $jsFileName, $minJs);

// Build CSS
$combinedCss = readFiles($cssEntries, $projectRoot);
$minCss = minifyCss($combinedCss);
$cssHash = substr(md5($minCss), 0, 10);
$cssFileName = "cardapio.$cssHash.css";
file_put_contents($outDir . DIRECTORY_SEPARATOR . $cssFileName, $minCss);

// Manifest
$manifest = [
    'cardapio.js' => '/dist/' . $jsFileName,
    'cardapio.css' => '/dist/' . $cssFileName,
];
file_put_contents($outDir . DIRECTORY_SEPARATOR . 'assets-manifest.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

fwrite(STDOUT, "Build complete:\n");
fwrite(STDOUT, " - JS: public/dist/$jsFileName\n");
fwrite(STDOUT, " - CSS: public/dist/$cssFileName\n");
fwrite(STDOUT, " - Manifest: public/dist/assets-manifest.json\n");
