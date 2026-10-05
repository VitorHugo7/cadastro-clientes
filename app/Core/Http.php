<?php
declare(strict_types=1);

final class Http
{
    public static function json(array $dados, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        echo json_encode($dados, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        exit;
    }

    public static function lerJson(): array
    {
        $tipo = strtolower(trim(explode(';', $_SERVER['CONTENT_TYPE'] ?? '')[0]));
        if ($tipo !== 'application/json') {
            self::json(['erro' => 'Envie os dados no formato application/json.'], 415);
        }
        // O formulário é pequeno; limita também requisições enviadas fora da tela.
        $conteudo = file_get_contents('php://input', false, null, 0, 16385);
        if ($conteudo === false || strlen($conteudo) > 16384) {
            self::json(['erro' => 'O conteúdo enviado é muito grande.'], 413);
        }
        try {
            $objeto = json_decode($conteudo, false, 32, JSON_THROW_ON_ERROR);
        } catch (JsonException $erro) {
            self::json(['erro' => 'O JSON enviado é inválido.'], 400);
        }
        if (!$objeto instanceof stdClass) {
            self::json(['erro' => 'Envie um objeto JSON com os dados do cliente.'], 400);
        }
        return get_object_vars($objeto);
    }

    public static function permitirMetodo(string $metodo, array $permitidos): void
    {
        if (!in_array($metodo, $permitidos, true)) {
            header('Allow: ' . implode(', ', $permitidos));
            self::json(['erro' => 'Método não permitido para esta rota.'], 405);
        }
    }
}
