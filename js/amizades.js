(function () {
    'use strict';

    var endpoint = 'api/amizades.php';

    function token() {
        var meta = document.querySelector('meta[name="csrf-amizade"]');
        return meta ? meta.getAttribute('content') : '';
    }

    function enviar(acao, outro, csrf) {
        return fetch(endpoint, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-Token': token()
            },
            body: JSON.stringify({ acao: acao, outro: outro, csrf: csrf || token() })
        }).then(function (resposta) {
            return resposta.json().catch(function () {
                return null;
            }).then(function (dados) {
                return { status: resposta.status, dados: dados };
            });
        }).catch(function () {
            return null;
        });
    }

    function tratar(resposta, botao) {
        var dados = resposta && resposta.dados ? resposta.dados : null;

        if (resposta && resposta.status === 200 && dados && dados.ok) {
            window.location.reload();
            return;
        }

        if (botao) {
            botao.disabled = false;
        }

        alert(dados && dados.mensagem ? dados.mensagem : 'Não foi possível concluir a ação.');
    }

    document.querySelectorAll('[data-amizade-acao]').forEach(function (botao) {
        botao.addEventListener('click', function (evento) {
            evento.preventDefault();

            if (botao.disabled) {
                return;
            }

            botao.disabled = true;

            enviar(
                botao.getAttribute('data-amizade-acao'),
                parseInt(botao.getAttribute('data-outro'), 10)
            ).then(function (resposta) {
                tratar(resposta, botao);
            });
        });
    });

    document.querySelectorAll('form[data-amizade-form]').forEach(function (form) {
        form.addEventListener('submit', function (evento) {
            evento.preventDefault();

            var pergunta = form.getAttribute('data-confirm');

            if (pergunta && !window.confirm(pergunta)) {
                return;
            }

            var dados = new FormData(form);
            var botao = form.querySelector('[type="submit"]');

            if (botao) {
                botao.disabled = true;
            }

            enviar(dados.get('acao'), parseInt(dados.get('outro'), 10), dados.get('csrf')).then(function (resposta) {
                tratar(resposta, botao);
            });
        });
    });
})();
