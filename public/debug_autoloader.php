<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/../vendor/autoload.php';

echo "<h1>Teste de Autoload</h1>";

try {
    echo "Tentando carregar CreateAdditionalGroupService...<br>";
    $service = new \App\Services\Additional\CreateAdditionalGroupService();
    echo "<span style='color:green'>Sucesso! Service carregado.</span><br>";
} catch (\Throwable $e) {
    echo "<span style='color:red'>Erro ao carregar Service: " . $e->getMessage() . "</span><br>";
}

try {
    echo "Tentando carregar AdditionalGroupManager...<br>";
    $manager = new \App\Domain\Additional\AdditionalGroupManager();
    echo "<span style='color:green'>Sucesso! Manager carregado.</span><br>";
} catch (\Throwable $e) {
    echo "<span style='color:red'>Erro ao carregar Manager: " . $e->getMessage() . "</span><br>";
}

try {
    echo "Tentando carregar AdditionalGroupRepository...<br>";
    $repo = new \App\Repositories\AdditionalGroupRepository();
    echo "<span style='color:green'>Sucesso! Repository carregado.</span><br>";
} catch (\Throwable $e) {
    echo "<span style='color:red'>Erro ao carregar Repository: " . $e->getMessage() . "</span><br>";
}
