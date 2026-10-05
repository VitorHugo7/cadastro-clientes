<?php
declare(strict_types=1);

final class Cliente
{
    private PDO $banco;

    public function __construct(PDO $banco)
    {
        $this->banco = $banco;
    }

    public function listar(string $busca = ''): array
    {
        $consulta = $this->banco->prepare('SELECT * FROM clientes
            WHERE nome LIKE :nome OR email LIKE :email OR cidade LIKE :cidade
            ORDER BY id DESC');
        $consulta->execute([
            'nome' => '%' . $busca . '%',
            'email' => '%' . $busca . '%',
            'cidade' => '%' . $busca . '%',
        ]);
        return $consulta->fetchAll();
    }

    public function buscar(int $id): ?array
    {
        $consulta = $this->banco->prepare('SELECT * FROM clientes WHERE id = :id');
        $consulta->execute(['id' => $id]);
        return $consulta->fetch() ?: null;
    }

    // Regras do cadastro são verificadas no servidor, mesmo sem o formulário.
    public static function validar(array $entrada): array
    {
        $limites = [
            'nome' => 120, 'email' => 254, 'cep' => 9, 'logradouro' => 180,
            'numero' => 20, 'complemento' => 120, 'bairro' => 100,
            'cidade' => 100, 'uf' => 2,
        ];
        $dados = [];
        $erros = [];
        foreach ($limites as $campo => $limite) {
            $valor = $entrada[$campo] ?? '';
            if (!is_string($valor)) {
                $erros[$campo] = 'Informe um texto válido.';
                $valor = '';
            }
            $valor = trim($valor);
            $dados[$campo] = $valor;
            $tamanho = preg_match_all('/./us', $valor);
            if ($tamanho === false || $tamanho > $limite || preg_match('/[\x00-\x1F\x7F]/', $valor)) {
                $erros[$campo] = "Use até {$limite} caracteres, sem quebras de linha.";
            } elseif ($valor === '' && !in_array($campo, ['complemento', 'bairro'], true)) {
                $erros[$campo] = 'Preencha este campo.';
            }
        }
        if ($dados['nome'] !== '' && preg_match_all('/./us', $dados['nome']) < 2) {
            $erros['nome'] = 'Informe um nome com pelo menos 2 caracteres.';
        }
        $dados['email'] = strtolower($dados['email']);
        if ($dados['email'] !== '' && !filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
            $erros['email'] = 'Informe um e-mail válido.';
        }
        if (!preg_match('/^\d{5}-?\d{3}$/D', $dados['cep'])) {
            $erros['cep'] = 'Informe um CEP com 8 dígitos.';
        }
        $dados['cep'] = str_replace('-', '', $dados['cep']);
        $dados['uf'] = strtoupper($dados['uf']);
        $estados = explode(' ', 'AC AL AP AM BA CE DF ES GO MA MT MS MG PA PB PR PE PI RJ RN RS RO RR SC SP SE TO');
        if (!in_array($dados['uf'], $estados, true)) {
            $erros['uf'] = 'Selecione uma UF válida.';
        }
        return ['dados' => $dados, 'erros' => $erros];
    }

    public function salvar(array $dados, ?int $id = null): void
    {
        // Placeholders mantêm os valores separados do comando SQL.
        if ($id === null) {
            $sql = 'INSERT INTO clientes
                (nome, email, cep, logradouro, numero, complemento, bairro, cidade, uf)
                VALUES (:nome, :email, :cep, :logradouro, :numero, :complemento, :bairro, :cidade, :uf)';
        } else {
            $sql = 'UPDATE clientes SET nome = :nome, email = :email, cep = :cep,
                logradouro = :logradouro, numero = :numero, complemento = :complemento,
                bairro = :bairro, cidade = :cidade, uf = :uf,
                atualizado_em = CURRENT_TIMESTAMP WHERE id = :id';
            $dados['id'] = $id;
        }
        $this->banco->prepare($sql)->execute($dados);
    }
}
