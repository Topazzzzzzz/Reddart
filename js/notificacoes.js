(function () {
    'use strict';

    var componente = document.querySelector('[data-notificacoes]');
    if (!componente) return;

    var endpoint = componente.getAttribute('data-endpoint');
    var botao = componente.querySelector('[data-notificacoes-abrir]');
    var painel = componente.querySelector('[data-notificacoes-painel]');
    var lista = componente.querySelector('[data-notificacoes-lista]');
    var contador = componente.querySelector('[data-notificacoes-contador]');
    var token = componente.getAttribute('data-csrf') || '';
    var carregando = false;

    function atualizarContador(total) {
        total = Math.max(0, parseInt(total, 10) || 0);
        contador.textContent = total > 99 ? '99+' : String(total);
        contador.hidden = total === 0;
    }

    function escapar(valor) {
        return String(valor || '').replace(/[&<>"']/g, function (caractere) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[caractere];
        });
    }

    function formatarHora(valor) {
        var data = new Date(String(valor || '').replace(' ', 'T'));
        if (Number.isNaN(data.getTime())) return '';
        return data.toLocaleString('pt-BR', { dateStyle: 'short', timeStyle: 'short' });
    }

    function buscar() {
        if (carregando) return Promise.resolve();
        carregando = true;

        return fetch(endpoint, { credentials: 'same-origin', cache: 'no-store' })
            .then(function (resposta) { return resposta.json(); })
            .then(function (dados) {
                if (!dados || !dados.ok) throw new Error('Falha ao carregar notificações.');
                token = dados.csrf || token;
                atualizarContador(dados.naoLidas);

                if (!dados.itens.length) {
                    lista.innerHTML = '<p class="rd-notificacoes__vazio">Você não tem notificações.</p>';
                    return;
                }

                lista.innerHTML = dados.itens.map(function (item) {
                    var classe = item.lida ? '' : ' is-nao-lida';
                    return '<a class="rd-notificacoes__item' + classe + '" href="' + escapar(item.url) + '" data-notificacao-id="' + Number(item.id) + '">' +
                        '<span class="rd-notificacoes__ponto" aria-hidden="true"></span>' +
                        '<span class="rd-notificacoes__conteudo"><strong>' + escapar(item.titulo) + '</strong>' +
                        '<span>' + escapar(item.mensagem) + '</span>' +
                        '<small>' + escapar(formatarHora(item.hora)) + '</small></span></a>';
                }).join('');
            })
            .catch(function () {
                lista.innerHTML = '<p class="rd-notificacoes__vazio">Não foi possível carregar as notificações.</p>';
            })
            .finally(function () { carregando = false; });
    }

    function enviar(dados) {
        dados.csrf = token;
        return fetch(endpoint, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': token },
            body: JSON.stringify(dados)
        }).then(function (resposta) { return resposta.json(); });
    }

    botao.addEventListener('click', function (evento) {
        evento.stopPropagation();
        painel.hidden = !painel.hidden;
        botao.setAttribute('aria-expanded', painel.hidden ? 'false' : 'true');
        if (!painel.hidden) buscar();
    });

    lista.addEventListener('click', function (evento) {
        var item = evento.target.closest('[data-notificacao-id]');
        if (!item || item.classList.contains('is-nao-lida') === false) return;
        evento.preventDefault();
        var destino = item.href;
        enviar({ acao: 'marcar_lida', id: item.getAttribute('data-notificacao-id') })
            .then(function (dados) { if (dados && dados.ok) atualizarContador(dados.naoLidas); })
            .finally(function () { window.location.href = destino; });
    });

    componente.querySelector('[data-notificacoes-ler-todas]').addEventListener('click', function () {
        enviar({ acao: 'marcar_todas' }).then(function (dados) {
            if (dados && dados.ok) {
                atualizarContador(dados.naoLidas);
                buscar();
            }
        });
    });

    document.addEventListener('click', function (evento) {
        if (!componente.contains(evento.target)) {
            painel.hidden = true;
            botao.setAttribute('aria-expanded', 'false');
        }
    });

    document.addEventListener('keydown', function (evento) {
        if (evento.key === 'Escape' && !painel.hidden) {
            painel.hidden = true;
            botao.setAttribute('aria-expanded', 'false');
            botao.focus();
        }
    });

    window.registrarVisualizacaoObra = function (idPublicacao) {
        var id = parseInt(idPublicacao, 10);
        if (!id || !token) return;
        enviar({ acao: 'visualizacao', idPublicacao: id }).catch(function () {});
    };

    window.setInterval(function () {
        fetch(endpoint + '?resumo=1', { credentials: 'same-origin', cache: 'no-store' })
            .then(function (resposta) { return resposta.json(); })
            .then(function (dados) { if (dados && dados.ok) atualizarContador(dados.naoLidas); })
            .catch(function () {});
    }, 60000);
})();
