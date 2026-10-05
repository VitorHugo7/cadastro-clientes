# Cadastro de clientes

Aplicação acadêmica simples em **PHP, MVC e SQLite**, com consulta de endereço pelo ViaCEP. Permite cadastrar, listar, consultar e editar clientes. Possui uma tela de listagem e um formulário de cadastro/edição.

## Executar

Requisitos: **PHP 8.3 ou superior**, com as extensões `pdo_sqlite` e `curl`. Não precisa de Composer, Node.js ou servidor de banco separado.

Clone este repositório ou use **Code → Download ZIP** no GitHub e extraia o arquivo. Abra a pasta do projeto, que contém `public`, `app`, `storage` e `iniciar.command`.

**No Mac:** abra o arquivo `iniciar.command` ou execute `bash iniciar.command` no Terminal, dentro da pasta do projeto. Ele procura um PHP compatível, incluindo instalações do MAMP em `/Applications/MAMP`, inicia o servidor e informa o endereço da aplicação. Mantenha a janela do Terminal aberta durante o uso.

**Pelo terminal:** entre na pasta do projeto e execute:

```sh
php -S 127.0.0.1:8085 -t public public/index.php
```

O comando `php` deve apontar para uma versão compatível. Confira a versão com `php -v` e as extensões com `php -m`. No Mac com MAMP instalado, prefira `iniciar.command` para selecionar o executável automaticamente.

Abra [http://127.0.0.1:8085](http://127.0.0.1:8085) no navegador. Para encerrar, pressione **Ctrl+C** no Terminal. Se a porta 8085 estiver ocupada por esta aplicação, use a janela do servidor que já está aberto.

O servidor embutido do PHP atende ao uso local de desenvolvimento e demonstração. O projeto não possui login. [Manual do servidor PHP](https://www.php.net/manual/en/features.commandline.webserver.php)

## Usar

1. Abra o formulário de novo cliente.
2. Preencha nome, e-mail e CEP; consulte o endereço e complete os dados restantes.
3. Salve e confira o cliente na listagem.
4. Abra a edição, altere um campo e salve novamente.

A consulta ao ViaCEP depende de internet. Se o serviço falhar ou o CEP não for encontrado, preencha o endereço manualmente. A consulta apenas auxilia o preenchimento; os clientes ficam no nosso banco. [Documentação do ViaCEP](https://viacep.com.br/)

## Banco de dados

O arquivo `storage/clientes.sqlite` é criado automaticamente, inicialmente sem clientes. Os registros permanecem salvos após fechar o navegador ou reiniciar o servidor. A pasta `storage` precisa permitir escrita.

O banco fica **fora de `public`**, que é a única pasta servida na web. Para testes isolados, a variável de ambiente `APP_DB_PATH` permite escolher outro arquivo de banco; mantenha esse arquivo fora de `public` também.

## Organização

| Arquivo ou pasta | Responsabilidade |
|---|---|
| `public/index.php` | Entrada da aplicação e definição das rotas |
| `app/Controllers/ClienteController.php` | Receber e coordenar as operações de clientes |
| `app/Controllers/CepController.php` | Receber a solicitação de consulta de CEP |
| `app/Models/Cliente.php` | Trabalhar com os dados e persistência dos clientes |
| `app/Views/clientes.php` | Estrutura da interface HTML |
| `app/Services/ViaCepService.php` | Comunicação com a API externa |
| `app/Core/BancoDeDados.php` | Conexão e preparação do SQLite via PDO |
| `app/Core/Http.php` | Apoio às requisições e respostas HTTP |
| `public/assets/` | JavaScript e CSS da interface |

O navegador envia requisições à nossa API; o PHP coordena as operações e retorna JSON. As consultas SQL usam parâmetros preparados, separando os valores informados pelo usuário da instrução SQL. [Manual do PDO](https://www.php.net/manual/en/pdo.prepared-statements.php)

## API

| Método | Rota | Operação |
|---|---|---|
| `GET` | `/api/clientes` | Listar clientes |
| `POST` | `/api/clientes` | Cadastrar cliente |
| `GET` | `/api/clientes/{id}` | Consultar um cliente |
| `PUT` | `/api/clientes/{id}` | Atualizar um cliente |
| `GET` | `/api/cep/{cep}` | Consultar endereço no ViaCEP |

Nos cadastros e atualizações, envie `Content-Type: application/json` e os campos `nome`, `email`, `cep`, `logradouro`, `numero`, `complemento`, `bairro`, `cidade` e `uf`. O `{id}` identifica um cliente existente; o `{cep}` usa oito dígitos.

O nome deve ter pelo menos dois caracteres. O e-mail deve ser válido e único. Complemento e bairro são opcionais; os demais campos são obrigatórios. As regras são verificadas no PHP, além das validações da interface. A API retorna `201` no cadastro, `200` nas consultas e edições, `422` em campos inválidos, `409` em e-mail repetido e `404` quando o recurso não existe.

## Como verificar

1. Cadastre um cliente com dados fictícios e confira sua presença na listagem.
2. Edite o número do endereço, salve e abra o cadastro novamente para conferir.
3. Reinicie o servidor e confirme que o cliente continua salvo.
4. Tente cadastrar outro cliente com o mesmo e-mail: o sistema deve informar a duplicidade.
5. Consulte um CEP válido, como `01001000`, e confira o preenchimento do endereço. Se a consulta falhar, confira se consegue preencher os campos manualmente.
