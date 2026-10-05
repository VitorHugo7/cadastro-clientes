// O restante do cadastro funciona com páginas e formulários PHP.
const cep = document.getElementById('cep');

if (cep) {
    const botao = document.getElementById('buscar-cep');
    const mensagem = document.getElementById('mensagem-cep');
    const campos = ['logradouro', 'bairro', 'cidade', 'uf'].map(id => document.getElementById(id));
    let versao = 0;
    let consulta = null;

    function informar(texto, erro = false) {
        mensagem.textContent = texto;
        mensagem.classList.toggle('is-error', erro);
        mensagem.hidden = false;
    }

    // Se o usuário editar durante a consulta, preservamos o que ele digitou.
    for (const campo of [cep, ...campos]) {
        campo.addEventListener('input', () => {
            versao++;
            if (consulta) consulta.abort();
            botao.disabled = false;
            botao.textContent = 'Buscar CEP';
            mensagem.hidden = true;
        });
    }

    botao.disabled = false;
    botao.addEventListener('click', async () => {
        if (!/^\d{5}-?\d{3}$/.test(cep.value.trim())) {
            informar('Informe um CEP com 8 números.', true);
            cep.focus();
            return;
        }

        if (consulta) consulta.abort();
        const requisicao = new AbortController();
        consulta = requisicao;
        const atual = ++versao;
        const valores = [cep, ...campos].map(campo => campo.value);
        const prazo = setTimeout(() => requisicao.abort(), 10000);
        botao.disabled = true;
        botao.textContent = 'Buscando…';
        informar('Consultando o CEP…');

        try {
            const resposta = await fetch('?acao=cep&cep=' + cep.value.replace('-', '').trim(), { signal: requisicao.signal });
            const dados = await resposta.json();
            if (!resposta.ok) throw new Error(dados.erro || 'Não foi possível consultar o CEP.');
            if (!dados.endereco || campos.some(campo => typeof dados.endereco[campo.id] !== 'string')) {
                throw new Error('A consulta retornou um endereço inválido.');
            }
            if (atual !== versao || [cep, ...campos].some((campo, indice) => campo.value !== valores[indice])) return;
            for (const campo of campos) campo.value = dados.endereco[campo.id];
            informar('Endereço preenchido. Confira os dados e informe o número.');
        } catch (erro) {
            if (atual === versao) {
                const texto = erro.name === 'AbortError' ? 'A consulta demorou demais.' : erro.message;
                informar(texto + ' Você pode preencher o endereço manualmente.', true);
            }
        } finally {
            clearTimeout(prazo);
            if (atual === versao) {
                consulta = null;
                botao.disabled = false;
                botao.textContent = 'Buscar CEP';
            }
        }
    });
}
