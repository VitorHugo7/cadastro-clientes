<?php
declare(strict_types=1);

final class ClienteController
{
    public function __construct(private Cliente $clientes) {}

    public function listar(): never
    {
        Http::json(['clientes' => $this->clientes->listar()]);
    }

    public function buscar(int $id): never
    {
        $cliente = $this->clientes->buscar($id);
        if ($cliente === null) {
            Http::json(['erro' => 'Cliente não encontrado.'], 404);
        }
        Http::json(['cliente' => $cliente]);
    }

    public function salvar(?int $id = null): never
    {
        if ($id !== null && $this->clientes->buscar($id) === null) {
            Http::json(['erro' => 'Cliente não encontrado.'], 404);
        }
        $validacao = Cliente::validar(Http::lerJson());
        if ($validacao['erros'] !== []) {
            Http::json(['erro' => 'Revise os campos indicados.', 'campos' => $validacao['erros']], 422);
        }
        try {
            $cliente = $this->clientes->salvar($validacao['dados'], $id);
        } catch (PDOException $erro) {
            if (($erro->errorInfo[1] ?? null) === 19 && str_contains($erro->getMessage(), 'clientes.email')) {
                Http::json([
                    'erro' => 'Já existe um cliente com este e-mail.',
                    'campos' => ['email' => 'Use um e-mail que ainda não esteja cadastrado.'],
                ], 409);
            }
            throw $erro;
        }
        if ($id === null) {
            header('Location: /api/clientes/' . $cliente['id']);
        }
        Http::json(['cliente' => $cliente], $id === null ? 201 : 200);
    }
}
