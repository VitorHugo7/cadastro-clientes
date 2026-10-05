# Cadastro de clientes — V2

Aplicação acadêmica em **PHP, MVC tradicional e SQLite**. Permite cadastrar, listar, buscar e editar clientes. A consulta de endereço usa o ViaCEP pelo backend; se ela falhar, o endereço pode ser preenchido manualmente.

## Executar localmente

Requisitos: **PHP 8.3 ou superior**, com as extensões `pdo_sqlite` e `curl`. Não é necessário Composer, Node.js ou servidor de banco separado.

Clone o repositório ou baixe e extraia o ZIP. No Mac, abra `iniciar.command` ou execute `bash iniciar.command` na pasta do projeto. O arquivo procura um PHP compatível, incluindo instalações do MAMP.

Também é possível iniciar pelo terminal, dentro da pasta do projeto:

```sh
php -S 127.0.0.1:8086 -t public public/index.php
```

Abra [http://127.0.0.1:8086](http://127.0.0.1:8086). Mantenha o Terminal aberto e use **Ctrl+C** para encerrar. A V2 usa a porta 8086; a versão original permanece na porta 8085.

## Usar

1. Abra o formulário de novo cliente e preencha os dados.
2. Consulte o CEP, confira o endereço e complete os campos restantes.
3. Salve e procure o cliente na listagem.
4. Abra a edição, altere os dados e salve novamente.

O banco SQLite é criado automaticamente em `storage`, inicialmente vazio. Os cadastros permanecem salvos após reiniciar o servidor. Essa pasta precisa permitir escrita e fica fora de `public`, a única pasta servida na web. Somente a consulta ao ViaCEP depende de internet.

## Organização MVC

| Arquivo | Responsabilidade |
|---|---|
| `public/index.php` | Identificar a ação solicitada e encaminhar a requisição |
| `config/banco.php` | Criar a conexão com SQLite e retornar o PDO |
| `app/Models/Cliente.php` | Validar os dados e executar as operações SQL |
| `app/Controllers/ClienteController.php` | Coordenar as operações e renderizar a tela |
| `app/Views/clientes.php` | Apresentar a listagem e o formulário em HTML/PHP |
| `app/Services/ViaCepService.php` | Consultar o ViaCEP pelo PHP |
| `public/assets/style.css` | Estilizar a interface |
| `public/assets/cep.js` | Consultar e preencher o endereço sem recarregar a tela |

As telas são renderizadas no PHP. Formulários `GET` fazem a busca e formulários `POST` salvam o cadastro, com um token CSRF para proteção. O JavaScript é usado somente na consulta de CEP.

O parâmetro `acao` seleciona a operação: `listar` (com `busca` opcional), `novo`, `editar` (com `id`), `salvar` por `POST` ou `cep` para obter o endereço em JSON. Por exemplo: `?acao=editar&id=1` e `?acao=cep&cep=01001000`.

## Entrega pelo GitHub

Envie o código-fonte, este README e o iniciador. Deixe fora do repositório os arquivos do banco em `storage` e a pasta `.vscode`. Quem baixar o projeto poderá executá-lo localmente e terá um banco vazio criado automaticamente.
