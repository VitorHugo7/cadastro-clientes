<?php
declare(strict_types=1);

class ClienteController
{
    private Cliente $clientes;

    public function __construct(Cliente $clientes)
    {
        $this->clientes = $clientes;
    }

    public function listar(): void
    {
        $this->exibir('lista');
    }

    public function novo(): void
    {
        $dados = array_fill_keys(['nome', 'email', 'cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf'], '');
        $this->exibir('formulario', $dados);
    }

    public function editar(): void
    {
        $id = filter_var($_GET['id'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $dados = $id ? $this->clientes->buscar($id) : null;
        if ($dados === null) {
            http_response_code(404);
            echo 'Cliente não encontrado.';
            return;
        }
        $this->exibir('formulario', $dados, [], $id);
    }

    public function salvar(): void
    {
        // O token confirma que o formulário pertence à sessão do usuário.
        $token = $_POST['csrf'] ?? '';
        if (!is_string($token) || !hash_equals($_SESSION['csrf'], $token)) {
            http_response_code(403);
            echo 'Formulário expirado ou inválido. Volte e abra o cadastro novamente.';
            return;
        }

        $id = null;
        if (($_POST['id'] ?? '') !== '') {
            $id = filter_var($_POST['id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if (!$id || $this->clientes->buscar($id) === null) {
                http_response_code(404);
                echo 'Cliente não encontrado.';
                return;
            }
        }

        $validacao = Cliente::validar($_POST);
        $dados = $validacao['dados'];
        $erros = $validacao['erros'];

        if (!$erros) {
            try {
                $this->clientes->salvar($dados, $id);
            } catch (PDOException $erro) {
                if (($erro->errorInfo[1] ?? null) !== 19 || !str_contains($erro->getMessage(), 'clientes.email')) {
                    throw $erro;
                }
                $erros['email'] = 'Este e-mail já está cadastrado.';
            }
        }

        if ($erros) {
            http_response_code(422);
            $this->exibir('formulario', $dados, $erros, $id);
            return;
        }

        $_SESSION['mensagem'] = $id === null ? 'Cliente cadastrado com sucesso.' : 'Cliente atualizado com sucesso.';
        // Redirecionar evita repetir o cadastro ao atualizar a página.
        header('Location: ?acao=listar', true, 303);
    }

    public function consultarCep(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $cep = $_GET['cep'] ?? '';
        if (!is_string($cep)) {
            http_response_code(422);
            echo json_encode(['erro' => 'Informe um CEP com 8 dígitos.']);
            return;
        }
        try {
            $servico = new ViaCepService();
            $endereco = $servico->consultar(str_replace('-', '', trim($cep)));
            echo json_encode(['endereco' => $endereco], JSON_UNESCAPED_UNICODE);
        } catch (InvalidArgumentException $erro) {
            http_response_code(422);
            echo json_encode(['erro' => $erro->getMessage()], JSON_UNESCAPED_UNICODE);
        } catch (RuntimeException $erro) {
            http_response_code(in_array($erro->getCode(), [404, 502, 504], true) ? $erro->getCode() : 502);
            echo json_encode(['erro' => $erro->getMessage()], JSON_UNESCAPED_UNICODE);
        }
    }

    private function exibir(string $tela, array $dados = [], array $erros = [], ?int $id = null): void
    {
        $busca = is_string($_GET['busca'] ?? null) ? trim($_GET['busca']) : '';
        $clientes = $tela === 'lista' ? $this->clientes->listar($busca) : [];
        $csrf = $_SESSION['csrf'];
        $mensagem = $_SESSION['mensagem'] ?? '';
        unset($_SESSION['mensagem']);
        require __DIR__ . '/../Views/clientes.php';
    }
}
