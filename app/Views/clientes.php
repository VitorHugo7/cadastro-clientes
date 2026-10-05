<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#153c48">
    <title>Cadastro de clientes</title>
    <link rel="stylesheet" href="/assets/style.css">
    <script src="/assets/app.js" defer></script>
</head>
<body>
    <a class="skip-link" href="#conteudo">Pular para o conteúdo</a>
    <header class="site-header">
        <div class="header-inner">
            <a class="brand" href="/" aria-label="Cadastro de clientes — início">
                <span class="brand-mark" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none"><circle cx="9" cy="8" r="3"/><path d="M3.5 19v-2a5.5 5.5 0 0 1 11 0v2M16 5.5a3 3 0 0 1 0 6M18 14a4 4 0 0 1 2.5 3.7V19"/></svg>
                </span>
                <span>Cadastro<span class="brand-secondary"> de clientes</span></span>
            </a>
        </div>
    </header>

    <main id="conteudo" class="page-shell">
        <noscript><div class="notice notice-error">Ative o JavaScript no navegador para consultar e cadastrar clientes.</div></noscript>
        <div id="mensagem-global" class="notice" role="status" aria-live="polite" hidden></div>

        <section id="vista-lista" aria-labelledby="titulo-lista">
            <div class="page-heading">
                <div>
                    <p class="eyebrow">AGENDA DE CONTATOS</p>
                    <h1 id="titulo-lista" tabindex="-1">Seus clientes</h1>
                </div>
                <button id="novo-cliente" class="button button-primary" type="button">
                    <svg viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M10 4v12M4 10h12"/></svg>
                    Novo cliente
                </button>
            </div>

            <div class="list-card">
                <div class="list-toolbar">
                    <div class="search-field">
                        <svg viewBox="0 0 20 20" fill="none" aria-hidden="true"><circle cx="8.7" cy="8.7" r="5.7"/><path d="m13 13 4 4"/></svg>
                        <label class="sr-only" for="busca">Buscar por nome, e-mail ou cidade</label>
                        <input id="busca" type="search" placeholder="Buscar por nome, e-mail ou cidade" autocomplete="off" disabled>
                    </div>
                    <p id="resumo-lista" class="list-summary" role="status" aria-live="polite">Carregando clientes…</p>
                </div>

                <div id="estado-carregando" class="empty-state" role="status">
                    <span class="spinner" aria-hidden="true"></span>
                    <p>Buscando seus clientes…</p>
                </div>
                <div id="estado-erro" class="empty-state" hidden>
                    <div class="empty-icon" aria-hidden="true">!</div>
                    <h2>Não foi possível carregar os clientes</h2>
                    <p id="erro-lista-texto">Verifique a conexão e tente novamente.</p>
                    <button id="tentar-novamente" class="button button-secondary" type="button">Tentar novamente</button>
                </div>
                <div id="estado-vazio" class="empty-state" hidden>
                    <div class="empty-icon" aria-hidden="true">
                        <svg viewBox="0 0 32 32" fill="none"><rect x="7" y="5" width="20" height="24" rx="3"/><path d="M4 10h6M4 16h6M4 22h6M13 24v-1a4 4 0 0 1 8 0v1"/><circle cx="17" cy="13" r="3"/></svg>
                    </div>
                    <h2>Seu primeiro cliente começa aqui</h2>
                    <p>Clique em <strong>Novo cliente</strong> para adicionar um contato.<br>Você poderá consultar e editar os dados quando precisar.</p>
                </div>
                <div id="estado-sem-resultados" class="empty-state" hidden>
                    <h2>Nenhum cliente encontrado</h2>
                    <p>Tente buscar por outro nome, e-mail ou cidade.</p>
                    <button id="limpar-busca" class="button button-secondary" type="button">Limpar busca</button>
                </div>
                <div id="tabela-container" class="table-scroll" hidden>
                    <table>
                        <caption class="sr-only">Clientes cadastrados</caption>
                        <thead><tr><th scope="col">Cliente</th><th scope="col">E-mail</th><th scope="col">Cidade / UF</th><th scope="col"><span class="sr-only">Ações</span></th></tr></thead>
                        <tbody id="clientes-corpo"></tbody>
                    </table>
                </div>
            </div>
        </section>

        <section id="vista-formulario" aria-labelledby="titulo-formulario" hidden>
            <button id="voltar-clientes" class="back-link" type="button">
                <svg viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="m8 4-6 6 6 6M2 10h15"/></svg>
                Voltar para clientes
            </button>
            <div class="page-heading form-heading">
                <div>
                    <p class="eyebrow">CADASTRO</p>
                    <h1 id="titulo-formulario" tabindex="-1">Novo cliente</h1>
                    <p id="descricao-formulario" class="page-description">Preencha os dados abaixo para adicionar um cliente.</p>
                </div>
                <span class="required-note">* Campos obrigatórios</span>
            </div>

            <form id="formulario-cliente" class="form-card">
                <div id="erro-formulario" class="notice notice-error form-notice" role="alert" hidden></div>
                <fieldset>
                    <legend><span class="section-number" aria-hidden="true">01</span> Dados do cliente</legend>
                    <p class="section-description">Informações para identificar e entrar em contato.</p>
                    <div class="form-grid two-columns">
                        <div class="field">
                            <label for="nome">Nome completo <span aria-hidden="true">*</span></label>
                            <input id="nome" name="nome" autocomplete="name" required minlength="2" maxlength="120" placeholder="Nome do cliente" aria-describedby="erro-nome">
                            <small id="erro-nome" class="field-error"></small>
                        </div>
                        <div class="field">
                            <label for="email">E-mail <span aria-hidden="true">*</span></label>
                            <input id="email" name="email" type="email" autocomplete="email" required maxlength="254" placeholder="nome@exemplo.com" aria-describedby="erro-email">
                            <small id="erro-email" class="field-error"></small>
                        </div>
                    </div>
                </fieldset>

                <fieldset>
                    <legend><span class="section-number" aria-hidden="true">02</span> Endereço</legend>
                    <p class="section-description">Consulte o CEP para preencher o endereço ou informe os dados manualmente.</p>
                    <div class="cep-row">
                        <div class="field">
                            <label for="cep">CEP <span aria-hidden="true">*</span></label>
                            <input id="cep" name="cep" autocomplete="postal-code" inputmode="numeric" required pattern="[0-9]{5}-?[0-9]{3}" maxlength="9" placeholder="00000-000" aria-describedby="erro-cep">
                            <small id="erro-cep" class="field-error"></small>
                        </div>
                        <button id="buscar-cep" class="button button-secondary" type="button">
                            <svg viewBox="0 0 20 20" fill="none" aria-hidden="true"><circle cx="8.7" cy="8.7" r="5.7"/><path d="m13 13 4 4"/></svg>
                            <span>Buscar CEP</span>
                        </button>
                    </div>
                    <p id="mensagem-cep" class="cep-message" role="status" aria-live="polite" hidden></p>

                    <div class="form-grid address-line">
                        <div class="field">
                            <label for="logradouro">Rua / avenida <span aria-hidden="true">*</span></label>
                            <input id="logradouro" name="logradouro" autocomplete="address-line1" required maxlength="180" placeholder="Nome da rua ou avenida" aria-describedby="erro-logradouro">
                            <small id="erro-logradouro" class="field-error"></small>
                        </div>
                        <div class="field">
                            <label for="numero">Número <span aria-hidden="true">*</span></label>
                            <input id="numero" name="numero" required maxlength="20" placeholder="Ex.: 120 ou s/n" aria-describedby="erro-numero">
                            <small id="erro-numero" class="field-error"></small>
                        </div>
                    </div>
                    <div class="form-grid two-columns">
                        <div class="field">
                            <label for="complemento">Complemento <span class="optional-label">(opcional)</span></label>
                            <input id="complemento" name="complemento" autocomplete="address-line2" maxlength="120" placeholder="Apartamento, bloco, referência…" aria-describedby="erro-complemento">
                            <small id="erro-complemento" class="field-error"></small>
                        </div>
                        <div class="field">
                            <label for="bairro">Bairro <span class="optional-label">(se houver)</span></label>
                            <input id="bairro" name="bairro" maxlength="100" placeholder="Nome do bairro" aria-describedby="erro-bairro">
                            <small id="erro-bairro" class="field-error"></small>
                        </div>
                    </div>
                    <div class="form-grid city-line">
                        <div class="field">
                            <label for="cidade">Cidade <span aria-hidden="true">*</span></label>
                            <input id="cidade" name="cidade" autocomplete="address-level2" required maxlength="100" placeholder="Nome da cidade" aria-describedby="erro-cidade">
                            <small id="erro-cidade" class="field-error"></small>
                        </div>
                        <div class="field">
                            <label for="uf">Estado <span aria-hidden="true">*</span></label>
                            <select id="uf" name="uf" autocomplete="address-level1" required aria-describedby="erro-uf">
                                <option value="">Selecione</option>
                                <option value="AC">AC</option><option value="AL">AL</option><option value="AP">AP</option><option value="AM">AM</option><option value="BA">BA</option><option value="CE">CE</option><option value="DF">DF</option><option value="ES">ES</option><option value="GO">GO</option><option value="MA">MA</option><option value="MT">MT</option><option value="MS">MS</option><option value="MG">MG</option><option value="PA">PA</option><option value="PB">PB</option><option value="PR">PR</option><option value="PE">PE</option><option value="PI">PI</option><option value="RJ">RJ</option><option value="RN">RN</option><option value="RS">RS</option><option value="RO">RO</option><option value="RR">RR</option><option value="SC">SC</option><option value="SP">SP</option><option value="SE">SE</option><option value="TO">TO</option>
                            </select>
                            <small id="erro-uf" class="field-error"></small>
                        </div>
                    </div>
                </fieldset>
                <div class="form-actions">
                    <p>Confira as informações antes de salvar.</p>
                    <div class="form-buttons">
                        <button id="cancelar" class="button button-secondary" type="button">Cancelar</button>
                        <button id="salvar" class="button button-primary" type="submit">Salvar cliente</button>
                    </div>
                </div>
            </form>
        </section>
    </main>
    <footer class="site-footer"><span>Cadastro de clientes</span></footer>
</body>
</html>
