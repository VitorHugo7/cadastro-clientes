<?php
declare(strict_types=1);

// O servidor PHP entrega apenas estes arquivos públicos diretamente.
$caminho = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if (PHP_SAPI === 'cli-server' && in_array($caminho, ['/assets/style.css', '/assets/cep.js'], true)) {
    return false;
}

header('X-Content-Type-Options: nosniff');
header("Content-Security-Policy: default-src 'self'; base-uri 'self'; form-action 'self'; frame-ancestors 'none'");
header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store');

// A v2 usa sua própria sessão e seu próprio arquivo SQLite.
session_name('cadastro_clientes_v2');
session_start(['cookie_httponly' => true, 'cookie_samesite' => 'Lax', 'use_strict_mode' => true]);
if (!isset($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

require __DIR__ . '/../app/Models/Cliente.php';
require __DIR__ . '/../app/Services/ViaCepService.php';
require __DIR__ . '/../app/Controllers/ClienteController.php';

$acao = $_GET['acao'] ?? 'listar';
$rotas = ['listar' => 'GET', 'novo' => 'GET', 'editar' => 'GET', 'salvar' => 'POST', 'cep' => 'GET'];
if (!is_string($acao) || !isset($rotas[$acao]) || !in_array($caminho, ['/', '/index.php'], true)) {
    http_response_code(404);
    exit('Página não encontrada.');
}
if ($_SERVER['REQUEST_METHOD'] !== $rotas[$acao]) {
    header('Allow: ' . $rotas[$acao]);
    http_response_code(405);
    exit('Método não permitido.');
}
if ((int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 16384) {
    http_response_code(413);
    exit('O formulário enviado é muito grande.');
}

try {
    $banco = require __DIR__ . '/../config/banco.php';
    $controller = new ClienteController(new Cliente($banco));

    switch ($acao) {
        case 'listar': $controller->listar(); break;
        case 'novo': $controller->novo(); break;
        case 'editar': $controller->editar(); break;
        case 'salvar': $controller->salvar(); break;
        case 'cep': $controller->consultarCep(); break;
    }
} catch (Throwable $erro) {
    error_log($erro->getMessage());
    http_response_code(500);
    if ($acao === 'cep') {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['erro' => 'Não foi possível consultar o CEP. Preencha o endereço manualmente.']);
    } else {
        echo 'Não foi possível concluir a operação. Tente novamente.';
    }
}
