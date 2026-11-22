(function () {
    'use strict';

    var modal = document.querySelector('[data-denuncia-modal]');
    if (!modal) return;

    var form = modal.querySelector('[data-denuncia-form]');
    var retorno = modal.querySelector('[data-denuncia-retorno]');
    var botaoEnviar = form.querySelector('[type="submit"]');

    function fechar() {
        modal.hidden = true;
        document.body.classList.remove('denuncia-aberta');
    }

    function abrir(id) {
        form.reset();
        form.elements.idPublicacao.value = String(id || '');
        retorno.hidden = true;
        retorno.textContent = '';
        botaoEnviar.disabled = false;
        botaoEnviar.hidden = false;
        modal.hidden = false;
        document.body.classList.add('denuncia-aberta');
        modal.querySelector('select').focus();
    }

    document.addEventListener('click', function (evento) {
        var botao = evento.target.closest('[data-denunciar-publicacao]');
        if (botao) {
            evento.preventDefault();
            var id = botao.getAttribute('data-publicacao-id');
            if (id) abrir(id);
            return;
        }

        if (evento.target === modal || evento.target.closest('[data-denuncia-fechar]')) {
            fechar();
        }
    });

    document.addEventListener('keydown', function (evento) {
        if (evento.key === 'Escape' && !modal.hidden) fechar();
    });

    form.addEventListener('submit', function (evento) {
        evento.preventDefault();
        botaoEnviar.disabled = true;

        var dados = {
            csrf: form.elements.csrf.value,
            idPublicacao: parseInt(form.elements.idPublicacao.value, 10),
            idTipo: parseInt(form.elements.idTipo.value, 10)
        };

        fetch('api/denunciar.php', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': dados.csrf },
            body: JSON.stringify(dados)
        }).then(function (resposta) {
            return resposta.json().then(function (json) {
                return { status: resposta.status, dados: json };
            });
        }).then(function (resposta) {
            retorno.textContent = resposta.dados.mensagem || 'Não foi possível enviar a denúncia.';
            retorno.classList.toggle('is-sucesso', !!resposta.dados.ok);
            retorno.hidden = false;

            if (resposta.dados.ok) {
                botaoEnviar.hidden = true;
            } else {
                botaoEnviar.disabled = false;
            }
        }).catch(function () {
            retorno.textContent = 'Falha de conexão. Tente novamente.';
            retorno.classList.remove('is-sucesso');
            retorno.hidden = false;
            botaoEnviar.disabled = false;
        });
    });

    window.abrirDenunciaPublicacao = abrir;
})();
