/* ==========================================================================
   VARIÁVEIS GLOBAIS E FUNÇÕES AUXILIARES
   ========================================================================== */
let idPostAtualModal = null;

function normalizarId(id) {
    if (!id) return '';
    let idStr = String(id).trim();
    return idStr.replace(/^https?:\/\/[^\/]+/, '');
}

document.addEventListener("DOMContentLoaded", () => {
    // 1. Sidebar
    const openBtn = document.getElementById('open_btn');
    const sidebar = document.getElementById('sidebar');

    if (openBtn && sidebar) {
        openBtn.addEventListener('click', () => {
            sidebar.classList.toggle('open-sidebar');
        });
    }

    // 2. Dados e Cards (Estáticos/Auxiliares)
    const data = [
        {
            title: "Fim de tarde",
            description: "@skywalker",
            image: "https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=500&auto=format&fit=crop&q=60"
        },
        {
            title: "Luzes da cidade",
            description: "@astroboy",
            image: "https://images.unsplash.com/photo-1519501025264-65ba15a82390?w=500&auto=format&fit=crop&q=60"
        },
        {
            title: "Profundezas",
            description: "@lonelynight",
            image: "https://images.unsplash.com/photo-1682687220063-4742bd7fd538?w=500&auto=format&fit=crop&q=60"
        },
        {
            title: "Natureza viva",
            description: "@greenmind",
            image: "https://images.unsplash.com/photo-1441974231531-c6227db76b6e?w=500&auto=format&fit=crop&q=60"
        }
    ];

    const cardContainer = document.querySelector('.card-container');
    const searchInput = document.getElementById('SearchInput');

    function displayData(items) {
        if (!cardContainer) return;
        cardContainer.innerHTML = '';

        items.forEach(e => {
            cardContainer.innerHTML += `    
                <div class="card">
                    <img src="${e.image}" alt="${e.title}" style="width:100%; height:140px; object-fit:cover;">
                    <div style="padding: 14px;">
                        <h3>${e.title}</h3>
                        <span style="color: #a1a1aa; font-size: 12px;">${e.description}</span>
                    </div>
                </div>
            `;
        });
    }

    if (cardContainer && data.length > 0) {
        displayData(data);
    }

    if (searchInput) {
        searchInput.addEventListener('keyup', (e) => {
            const value = e.target.value.toLowerCase();
            const filtered = data.filter(item =>
                item.title.toLowerCase().includes(value) ||
                item.description.toLowerCase().includes(value)
            );
            displayData(filtered);
        });
    }

    // 3. Barra de categorias (Carrossel)
    const catBar = document.getElementById('categoriasBar');
    const arrowLeft = document.getElementById('catArrowLeft');
    const arrowRight = document.getElementById('catArrowRight');

    if (catBar && arrowLeft && arrowRight) {
        const VELOCIDADE_MARQUEE = 0.55;
        const prefereMenosMovimento = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        const wrapper = catBar.closest('.categorias-wrapper');

        let marqueePausado = false;
        let modoManual = prefereMenosMovimento;
        let ultimoTempo = null;

        try {
            if (sessionStorage.getItem('catManual') === '1') modoManual = true;
        } catch (e) { /* sessionStorage indisponível */ }

        const limiteMaximo = () => Math.max(catBar.scrollWidth - catBar.clientWidth, 0);
        const passoRolagem = () => Math.max(catBar.clientWidth * 0.8, 240);

        function centralizarAtivo() {
            const btnAtivo = catBar.querySelector('.cat-btn.active');
            if (!btnAtivo) return;
            const alvo = btnAtivo.offsetLeft - (catBar.clientWidth - btnAtivo.offsetWidth) / 2;
            catBar.scrollLeft = Math.max(0, Math.min(alvo, limiteMaximo()));
        }

        centralizarAtivo();

        function rolarPara(posicao, suave = true) {
            const destino = Math.max(0, Math.min(posicao, limiteMaximo()));
            try {
                catBar.scrollTo({ left: destino, behavior: suave ? 'smooth' : 'auto' });
            } catch (e) {
                catBar.scrollLeft = destino;
            }
        }

        function marqueeLoop(timestamp) {
            if (ultimoTempo === null) ultimoTempo = timestamp;
            const delta = timestamp - ultimoTempo;
            ultimoTempo = timestamp;

            const limite = limiteMaximo();

            if (!marqueePausado && !modoManual && limite > 0) {
                catBar.scrollLeft += VELOCIDADE_MARQUEE * (delta / 16.7);

                if (catBar.scrollLeft >= limite) {
                    catBar.scrollLeft = 0;
                }
            }

            requestAnimationFrame(marqueeLoop);
        }

        requestAnimationFrame((t) => {
            ultimoTempo = null;
            requestAnimationFrame(marqueeLoop);
        });

        if (wrapper) {
            wrapper.addEventListener('mouseenter', () => {
                if (!modoManual) marqueePausado = true;
            });
            wrapper.addEventListener('mouseleave', () => {
                marqueePausado = false;
            });
        }

        function rolar(direcao) {
            const limite = limiteMaximo();
            if (limite === 0) return;
            rolarPara(catBar.scrollLeft + direcao * passoRolagem());
        }

        arrowLeft.addEventListener('click', () => {
            modoManual = true;
            rolar(-1);
        });

        arrowRight.addEventListener('click', () => {
            modoManual = true;
            rolar(1);
        });

        catBar.querySelectorAll('.cat-btn').forEach((btn) => {
            btn.addEventListener('click', () => {
                try { sessionStorage.setItem('catManual', '1'); } catch (e) { /* ignora */ }
            });
        });

        let arrastando = false;
        let moveuDuranteArrasto = false;
        let inicioX = 0;
        let scrollInicial = 0;

        catBar.addEventListener('pointerdown', (e) => {
            if (e.pointerType !== 'mouse' || e.button !== 0) return;
            arrastando = true;
            moveuDuranteArrasto = false;
            inicioX = e.clientX;
            scrollInicial = catBar.scrollLeft;
            catBar.classList.add('arrastando');
        });

        window.addEventListener('pointermove', (e) => {
            if (!arrastando) return;
            const delta = e.clientX - inicioX;
            if (Math.abs(delta) > 5) {
                moveuDuranteArrasto = true;
                modoManual = true;
            }
            catBar.scrollLeft = scrollInicial - delta;
        });

        window.addEventListener('pointerup', () => {
            arrastando = false;
            catBar.classList.remove('arrastando');
        });

        catBar.addEventListener('click', (e) => {
            if (moveuDuranteArrasto) {
                e.preventDefault();
                e.stopPropagation();
                moveuDuranteArrasto = false;
            }
        }, true);
    }

    // 4. Tema Claro / Escuro (CORRIGIDO)
    const chk = document.getElementById('chk');
    if (chk) {
        const temaSalvo = localStorage.getItem('tema');
        if (temaSalvo === 'escuro') {
            document.body.classList.add('dark');
            chk.checked = true;
        } else {
            document.body.classList.remove('dark');
            chk.checked = false;
        }

        chk.addEventListener('change', () => {
            const ehEscuro = document.body.classList.toggle('dark');
            localStorage.setItem('tema', ehEscuro ? 'escuro' : 'claro');
        });
    }

    // 5. Pop-up de Configurações
    const configBtn = document.getElementById("logout_btn");
    const popup = document.getElementById("config_popup");
    const overlay = document.getElementById("overlay");
    const closeBtn = document.getElementById("close_popup");

    if (configBtn && popup && overlay) {
        configBtn.addEventListener("click", () => {
            popup.classList.add("show");
            overlay.classList.add("show");
        });

        function fecharConfig() {
            popup.classList.remove("show");
            overlay.classList.remove("show");
        }

        if (closeBtn) closeBtn.addEventListener("click", fecharConfig);
        overlay.addEventListener("click", fecharConfig);
    }

    // 6. Pop-up de Visualização (Modal)
    const previewModal = document.getElementById("previewModal");
    const previewImage = document.getElementById("previewImage");
    const closePreview = document.querySelector(".close-preview");

    window.abrirModal = function (urlImagem, nomeAutor, idPub, titulo, descricao, curtidas) {
        idPostAtualModal = idPub;

        if (previewModal && previewImage) {
            previewImage.src = urlImagem;
            previewImage.dataset.id = idPub || urlImagem;

            const tituloModal = document.getElementById("modalTitulo");
            if (tituloModal) {
                tituloModal.innerText = titulo || "Sem título";
            }

            const descricaoModal = document.getElementById("modalDescricao");
            if (descricaoModal) {
                descricaoModal.innerText = descricao || "";
            }

            const nomeModal = document.getElementById("modalNomeUsuario");
            if (nomeModal) {
                nomeModal.innerText = nomeAutor || "@usuario";
            }

            const curtidasModal = document.getElementById("modalCurtidas");
            if (curtidasModal) {
                curtidasModal.innerText = curtidas ?? 0;
            }

            const lista = document.getElementById("listaComentarios");
            if (lista) {
                lista.innerHTML = "<p class='sem-comentario'>Nenhum comentário ainda.</p>";
            }

            const contador = document.getElementById("modalComentarios");
            if (contador) {
                contador.innerText = "0";
            }

            // Sincroniza o ícone de coração preenchido/vazio dentro do modal ao abrir
            const elementoFeed = document.getElementById("curtidas-" + idPub);
            const cardOrigem = elementoFeed ? elementoFeed.closest('.card, .media-card') : null;
            const iconeModal = document.querySelector('#btnCurtirModal i');
            if (cardOrigem && iconeModal) {
                const iconeCard = cardOrigem.querySelector('.fa-heart');
                if (iconeCard) {
                    if (iconeCard.classList.contains('fa-solid')) {
                        iconeModal.classList.remove('fa-regular');
                        iconeModal.classList.add('fa-solid', 'curtido');
                        iconeModal.style.color = '#ff3366';
                    } else {
                        iconeModal.classList.remove('fa-solid', 'curtido');
                        iconeModal.classList.add('fa-regular');
                        iconeModal.style.color = '';
                    }
                }
            }

            const favoritos = obterFavoritos();
            const idAtual = normalizarId(previewImage.dataset.id);
            const itemSalvo = favoritos.find(item =>
                normalizarId(item.id) === idAtual || normalizarId(item.imagem) === idAtual
            );
            const estaSalvo = !!itemSalvo;

            const btnIcon = document.querySelector('#btnSalvarModal i') || document.getElementById('btnSalvarModal');
            if (btnIcon) {
                if (estaSalvo) {
                    btnIcon.classList.remove('fa-regular');
                    btnIcon.classList.add('fa-solid');
                } else {
                    btnIcon.classList.remove('fa-solid');
                    btnIcon.classList.add('fa-regular');
                }
            }

            previewModal.classList.add("show");
        }
    };

    if (closePreview && previewModal) {
        closePreview.addEventListener("click", () => {
            previewModal.classList.remove("show");
        });

        previewModal.addEventListener("click", (e) => {
            if (e.target === previewModal) {
                previewModal.classList.remove("show");
            }
        });
    }

    // 7. Marcar Bookmarks dos Favoritos ao Carregar
    const favoritos = obterFavoritos();
    const idsFavoritados = favoritos.flatMap(item => [normalizarId(item.id), normalizarId(item.imagem)]);

    document.querySelectorAll('.media-card, .card').forEach(card => {
        const imgElement = card.querySelector('img');
        const rawId = card.dataset.id || (imgElement ? imgElement.getAttribute('src') : '');
        const cardId = normalizarId(rawId);

        const icon = card.querySelector('.bookmark-icon, .fa-bookmark');
        if (icon) {
            if (idsFavoritados.includes(cardId)) {
                icon.classList.add('active', 'fa-solid');
                icon.classList.remove('fa-regular');
            } else {
                icon.classList.remove('active', 'fa-solid');
                icon.classList.add('fa-regular');
            }
        }
    });
});

/* ==========================================================================
   FUNÇÕES GLOBAIS (AJAX / EVENTOS / FAVORITOS)
   ========================================================================== */

function enviarComentario() {
    const input = document.getElementById("inputComentario");
    const lista = document.getElementById("listaComentarios");

    if (!input || !lista) return;

    const texto = input.value.trim();

    if (texto !== "") {
        if (lista.innerHTML.includes("Nenhum comentário ainda.")) {
            lista.innerHTML = "";
        }

        const novoComentario = document.createElement("div");
        novoComentario.className = "comentario-item";
        novoComentario.innerHTML = `<strong>Você:</strong> ${texto}`;

        lista.appendChild(novoComentario);
        input.value = "";
        lista.scrollTop = lista.scrollHeight;

        const contador = document.getElementById("modalComentarios");
        if (contador) {
            contador.innerText = parseInt(contador.innerText || '0') + 1;
        }
    }
}

function curtir(event, idPublicacao) {
    if (event) event.stopPropagation();
    if (!idPublicacao) return;

    const dados = new FormData();
    dados.append("idPublicacao", idPublicacao);

    fetch("curtir.php", {
        method: "POST",
        body: dados
    })
        .then(response => response.json())
        .then(data => {
            if (data.sucesso) {
                const elementoFeed = document.getElementById("curtidas-" + idPublicacao);
                if (elementoFeed) {
                    elementoFeed.textContent = data.totalCurtidas;
                }

                if (String(idPostAtualModal) === String(idPublicacao)) {
                    const elementoModal = document.getElementById("modalCurtidas");
                    if (elementoModal) {
                        elementoModal.textContent = data.totalCurtidas;
                    }
                }

                // Atualiza o coração no Feed/Card
                const card = elementoFeed ? elementoFeed.closest('.card, .media-card') : null;
                if (card) {
                    const coracaoIcon = card.querySelector('.fa-heart');
                    if (coracaoIcon) {
                        if (data.curtiu) {
                            coracaoIcon.classList.remove('fa-regular');
                            coracaoIcon.classList.add('fa-solid', 'curtido');
                            coracaoIcon.style.color = '#ff3366'; // Cor vermelha/rosa de destaque
                        } else {
                            coracaoIcon.classList.remove('fa-solid', 'curtido');
                            coracaoIcon.classList.add('fa-regular');
                            coracaoIcon.style.color = '';
                        }
                    }
                }

                // Atualiza o coração no Modal de Visualização (se estiver aberto para este post)
                if (String(idPostAtualModal) === String(idPublicacao)) {
                    const coracaoModalIcon = document.querySelector('#btnCurtirModal i');
                    if (coracaoModalIcon) {
                        if (data.curtiu) {
                            coracaoModalIcon.classList.remove('fa-regular');
                            coracaoModalIcon.classList.add('fa-solid', 'curtido');
                            coracaoModalIcon.style.color = '#ff3366';
                        } else {
                            coracaoModalIcon.classList.remove('fa-solid', 'curtido');
                            coracaoModalIcon.classList.add('fa-regular');
                            coracaoModalIcon.style.color = '';
                        }
                    }
                }
            } else {
                alert(data.mensagem || "Erro ao processar a curtida.");
            }
        })
        .catch(error => {
            console.error("Erro na requisição AJAX:", error);
        });
}

function alternarIconeSalvar() {
    const btnSalvar = document.getElementById('btnSalvarModal');
    if (!btnSalvar) return;

    const btnIcon = btnSalvar.querySelector('i') || btnSalvar;
    const previewImage = document.getElementById('previewImage');
    const tituloModal = document.getElementById('modalTitulo');
    const curtidasModal = document.getElementById('modalCurtidas');

    if (!previewImage || !previewImage.src) return;

    const imagemUrl = previewImage.getAttribute('src');
    const cardId = previewImage.dataset.id || imagemUrl;

    const cardData = {
        id: normalizarId(cardId),
        titulo: tituloModal ? tituloModal.innerText : 'Sem título',
        imagem: imagemUrl,
        curtidas: curtidasModal ? curtidasModal.innerText : '0'
    };

    const idN = normalizarId(cardData.id);

    if (itemJaFavoritado(idN)) {
        alternarFavorito(cardData);
        definirIconeBookmark(btnIcon, false);
    } else {
        abrirBalaoPastas(btnSalvar, cardData, (pasta) => {
            if (!pasta) return;
            alternarFavorito(Object.assign({}, cardData, { pasta: pasta }));
            definirIconeBookmark(btnIcon, true);
        });
    }
}

/* ==========================================================================
   SISTEMA DE FAVORITOS (LOCALSTORAGE)
   ========================================================================== */

function obterFavoritos() {
    try {
        return JSON.parse(localStorage.getItem('meusFavoritos')) || [];
    } catch (e) {
        return [];
    }
}

function alternarFavorito(cardData) {
    let favoritos = obterFavoritos();
    const idNormalizado = normalizarId(cardData.id);

    const index = favoritos.findIndex(item => {
        const itemId = normalizarId(item.id);
        const itemImg = normalizarId(item.imagem);
        return itemId === idNormalizado || itemImg === idNormalizado;
    });

    if (index > -1) {
        favoritos.splice(index, 1);
    } else {
        cardData.id = idNormalizado;
        cardData.imagem = normalizarId(cardData.imagem);
        favoritos.push(cardData);
    }

    localStorage.setItem('meusFavoritos', JSON.stringify(favoritos));
}

/* ==========================================================================
   PASTAS DO USUÁRIO + PICKER AO FAVORITAR (página inicial)
   ========================================================================== */

function obterPastasUsuario() {
    try {
        const custom = JSON.parse(localStorage.getItem('minhasPastas')) || [];
        return custom.filter(n => n && String(n).trim() !== '');
    } catch (e) { return []; }
}

function definirIconeBookmark(el, salvo) {
    if (!el) return;
    if (salvo) {
        el.classList.remove('fa-regular');
        el.classList.add('fa-solid', 'active');
    } else {
        el.classList.remove('fa-solid', 'active');
        el.classList.add('fa-regular');
    }
}

function itemJaFavoritado(idNormalizado) {
    const favoritos = obterFavoritos();
    return favoritos.some(item =>
        normalizarId(item.id) === idNormalizado || normalizarId(item.imagem) === idNormalizado
    );
}

function escaparAttr(s) {
    return String(s ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

function fecharBalaoPastas() {
    const b = document.getElementById('favBalloon');
    if (b) b.remove();
    document.removeEventListener('click', fecharBalaoFora, true);
    document.removeEventListener('keydown', fecharBalaoEsc);
    window.removeEventListener('resize', fecharBalaoPastas);
}

function fecharBalaoFora(e) {
    const b = document.getElementById('favBalloon');
    if (!b) return;
    if (b.contains(e.target)) return;
    if (b._anchor && b._anchor.contains(e.target)) return;
    fecharBalaoPastas();
}

function fecharBalaoEsc(e) {
    if (e.key === 'Escape') fecharBalaoPastas();
}

function abrirBalaoPastas(anchor, cardData, aoEscolher) {
    fecharBalaoPastas();

    const balao = document.createElement('div');
    balao.id = 'favBalloon';
    balao.className = 'fav-balloon';
    balao._anchor = anchor;

    let linhas = '';
    obterPastasUsuario().forEach(nome => {
        linhas += `<button type="button" class="fav-balloon-item" data-pasta="${escaparAttr(nome)}"><i class="fa-solid fa-folder"></i><span>${escaparAttr(nome)}</span></button>`;
    });

    balao.innerHTML = `
        <p class="fav-balloon-title">Salvar em...</p>
        ${linhas === '' ? '<p class="fav-balloon-empty">Você ainda não tem pastas.<br>Crie a primeira abaixo:</p>' : `<div class="fav-balloon-list">${linhas}</div>`}
        <form class="fav-balloon-new">
            <input type="text" placeholder="Nova pasta..." maxlength="50">
            <button type="submit" title="Criar e salvar aqui"><i class="fa-solid fa-plus"></i></button>
        </form>`;
    document.body.appendChild(balao);

    const r = anchor.getBoundingClientRect();
    const bw = 250;
    const left = Math.min(Math.max(8, r.left + r.width / 2 - bw / 2), window.innerWidth - bw - 8);
    balao.style.left = left + 'px';
    const bh = balao.offsetHeight;
    let top = r.bottom + 12;
    let acima = false;
    if (top + bh > window.innerHeight - 8) {
        top = Math.max(8, r.top - bh - 12);
        acima = true;
    }
    balao.style.top = top + 'px';
    if (acima) balao.classList.add('acima');
    const setaX = Math.min(Math.max(18, r.left + r.width / 2 - left), bw - 18);
    balao.style.setProperty('--seta', setaX + 'px');

    function concluir(nome) {
        fecharBalaoPastas();
        if (typeof aoEscolher === 'function') aoEscolher(nome);
    }

    balao.addEventListener('click', (e) => {
        const btn = e.target.closest('.fav-balloon-item');
        if (btn) concluir(btn.getAttribute('data-pasta'));
    });

    const form = balao.querySelector('.fav-balloon-new');
    form.addEventListener('submit', (e) => {
        e.preventDefault();
        e.stopPropagation();
        const nome = (form.querySelector('input').value || '').trim();
        if (!nome) return;
        try {
            const arr = JSON.parse(localStorage.getItem('minhasPastas')) || [];
            if (arr.indexOf(nome) === -1) arr.push(nome);
            localStorage.setItem('minhasPastas', JSON.stringify(arr));
        } catch (err) { /* ignora */ }
        concluir(nome);
    });

    document.addEventListener('click', fecharBalaoFora, true);
    document.addEventListener('keydown', fecharBalaoEsc);
    window.addEventListener('resize', fecharBalaoPastas);
}

document.addEventListener('click', function (e) {
    const bookmark = e.target.closest('.bookmark-icon, .fa-bookmark');

    if (!bookmark || e.target.closest('#btnSalvarModal')) return;

    e.preventDefault();
    e.stopPropagation();

    const card = bookmark.closest('.media-card, .card');
    if (!card) return;

    const imgElement = card.querySelector('img');
    const imagemUrl = imgElement ? imgElement.getAttribute('src') : '';
    let cardId = card.dataset.id || imagemUrl;

    if (!cardId) return;

    const cardData = {
        id: normalizarId(cardId),
        titulo: card.dataset.titulo || (imgElement ? imgElement.alt : '') || 'Sem título',
        imagem: imagemUrl,
        curtidas: card.querySelector('.curtida span, [id^="curtidas-"]')?.innerText.trim() || '0'
    };

    const idN = normalizarId(cardData.id);

    if (itemJaFavoritado(idN)) {
        alternarFavorito(cardData);
        definirIconeBookmark(bookmark, false);
    } else {
        abrirBalaoPastas(bookmark, cardData, (pasta) => {
            if (!pasta) return;
            alternarFavorito(Object.assign({}, cardData, { pasta: pasta }));
            definirIconeBookmark(bookmark, true);
        });
    }
});

function removerSalvo(id, iconeElemento) {
    let favoritos = obterFavoritos();
    const idParaRemover = normalizarId(id);

    favoritos = favoritos.filter(item => {
        const itemId = normalizarId(item.id);
        const itemImg = normalizarId(item.imagem);
        return itemId !== idParaRemover && itemImg !== idParaRemover;
    });

    localStorage.setItem('meusFavoritos', JSON.stringify(favoritos));

    const card = iconeElemento ? iconeElemento.closest('.media-card, .card') : null;
    if (card) {
        card.remove();
    }

    if (favoritos.length === 0) {
        const container = document.getElementById('favoritosContainer');
        if (container) {
            container.innerHTML = '<p style="color: #888; padding: 15px;">Você ainda não salvou nenhuma mídia.</p>';
        }
    }
}

/* ==========================================================================
   CONEXÃO FRONTEND COM A API DE VERIFICAÇÃO DE E-MAIL (NODE.JS)
   ========================================================================== */

const API_URL = (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1')
    ? 'http://localhost:3000'
    : `${window.location.protocol}//${window.location.hostname}`;

async function solicitarCodigoConfirmacao(idUsuario, email) {
    try {
        const response = await fetch(`${API_URL}/api/enviar-codigo`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ idUsuario, email })
        });

        const data = await response.json();

        if (response.ok) {
            console.log('Resposta do servidor:', data);
            return true;
        } else {
            alert('Erro ao enviar código: ' + (data.mensagem || data.erro));
            console.error('Erro:', data);
            return false;
        }
    } catch (error) {
        alert('Erro de conexão com o servidor de e-mail.');
        console.error('Erro na requisição:', error);
        return false;
    }
}

async function confirmarCodigoEmail(idUsuario, codigo) {
    try {
        const response = await fetch(`${API_URL}/api/validar-codigo`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ idUsuario, codigo })
        });

        const data = await response.json();

        if (response.ok) {
            console.log('Sucesso:', data);
            return true;
        } else {
            alert('Falha na verificação: ' + (data.mensagem || data.erro));
            console.error('Erro na validação:', data);
            return false;
        }
    } catch (error) {
        alert('Erro de conexão com o servidor.');
        console.error('Erro na requisição:', error);
        return false;
    }
}