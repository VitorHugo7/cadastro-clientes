<?php
function escapar($valor)
{
    return htmlspecialchars((string) $valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de clientes</title>
    <link rel="stylesheet" href="assets/style.css">
    <script src="assets/cep.js" defer></script>
</head>
<body>
    <a class="skip-link" href="#conteudo">Pular para o conteúdo</a>
    <header class="site-header">
        <div class="header-inner"><a class="brand" href="?acao=listar">Cadastro de clientes</a></div>
    </header>

    <main id="conteudo" class="page-shell">
        <?php if ($mensagem !== ''): ?>
            <div class="notice" role="status"><?= escapar($mensagem) ?></div>
        <?php endif; ?>

        <?php if ($tela === 'lista'): ?>
            <div class="page-heading">
                <h1>Clientes</h1>
                <a class="button button-primary" href="?acao=novo">Novo cliente</a>
            </div>
            <section class="list-card" aria-label="Clientes cadastrados">
                <div class="list-toolbar">
                    <form class="search-form" method="get">
                        <input type="hidden" name="acao" value="listar">
                        <label class="sr-only" for="busca">Buscar por nome, e-mail ou cidade</label>
                        <input id="busca" name="busca" type="search" value="<?= escapar($busca) ?>" placeholder="Nome, e-mail ou cidade" maxlength="120">
                        <button class="button button-secondary" type="submit">Buscar</button>
                        <?php if ($busca !== ''): ?><a href="?acao=listar">Limpar</a><?php endif; ?>
                    </form>
                    <p class="list-summary"><?= count($clientes) ?> cliente(s)</p>
                </div>
                <?php if (count($clientes) === 0): ?>
                    <div class="empty-state">
                        <h2><?= $busca !== '' ? 'Nenhum cliente encontrado' : 'Nenhum cliente cadastrado' ?></h2>
                        <p><?= $busca !== '' ? 'Tente buscar por outro nome, e-mail ou cidade.' : 'Clique em Novo cliente para adicionar um cadastro.' ?></p>
                    </div>
                <?php else: ?>
                    <div class="table-scroll">
                        <table>
                            <caption class="sr-only">Clientes cadastrados</caption>
                            <thead><tr><th scope="col">Nome</th><th scope="col">E-mail</th><th scope="col">Cidade / UF</th><th scope="col">Ações</th></tr></thead>
                            <tbody>
                                <?php foreach ($clientes as $cliente): ?>
                                    <tr>
                                        <td class="client-name"><?= escapar($cliente['nome']) ?></td>
                                        <td><?= escapar($cliente['email']) ?></td>
                                        <td><?= escapar($cliente['cidade']) ?> / <?= escapar($cliente['uf']) ?></td>
                                        <td><a class="edit-button" href="?acao=editar&amp;id=<?= (int) $cliente['id'] ?>" aria-label="Editar <?= escapar($cliente['nome']) ?>">Editar</a></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </section>
        <?php else: ?>
            <a class="back-link" href="?acao=listar">← Voltar para clientes</a>
            <div class="page-heading">
                <h1><?= $id === null ? 'Novo cliente' : 'Editar cliente' ?></h1>
                <span class="required-note">* Campos obrigatórios</span>
            </div>
            <form class="form-card" method="post" action="?acao=salvar">
                <input type="hidden" name="id" value="<?= escapar($id) ?>">
                <input type="hidden" name="csrf" value="<?= escapar($csrf) ?>">
                <?php if ($erros): ?>
                    <div class="notice notice-error form-notice" role="alert"><?= escapar($erros['geral'] ?? 'Confira os campos indicados antes de salvar.') ?></div>
                <?php endif; ?>
                <fieldset>
                    <legend>Dados do cliente</legend>
                    <div class="form-grid two-columns">
                        <div class="field">
                            <label for="nome">Nome completo *</label>
                            <input id="nome" name="nome" value="<?= escapar($dados['nome']) ?>" autocomplete="name" required minlength="2" maxlength="120" aria-invalid="<?= isset($erros['nome']) ? 'true' : 'false' ?>" aria-describedby="erro-nome">
                            <small id="erro-nome" class="field-error"><?= escapar($erros['nome'] ?? '') ?></small>
                        </div>
                        <div class="field">
                            <label for="email">E-mail *</label>
                            <input id="email" name="email" type="email" value="<?= escapar($dados['email']) ?>" autocomplete="email" required maxlength="254" aria-invalid="<?= isset($erros['email']) ? 'true' : 'false' ?>" aria-describedby="erro-email">
                            <small id="erro-email" class="field-error"><?= escapar($erros['email'] ?? '') ?></small>
                        </div>
                    </div>
                </fieldset>
                <fieldset>
                    <legend>Endereço</legend>
                    <p class="section-description">Busque pelo CEP ou preencha o endereço manualmente.</p>
                    <noscript><p class="section-description">A busca de CEP precisa de JavaScript. Você pode preencher o endereço e salvar normalmente.</p></noscript>
                    <div class="cep-row">
                        <div class="field">
                            <label for="cep">CEP *</label>
                            <input id="cep" name="cep" value="<?= escapar($dados['cep']) ?>" autocomplete="postal-code" inputmode="numeric" required pattern="[0-9]{5}-?[0-9]{3}" maxlength="9" placeholder="00000-000" aria-invalid="<?= isset($erros['cep']) ? 'true' : 'false' ?>" aria-describedby="erro-cep">
                            <small id="erro-cep" class="field-error"><?= escapar($erros['cep'] ?? '') ?></small>
                        </div>
                        <button id="buscar-cep" class="button button-secondary" type="button" disabled>Buscar CEP</button>
                    </div>
                    <p id="mensagem-cep" class="cep-message" role="status" aria-live="polite" hidden></p>
                    <div class="form-grid address-line">
                        <div class="field">
                            <label for="logradouro">Rua / avenida *</label>
                            <input id="logradouro" name="logradouro" value="<?= escapar($dados['logradouro']) ?>" autocomplete="address-line1" required maxlength="180" aria-invalid="<?= isset($erros['logradouro']) ? 'true' : 'false' ?>" aria-describedby="erro-logradouro">
                            <small id="erro-logradouro" class="field-error"><?= escapar($erros['logradouro'] ?? '') ?></small>
                        </div>
                        <div class="field">
                            <label for="numero">Número *</label>
                            <input id="numero" name="numero" value="<?= escapar($dados['numero']) ?>" required maxlength="20" placeholder="Ex.: 120 ou s/n" aria-invalid="<?= isset($erros['numero']) ? 'true' : 'false' ?>" aria-describedby="erro-numero">
                            <small id="erro-numero" class="field-error"><?= escapar($erros['numero'] ?? '') ?></small>
                        </div>
                    </div>
                    <div class="form-grid two-columns">
                        <div class="field">
                            <label for="complemento">Complemento (opcional)</label>
                            <input id="complemento" name="complemento" value="<?= escapar($dados['complemento']) ?>" autocomplete="address-line2" maxlength="120" aria-invalid="<?= isset($erros['complemento']) ? 'true' : 'false' ?>" aria-describedby="erro-complemento">
                            <small id="erro-complemento" class="field-error"><?= escapar($erros['complemento'] ?? '') ?></small>
                        </div>
                        <div class="field">
                            <label for="bairro">Bairro (se houver)</label>
                            <input id="bairro" name="bairro" value="<?= escapar($dados['bairro']) ?>" maxlength="100" aria-invalid="<?= isset($erros['bairro']) ? 'true' : 'false' ?>" aria-describedby="erro-bairro">
                            <small id="erro-bairro" class="field-error"><?= escapar($erros['bairro'] ?? '') ?></small>
                        </div>
                    </div>
                    <div class="form-grid city-line">
                        <div class="field">
                            <label for="cidade">Cidade *</label>
                            <input id="cidade" name="cidade" value="<?= escapar($dados['cidade']) ?>" autocomplete="address-level2" required maxlength="100" aria-invalid="<?= isset($erros['cidade']) ? 'true' : 'false' ?>" aria-describedby="erro-cidade">
                            <small id="erro-cidade" class="field-error"><?= escapar($erros['cidade'] ?? '') ?></small>
                        </div>
                        <div class="field">
                            <label for="uf">Estado *</label>
                            <select id="uf" name="uf" autocomplete="address-level1" required aria-invalid="<?= isset($erros['uf']) ? 'true' : 'false' ?>" aria-describedby="erro-uf">
                                <option value="">Selecione</option>
                                <?php foreach (['AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA', 'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE', 'PI', 'RJ', 'RN', 'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO'] as $estado): ?>
                                    <option value="<?= $estado ?>" <?= $dados['uf'] === $estado ? 'selected' : '' ?>><?= $estado ?></option>
                                <?php endforeach; ?>
                            </select>
                            <small id="erro-uf" class="field-error"><?= escapar($erros['uf'] ?? '') ?></small>
                        </div>
                    </div>
                </fieldset>
                <div class="form-actions">
                    <a class="button button-secondary" href="?acao=listar">Cancelar</a>
                    <button class="button button-primary" type="submit">Salvar cliente</button>
                </div>
            </form>
        <?php endif; ?>
    </main>
    <footer class="site-footer">Cadastro de clientes</footer>
</body>
</html>
