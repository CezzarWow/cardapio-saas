<?php

/**
 * Gera lista de classes carregáveis pelo Container e tenta resolvê-las.
 * Registra as falhas (falta de binding ou exceções internas) e aponta os bindings
 * que ainda dependem do fallback automático.
 */

require __DIR__ . '/../vendor/autoload.php';

$container = require __DIR__ . '/../app/Config/dependencies.php';

$directories = [
    __DIR__ . '/../app/Controllers',
    __DIR__ . '/../app/Services',
    __DIR__ . '/../app/Repositories',
    __DIR__ . '/../app/Validators',
];

$classes = [];

foreach ($directories as $directory) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        if ($file->getExtension() !== 'php') {
            continue;
        }

        $content = file_get_contents($file->getRealPath());
        $namespace = '';
        if (preg_match('/namespace\\s+([^;]+);/i', $content, $matches)) {
            $namespace = trim($matches[1]);
        }

        if (preg_match_all('/class\\s+([a-zA-Z0-9_]+)/', $content, $matches)) {
            foreach ($matches[1] as $className) {
                $cleanNamespace = trim($namespace, '\\\\');
                $fqcn = $cleanNamespace ? ($cleanNamespace . '\\' . $className) : $className;
                $classes[] = $fqcn;
            }
        }
    }
}

$classes = array_unique($classes);
const IGNORED_CLASSES = [
    \App\Controllers\Admin\BaseController::class,
    \App\Services\Order\OrderStatus::class,
    \App\Services\Order\TotalCalculator::class,
];
$issues = [];
$success = 0;

foreach ($classes as $class) {
    if (in_array($class, IGNORED_CLASSES, true)) {
        continue;
    }
    if (!$container->has($class)) {
        $issues[$class] = "Dependency not found: {$class}";
        continue;
    }

    try {
        $container->get($class);
        $success++;
    } catch (\Throwable $t) {
        $issues[$class] = $t->getMessage();
    }
}

echo "Total classes: " . count($classes) . PHP_EOL;
echo "Resolvidas: {$success}" . PHP_EOL;
echo "Com problemas: " . count($issues) . PHP_EOL;

foreach ($issues as $class => $message) {
    echo "- {$class}: {$message}" . PHP_EOL;
}
