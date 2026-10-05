'use strict';

// A View conversa apenas com o nosso backend. A consulta ao ViaCEP ocorre no PHP.
const campos = ['nome', 'email', 'cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf'];
const $ = (id) => document.getElementById(id);
const formulario = $('formulario-cliente');
let clientes = [];
let clienteAtual = null;
let salvando = false;
let versaoFormulario = 0;
let consultaCep = null;
let versaoLista = 0;
let edicaoPendente = 0;

class ErroApi extends Error {
    constructor(mensagem, status = 0, camposInvalidos = {}) {
        super(mensagem);
        this.status = status;
        this.campos = camposInvalidos;
    }
}

async function requisitar(caminho, opcoes = {}) {
    let resposta;
    try {
        resposta = await fetch(caminho, {
            ...opcoes,
            headers: { Accept: 'application/json', ...(opcoes.body ? { 'Content-Type': 'application/json' } : {}), ...opcoes.headers },
        });
    } catch (erro) {
        if (erro.name === 'AbortError') throw erro;
        throw new ErroApi('Não foi possível se conectar ao servidor. Verifique a conexão e tente novamente.');
    }
    let dados;
    try {
        dados = await resposta.json();
    } catch {
        throw new ErroApi('O servidor retornou uma resposta inesperada. Tente novamente.', resposta.status);
    }
    if (!resposta.ok) {
        throw new ErroApi(dados.erro || 'Não foi possível concluir a operação.', resposta.status, dados.campos || {});
    }
    return dados;
}

function mensagemGlobal(texto, tipo = 'success') {
    const aviso = $('mensagem-global');
    aviso.textContent = texto;
    aviso.className = `notice${tipo === 'error' ? ' notice-error' : tipo === 'info' ? ' notice-info' : ''}`;
    aviso.hidden = !texto;
}

function normalizarTexto(texto) {
    return String(texto || '').normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
}

function renderizarClientes() {
    const termo = normalizarTexto($('busca').value.trim());
    const encontrados = clientes.filter((cliente) => normalizarTexto(`${cliente.nome} ${cliente.email} ${cliente.cidade} ${cliente.uf}`).includes(termo));
    const corpo = $('clientes-corpo');
    corpo.replaceChildren();
    $('estado-carregando').hidden = true;
    $('estado-erro').hidden = true;
    $('estado-vazio').hidden = clientes.length !== 0;
    $('estado-sem-resultados').hidden = clientes.length === 0 || encontrados.length !== 0;
    $('tabela-container').hidden = encontrados.length === 0;
    $('busca').disabled = false;
    $('resumo-lista').textContent = termo
        ? `${encontrados.length} de ${clientes.length} ${clientes.length === 1 ? 'cliente' : 'clientes'}`
        : `${clientes.length} ${clientes.length === 1 ? 'cliente cadastrado' : 'clientes cadastrados'}`;

    for (const cliente of encontrados) {
        const linha = document.createElement('tr');
        for (const [texto, classe] of [[cliente.nome, 'client-name'], [cliente.email, 'client-email'], [`${cliente.cidade} / ${cliente.uf}`, 'client-city']]) {
            const celula = document.createElement('td');
            celula.className = classe;
            celula.textContent = texto;
            linha.append(celula);
        }
        const acoes = document.createElement('td');
        const editar = document.createElement('button');
        editar.type = 'button';
        editar.className = 'edit-button';
        editar.textContent = 'Editar';
        editar.setAttribute('aria-label', `Editar cadastro de ${cliente.nome}`);
        editar.addEventListener('click', () => abrirEdicao(cliente.id, editar));
        acoes.append(editar);
        linha.append(acoes);
        corpo.append(linha);
    }
}

async function carregarClientes() {
    const requisicao = ++versaoLista;
    $('estado-carregando').hidden = false;
    for (const id of ['estado-vazio', 'estado-sem-resultados', 'estado-erro', 'tabela-container']) $(id).hidden = true;
    $('busca').disabled = true;
    $('resumo-lista').textContent = 'Carregando clientes…';
    try {
        const dados = await requisitar('/api/clientes');
        if (requisicao !== versaoLista) return;
        clientes = dados.clientes;
        renderizarClientes();
    } catch (erro) {
        if (requisicao !== versaoLista) return;
        $('estado-carregando').hidden = true;
        $('estado-erro').hidden = false;
        $('erro-lista-texto').textContent = erro.message;
        $('resumo-lista').textContent = 'Consulta indisponível';
    }
}

function limparErros() {
    $('erro-formulario').hidden = true;
    $('erro-formulario').textContent = '';
    for (const campo of campos) {
        $(`erro-${campo}`).textContent = '';
        $(campo).removeAttribute('aria-invalid');
    }
}

function interromperConsultaCep() {
    if (consultaCep) consultaCep.abort();
    consultaCep = null;
    $('buscar-cep').disabled = salvando;
    $('buscar-cep').querySelector('span').textContent = 'Buscar CEP';
}

function abrirFormulario(cliente = null) {
    edicaoPendente++;
    versaoFormulario++;
    interromperConsultaCep();
    clienteAtual = cliente ? cliente.id : null;
    formulario.reset();
    limparErros();
    mensagemGlobal('');
    $('mensagem-cep').hidden = true;
    if (cliente) {
        for (const campo of campos) $(campo).value = cliente[campo] ?? '';
        formatarCep();
    }
    $('titulo-formulario').textContent = cliente ? 'Editar cliente' : 'Novo cliente';
    $('descricao-formulario').textContent = cliente ? 'Atualize os dados e salve as alterações.' : 'Preencha os dados abaixo para adicionar um cliente.';
    $('salvar').textContent = cliente ? 'Salvar alterações' : 'Salvar cliente';
    $('vista-lista').hidden = true;
    $('vista-formulario').hidden = false;
    $('titulo-formulario').focus();
    window.scrollTo({ top: 0, behavior: 'instant' });
}

async function abrirEdicao(id, botao) {
    const requisicao = ++edicaoPendente;
    botao.disabled = true;
    botao.textContent = 'Abrindo…';
    mensagemGlobal('');
    try {
        const dados = await requisitar(`/api/clientes/${id}`);
        if (requisicao !== edicaoPendente) return;
        abrirFormulario(dados.cliente);
    } catch (erro) {
        if (requisicao !== edicaoPendente) return;
        mensagemGlobal(erro.message, 'error');
        if (erro.status === 404) carregarClientes();
    } finally {
        botao.disabled = false;
        botao.textContent = 'Editar';
    }
}

function voltarParaLista() {
    if (salvando) return;
    edicaoPendente++;
    versaoFormulario++;
    interromperConsultaCep();
    $('vista-formulario').hidden = true;
    $('vista-lista').hidden = false;
    $('titulo-lista').focus();
    window.scrollTo({ top: 0, behavior: 'instant' });
}

function formatarCep() {
    const numeros = $('cep').value.replace(/\D/g, '').slice(0, 8);
    $('cep').value = numeros.length > 5 ? `${numeros.slice(0, 5)}-${numeros.slice(5)}` : numeros;
}

function mensagemCep(texto, erro = false) {
    $('mensagem-cep').textContent = texto;
    $('mensagem-cep').className = `cep-message${erro ? ' is-error' : ''}`;
    $('mensagem-cep').hidden = !texto;
}

async function buscarCep() {
    const cep = $('cep').value.replace(/\D/g, '');
    if (cep.length !== 8) {
        mensagemCep('Informe um CEP com 8 números para consultar o endereço.', true);
        $('cep').focus();
        return;
    }
    interromperConsultaCep();
    const controlador = new AbortController();
    consultaCep = controlador;
    const formularioConsultado = versaoFormulario;
    // Não substituir um endereço que a pessoa editou enquanto aguardava a API.
    const enderecoAnterior = Object.fromEntries(['logradouro', 'bairro', 'cidade', 'uf'].map((campo) => [campo, $(campo).value]));
    $('buscar-cep').disabled = true;
    $('buscar-cep').querySelector('span').textContent = 'Buscando…';
    mensagemCep('Consultando o endereço…');
    try {
        const dados = await requisitar(`/api/cep/${cep}`, { signal: controlador.signal });
        if (consultaCep !== controlador || formularioConsultado !== versaoFormulario || $('cep').value.replace(/\D/g, '') !== cep) return;
        let preservado = false;
        for (const campo of ['logradouro', 'bairro', 'cidade', 'uf']) {
            if ($(campo).value === enderecoAnterior[campo]) {
                $(campo).value = dados.endereco[campo] || '';
                $(`erro-${campo}`).textContent = '';
                $(campo).removeAttribute('aria-invalid');
            } else {
                preservado = true;
            }
        }
        const incompleto = ['logradouro', 'cidade', 'uf'].some((campo) => !$(campo).value);
        mensagemCep(preservado ? 'CEP consultado. Mantivemos os campos que você alterou durante a busca; confira o endereço antes de salvar.' : incompleto ? 'CEP encontrado. Complete manualmente os campos que não vieram na consulta.' : 'Endereço encontrado. Confira os dados e informe o número.');
        if (!preservado) $('numero').focus();
    } catch (erro) {
        if (erro.name === 'AbortError' || consultaCep !== controlador || formularioConsultado !== versaoFormulario) return;
        const orientacao = erro.message.includes('manualmente') ? '' : ' Você pode preencher o endereço manualmente e continuar o cadastro.';
        mensagemCep(erro.message + orientacao, true);
    } finally {
        if (consultaCep === controlador) {
            consultaCep = null;
            $('buscar-cep').disabled = salvando;
            $('buscar-cep').querySelector('span').textContent = 'Buscar CEP';
        }
    }
}

function mudarEstadoSalvamento(ativo) {
    salvando = ativo;
    for (const elemento of formulario.querySelectorAll('input, select, button')) elemento.disabled = ativo;
    $('voltar-clientes').disabled = ativo;
    formulario.setAttribute('aria-busy', String(ativo));
    $('salvar').textContent = ativo ? 'Salvando…' : clienteAtual ? 'Salvar alterações' : 'Salvar cliente';
}

formulario.addEventListener('submit', async (evento) => {
    evento.preventDefault();
    if (salvando) return;
    limparErros();
    if (!formulario.reportValidity()) return;
    const dados = Object.fromEntries(campos.map((campo) => [campo, $(campo).value.trim()]));
    dados.cep = dados.cep.replace(/\D/g, '');
    const editando = clienteAtual !== null;
    interromperConsultaCep();
    mudarEstadoSalvamento(true);
    let salvo = false;
    try {
        await requisitar(editando ? `/api/clientes/${clienteAtual}` : '/api/clientes', {
            method: editando ? 'PUT' : 'POST', body: JSON.stringify(dados),
        });
        salvo = true;
    } catch (erro) {
        $('erro-formulario').textContent = erro.message;
        $('erro-formulario').hidden = false;
        const invalidos = Object.entries(erro.campos || {}).filter(([campo]) => campos.includes(campo));
        for (const [campo, mensagem] of invalidos) {
            $(`erro-${campo}`).textContent = mensagem;
            $(campo).setAttribute('aria-invalid', 'true');
        }
        // O campo só poderá receber foco após sair do estado desabilitado.
        mudarEstadoSalvamento(false);
        if (invalidos.length) $(invalidos[0][0]).focus();
        else $('erro-formulario').scrollIntoView({ block: 'center', behavior: 'instant' });
    } finally {
        mudarEstadoSalvamento(false);
    }
    if (salvo) {
        $('busca').value = '';
        voltarParaLista();
        mensagemGlobal(editando ? 'Cadastro atualizado com sucesso.' : 'Cliente cadastrado com sucesso.');
        carregarClientes();
    }
});

for (const campo of campos) {
    $(campo).addEventListener('input', () => {
        $(`erro-${campo}`).textContent = '';
        $(campo).removeAttribute('aria-invalid');
    });
}
$('cep').addEventListener('input', () => {
    interromperConsultaCep();
    mensagemCep('');
    formatarCep();
});
$('cep').addEventListener('blur', formatarCep);
$('novo-cliente').addEventListener('click', () => abrirFormulario());
$('voltar-clientes').addEventListener('click', voltarParaLista);
$('cancelar').addEventListener('click', voltarParaLista);
$('buscar-cep').addEventListener('click', buscarCep);
$('tentar-novamente').addEventListener('click', carregarClientes);
$('busca').addEventListener('input', renderizarClientes);
$('limpar-busca').addEventListener('click', () => { $('busca').value = ''; renderizarClientes(); $('busca').focus(); });

carregarClientes();
