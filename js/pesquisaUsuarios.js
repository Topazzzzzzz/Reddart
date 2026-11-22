(function () {
    'use strict';

    var ESPERA_MS = 300;
    var MINIMO_CARACTERES = 2;
    var FOTO_PADRAO = 'uploads/usuNovoPerfil.jpg';

    function iniciar() {
        var raiz = document.querySelector('[data-rd-busca]');

        if (!raiz || raiz.getAttribute('data-rd-pronto') === '1') {
            return;
        }

        var input = raiz.querySelector('.rd-busca-usuarios__input');
        var resultados = raiz.querySelector('.rd-busca-usuarios__resultados');
        var limpar = raiz.querySelector('.rd-busca-usuarios__limpar');
        var gatilho = raiz.querySelector('.rd-busca-usuarios__gatilho');
        var endpoint = raiz.getAttribute('data-endpoint') || 'api/pesquisarUsuarios.php';

        if (!input || !resultados) {
            return;
        }

        raiz.setAttribute('data-rd-pronto', '1');

        var timer = null;
        var controle = null;
        var ultimoTermo = '';
        var itens = [];
        var indiceAtivo = -1;

        function fecharLista() {
            resultados.hidden = true;
            input.setAttribute('aria-expanded', 'false');
            input.removeAttribute('aria-activedescendant');
            indiceAtivo = -1;

            itens.forEach(function (item) {
                item.classList.remove('is-ativo');
                item.setAttribute('aria-selected', 'false');
            });
        }

        function abrirLista() {
            if (!resultados.hidden) {
                return;
            }

            resultados.hidden = false;
            input.setAttribute('aria-expanded', 'true');
        }

        function definirAberto(aberto) {
            raiz.classList.toggle('is-aberto', aberto);

            if (gatilho) {
                gatilho.setAttribute('aria-expanded', aberto ? 'true' : 'false');
            }

            if (!aberto) {
                fecharLista();
            }
        }

        function mostrarAviso(texto) {
            resultados.textContent = '';
            itens = [];
            indiceAtivo = -1;

            var aviso = document.createElement('div');
            aviso.className = 'rd-busca-usuarios__aviso';
            aviso.textContent = texto;
            resultados.appendChild(aviso);

            abrirLista();
        }

        function montarItem(usuario) {
            var item = document.createElement('a');
            item.className = 'rd-busca-usuarios__item';
            item.href = 'perfil.php?id=' + encodeURIComponent(usuario.id);
            item.setAttribute('role', 'option');
            item.setAttribute('aria-selected', 'false');
            item.id = 'rdBuscaResultado' + itens.length;

            var foto = document.createElement('img');
            foto.src = usuario.foto || FOTO_PADRAO;
            foto.alt = '';
            foto.width = 36;
            foto.height = 36;
            foto.loading = 'lazy';
            foto.addEventListener('error', function () {
                foto.src = FOTO_PADRAO;
            }, { once: true });

            var info = document.createElement('span');
            info.className = 'rd-busca-usuarios__info';

            var nome = document.createElement('strong');
            nome.textContent = usuario.nome || 'Artista';
            info.appendChild(nome);

            if (usuario.nick) {
                var nick = document.createElement('small');
                nick.textContent = '@' + usuario.nick;
                info.appendChild(nick);
            }

            item.appendChild(foto);
            item.appendChild(info);

            return item;
        }

        function renderizar(lista) {
            resultados.textContent = '';
            itens = [];
            indiceAtivo = -1;

            if (!lista.length) {
                mostrarAviso('Nenhum usuário encontrado');
                return;
            }

            lista.forEach(function (usuario) {
                var item = montarItem(usuario);
                resultados.appendChild(item);
                itens.push(item);
            });

            abrirLista();
        }

        function buscar(termo) {
            if (controle) {
                controle.abort();
            }

            controle = ('AbortController' in window) ? new AbortController() : null;

            fetch(endpoint + '?q=' + encodeURIComponent(termo), {
                method: 'GET',
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin',
                signal: controle ? controle.signal : undefined
            })
                .then(function (resposta) {
                    if (!resposta.ok) {
                        throw new Error('http');
                    }

                    return resposta.json();
                })
                .then(function (dados) {
                    if (ultimoTermo !== termo) {
                        return;
                    }

                    renderizar(Array.isArray(dados.resultados) ? dados.resultados : []);
                })
                .catch(function (erro) {
                    if (erro && erro.name === 'AbortError') {
                        return;
                    }

                    if (ultimoTermo !== termo) {
                        return;
                    }

                    mostrarAviso('Nenhum usuário encontrado');
                });
        }

        function agendarBusca() {
            var termo = input.value.trim();

            if (limpar) {
                limpar.hidden = termo.length === 0;
            }

            if (termo.length < MINIMO_CARACTERES) {
                ultimoTermo = '';

                if (timer) {
                    clearTimeout(timer);
                    timer = null;
                }

                if (controle) {
                    controle.abort();
                    controle = null;
                }

                resultados.textContent = '';
                itens = [];
                fecharLista();
                return;
            }

            ultimoTermo = termo;

            if (timer) {
                clearTimeout(timer);
            }

            timer = setTimeout(function () {
                buscar(termo);
            }, ESPERA_MS);
        }

        function moverSelecao(passo) {
            if (!itens.length) {
                return;
            }

            if (indiceAtivo < 0) {
                indiceAtivo = passo > 0 ? 0 : itens.length - 1;
            } else {
                indiceAtivo = (indiceAtivo + passo + itens.length) % itens.length;
            }

            itens.forEach(function (item, indice) {
                var ativo = indice === indiceAtivo;
                item.classList.toggle('is-ativo', ativo);
                item.setAttribute('aria-selected', ativo ? 'true' : 'false');
            });

            input.setAttribute('aria-activedescendant', itens[indiceAtivo].id);
            itens[indiceAtivo].scrollIntoView({ block: 'nearest' });
        }

        input.addEventListener('input', agendarBusca);

        input.addEventListener('focus', function () {
            if (resultados.children.length > 0) {
                abrirLista();
            }
        });

        input.addEventListener('keydown', function (evento) {
            if (evento.key === 'ArrowDown') {
                if (!itens.length) {
                    return;
                }

                evento.preventDefault();
                moverSelecao(1);
                return;
            }

            if (evento.key === 'ArrowUp') {
                if (!itens.length) {
                    return;
                }

                evento.preventDefault();
                moverSelecao(-1);
                return;
            }

            if (evento.key === 'Enter') {
                if (indiceAtivo >= 0 && itens[indiceAtivo]) {
                    evento.preventDefault();
                    window.location.href = itens[indiceAtivo].href;
                }

                return;
            }

            if (evento.key === 'Escape') {
                fecharLista();
                definirAberto(false);
            }
        });

        if (limpar) {
            limpar.addEventListener('click', function () {
                input.value = '';
                agendarBusca();
                input.focus();
            });
        }

        if (gatilho) {
            gatilho.addEventListener('click', function () {
                var abrir = !raiz.classList.contains('is-aberto');
                definirAberto(abrir);

                if (abrir) {
                    window.requestAnimationFrame(function () {
                        input.focus();
                    });
                }
            });
        }

        document.addEventListener('click', function (evento) {
            if (raiz.contains(evento.target)) {
                return;
            }

            fecharLista();
            definirAberto(false);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', iniciar);
    } else {
        iniciar();
    }
})();
