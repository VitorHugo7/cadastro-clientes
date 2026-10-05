<?php
declare(strict_types=1);

final class BancoDeDados
{
    public static function conectar(): PDO
    {
        $arquivo = getenv('APP_DB_PATH') ?: dirname(__DIR__, 2) . '/storage/clientes.sqlite';
        $diretorio = dirname($arquivo);
        if (!is_dir($diretorio) && !mkdir($diretorio, 0750, true) && !is_dir($diretorio)) {
            throw new RuntimeException('Não foi possível criar a pasta do banco.');
        }

        $pdo = new PDO('sqlite:' . $arquivo, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        $pdo->exec('PRAGMA busy_timeout = 5000');
        // O banco e a tabela são criados na primeira utilização.
        $pdo->exec('CREATE TABLE IF NOT EXISTS clientes (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nome TEXT NOT NULL,
            email TEXT NOT NULL COLLATE NOCASE UNIQUE,
            cep TEXT NOT NULL,
            logradouro TEXT NOT NULL,
            numero TEXT NOT NULL,
            complemento TEXT NOT NULL DEFAULT \'\',
            bairro TEXT NOT NULL DEFAULT \'\',
            cidade TEXT NOT NULL,
            uf TEXT NOT NULL,
            criado_em TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
            atualizado_em TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
        )');
        return $pdo;
    }
}
