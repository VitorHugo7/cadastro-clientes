<?php
declare(strict_types=1);

// Ponto de entrada: encaminha cada URL para sua ação.
$caminho = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$metodo = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// Somente os arquivos públicos conhecidos são servidos diretamente pelo PHP CLI.
if (PHP_SAPI === 'cli-server' && in_array($caminho, ['/assets/app.js', '/assets/style.css'], true)) {
    return false;
}

header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: same-origin');
header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self'; img-src 'self' data:; connect-src 'self'; base-uri 'none'; frame-ancestors 'none'; form-action 'self'");

$raiz = dirname(__DIR__);
require $raiz . '/app/Core/Http.php';
require $raiz . '/app/Core/BancoDeDados.php';
require $raiz . '/app/Models/Cliente.php';
require $raiz . '/app/Controllers/ClienteController.php';
require $raiz . '/app/Controllers/CepController.php';
require $raiz . '/app/Services/ViaCepService.php';

try {
    if ($caminho === '/') {
        Http::permitirMetodo($metodo, ['GET']);
        header('Content-Type: text/html; charset=utf-8');
        require $raiz . '/app/Views/clientes.php';
        exit;
    }
    if ($caminho === '/api/clientes') {
        Http::permitirMetodo($metodo, ['GET', 'POST']);
        $controller = new ClienteController(new Cliente(BancoDeDados::conectar()));
        if ($metodo === 'GET') {
            $controller->listar();
        }
        $controller->salvar();
    }
    if (preg_match('#^/api/clientes/([1-9][0-9]{0,9})$#D', $caminho, $partes)) {
        Http::permitirMetodo($metodo, ['GET', 'PUT']);
        $controller = new ClienteController(new Cliente(BancoDeDados::conectar()));
        $id = (int) $partes[1];
        if ($metodo === 'GET') {
            $controller->buscar($id);
        }
        $controller->salvar($id);
    }
    if (preg_match('#^/api/cep/([^/]+)$#D', $caminho, $partes)) {
        Http::permitirMetodo($metodo, ['GET']);
        (new CepController(new ViaCepService()))->consultar($partes[1]);
    }
    Http::json(['erro' => 'Rota não encontrada.'], 404);
} catch (Throwable $erro) {
    error_log((string) $erro);
    Http::json(['erro' => 'Não foi possível concluir a operação. Tente novamente.'], 500);
}
