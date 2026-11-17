<?php 
include_once 'include/header.php'; 
?>

<main>
    <link rel="stylesheet" href="css/fav.css?v=11">
    <div class="top-bar top-bar-end">
        <div class="button_dn">
            <input type="checkbox" id="chk" class="checkbox">
            <label class="label" for="chk">
                <i class="fas fa-moon"></i>
                <i class="fas fa-sun"></i>
                <div class="ball"></div>
            </label>
        </div>
    </div>

    <div class="fav-layout">
        <!-- COLUNA PRINCIPAL -->
        <div class="fav-main">
            <!-- BANNER — idêntico ao da tela inicial (mesma imagem, mesma estrutura) -->
            <div class="banner-container">
                <div class="banner-card">
                    <img src="<?php echo htmlspecialchars($bannerAtual ?? 'img/placeholder.jpg'); ?>" alt="Banner Favoritos">
                    <div class="banner-info">
                        <span class="banner-badge">FAVORITOS</span>
                        <h2>Favoritos</h2>
                        <p>Aqui ficam todas as coisas que você salvou para não perder de vista.</p>
                    </div>
                </div>
            </div>

            <!-- FILTROS (mesmo estilo dos botões de categoria da inicial) -->
            <div class="fav-filters" role="tablist" aria-label="Filtrar por categoria">
                    <button class="fav-chip active" data-filter="tudo" role="tab" aria-selected="true"><i class="fa-solid fa-heart"></i> Tudo</button>
            </div>

            <!-- BUSCA + SELECT -->
            <div class="fav-searchrow">
                <label class="fav-search">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" id="favSearch" placeholder="Pesquisar..." autocomplete="off">
                </label>
                <div class="fav-select-wrap">
                    <select id="favTipoMidia" aria-label="Filtrar tipo de mídia">
                        <option value="todas">Todas as mídias</option>
                        <option value="recentes">Adicionados recentemente</option>
                        <option value="antigos">Mais antigos</option>
                        <option value="az">A–Z</option>
                    </select>
                    <i class="fa-solid fa-chevron-down"></i>
                </div>
            </div>

            <!-- CONTEÚDO -->
            <div class="section-container salvos-container">
                <div class="section-header fav-section-title">
                    <h3><span class="fav-star">★</span> Minhas Salvas</h3>
                    <span class="fav-count-hint" id="favCountHint"></span>
                </div>

                <div class="cards-row fav-grid" id="favoritosContainer">
                    <!-- Mídias salvas via localStorage entram aqui -->
                </div>

                <!-- Compat: container antigo de pastas (mantido p/ lógica existente, oculto no novo visual) -->
                <div class="pastas-grid" id="pastasContainer" style="display:none"></div>
            </div>
        </div>

        <!-- PAINEL DIREITO ESTREITO (fixo, sem recolher) -->
        <aside class="fav-aside" id="favAside" aria-label="Organização">
            <div class="fav-aside-inner">
                <div class="fav-side-card fav-side-total">
                    <span class="fav-side-star"><i class="fa-solid fa-star"></i></span>
                    <span>
                        <small>Total de favoritos</small>
                        <strong><span id="totalFavSide">0</span> itens</strong>
                    </span>
                </div>

                <div class="fav-side-card">
                    <div class="fav-side-head">
                        <span><i class="fa-solid fa-folder"></i> Pastas</span>
                    </div>
                    <ul class="fav-folder-list" id="pastaList"></ul>
                    <button class="fav-create" onclick="abrirModalPasta()"><i class="fa-solid fa-plus"></i> Criar pasta</button>
                </div>
            </div>
        </aside>
    </div>

    <!-- Header antigo mantido p/ compat (oculto no CSS novo) -->
    <div class="salvos-header" style="display:none">
        <h2>Meus Salvos</h2>
        <button class="btn-criar-pasta" onclick="abrirModalPasta()">
            <i class="fa-solid fa-plus"></i> Criar Pasta
        </button>
    </div>
</main>

<div id="modalCriarPasta" class="preview-modal">
    <div class="modal-pasta-box">
        <span class="close-preview" onclick="fecharModalPasta()">&times;</span>
        <h3>Criar nova pasta</h3>
        <form id="formNovaPasta">
            <input type="text" id="nomePastaInput" placeholder="Ex: Inspirações de Jogos, Paisagens..." required maxlength="50">
            <button type="submit" class="btn-salvar-pasta">Criar</button>
        </form>
    </div>
</div>

<!-- Visualização do item salvo (abre ao clicar na imagem, como na inicial) -->
<div id="favPreview" class="preview-modal">
    <div class="fav-preview-box">
        <span class="close-preview" onclick="fecharFavPreview()">&times;</span>
        <img id="favPreviewImg" src="" alt="Mídia salva">
        <div class="fav-preview-info">
            <h3 id="favPreviewTitulo">Sem título</h3>
            <p id="favPreviewSub"></p>
            <button class="fav-unfav" onclick="desfavoritarNoPreview(event)">
                <i class="fa-solid fa-heart-crack"></i> Desfavoritar
            </button>
        </div>
    </div>
</div>

<script src="public/script.js?v=14"></script>

<script>
// Funções do Modal de Pasta
function abrirModalPasta() {
    const modal = document.getElementById('modalCriarPasta');
    if (modal) modal.style.display = 'flex';
}

function fecharModalPasta() {
    const modal = document.getElementById('modalCriarPasta');
    if (modal) modal.style.display = 'none';
}

// Helper para padronizar caminhos de IDs/URLs
function normalizar(str) {
    if (!str) return '';
    return String(str).trim().replace(/^https?:\/\/[^\/]+/, '');
}

// Estado local de UI (não altera a lógica de salvamento)
let favFiltroAtual = 'tudo';
let favBuscaAtual = '';
let favOrdemAtual = 'todas';
let favCache = [];

function escaparHtml(s) {
    return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}

function categoriaDe(item) {
    const raw = (item.categoria || item.genero || item.tipo || 'Outros');
    const c = String(raw).toLowerCase();
    return { key: 'outros', label: 'Outros' };
}

function nomePastaParaFiltro(key) {
    const map = { animes:'Animes', jogos:'Jogos', filmes:'Filmes', mangas:'Mangas', outros:'Outros' };
    return map[key] || key;
}

// Pasta efetiva do item: a pasta escolhida ao favoritar (item.pasta);
// itens antigos, sem pasta, caem na inferência por categoria.
function chavePastaBase(nome) {
    const c = String(nome || '').toLowerCase();

    return null;
}

function pastaEfetiva(item) {
    const pasta = (item.pasta || '').trim();
    if (pasta) {
        const base = chavePastaBase(pasta);
        if (base) return { key: base, label: pasta };
        return { key: 'custom:' + pasta, label: pasta };
    }
    return categoriaDe(item);
}

// Renderização dos Itens Salvos (usa dados reais do localStorage)
function carregarMidiasSalvas() {
    const container = document.getElementById('favoritosContainer');
    if (!container) return;

    const favoritos = JSON.parse(localStorage.getItem('meusFavoritos')) || [];
    favCache = favoritos.slice();

    aplicarFiltrosERenderizar();
    atualizarTotais(favoritos.length);
    renderPastas(favoritos);
}

function aplicarFiltrosERenderizar() {
    const container = document.getElementById('favoritosContainer');
    if (!container) return;

    let lista = favCache.slice();

    if (favFiltroAtual !== 'tudo') {
        if (favFiltroAtual.indexOf('custom:') === 0) {
            const nome = favFiltroAtual.slice(7);
            lista = lista.filter(item => ((item.pasta || '').trim()) === nome);
        } else {
            lista = lista.filter(item => pastaEfetiva(item).key === favFiltroAtual);
        }
    }

    if (favBuscaAtual) {
        const q = favBuscaAtual.toLowerCase();
        lista = lista.filter(item => String(item.titulo || '').toLowerCase().includes(q));
    }

    if (favOrdemAtual === 'az') {
        lista.sort((a,b) => String(a.titulo||'').localeCompare(String(b.titulo||'')));
    } else if (favOrdemAtual === 'antigos') {
        lista.reverse();
    }

    const hint = document.getElementById('favCountHint');
    if (hint) hint.textContent = lista.length === 1 ? '1 item' : lista.length + ' itens';

    if (lista.length === 0) {
        container.innerHTML = '<div class="fav-empty"><span class="fav-empty-icon"><i class="fa-regular fa-bookmark"></i></span><p>' +
            (favCache.length === 0 ? 'Você ainda não salvou nenhuma mídia.' : 'Nenhum item combina com este filtro.') +
            '</p></div>';
        return;
    }

    container.innerHTML = '';

    let html = '';
    lista.forEach(item => {
        const imagemSrc = item.imagem && item.imagem !== '' ? item.imagem : 'img/placeholder.jpg';
        const idIdentificador = normalizar(item.id || item.imagem);
        const titulo = escaparHtml(item.titulo || 'Sem título');
        const pasta = pastaEfetiva(item);
        const detalhe = escaparHtml(item.detalhe || (item.curtidas ? item.curtidas + ' curtidas' : pasta.label));
        const views = escaparHtml(item.curtidas || 0);
        const idJs = idIdentificador.replace(/'/g, "\\'");

        const cardHTML = `
            <article class="media-card fav-card" data-id="${escaparHtml(idIdentificador)}" data-titulo="${titulo}" data-cat="${escaparHtml(pasta.key)}">
                <div class="fav-thumb" onclick="abrirFavItem(event, '${idJs}')" title="Abrir">
                    <img src="${escaparHtml(imagemSrc)}" alt="${titulo}" loading="lazy" onerror="this.onerror=null;this.src='img/placeholder.jpg'">
                    <button class="fav-heart active" aria-label="Favoritado" onclick="removerSalvo(event, '${idJs}', this)"><i class="fa-solid fa-heart"></i></button>
                    <span class="fav-badge">${escaparHtml(pasta.label)}</span>
                </div>
                <div class="fav-body">
                    <h4 class="fav-title">${titulo}</h4>
                    <p class="fav-sub">${escaparHtml(pasta.label)} · ${detalhe}</p>
                    <div class="fav-meta">
                        <span class="fav-cat-pill">${escaparHtml(pasta.label)}</span>
                        <span class="fav-views"><i class="fa-regular fa-eye"></i> ${views}</span>
                        <span class="fav-dots" onclick="toggleFavMenu(event, this)" title="Opções"><i class="fa-solid fa-ellipsis-vertical"></i></span>
                    </div>
                    <div class="fav-menu">
                        <button onclick="abrirFavItem(event, '${idJs}')"><i class="fa-regular fa-eye"></i> Abrir</button>
                    </div>
                </div>
                <div class="card-footer-info" style="display:none">
                    <span class="curtida">
                        <i class="fa-regular fa-heart"></i>
                        <span>${views}</span>
                    </span>
                    <span><i class="fa-regular fa-comment"></i> 0</span>
                    <i class="fa-solid fa-bookmark bookmark-icon active" onclick="removerSalvo(event, '${idJs}', this)"></i>
                </div>
            </article>
        `;
        html += cardHTML;
    });
    container.innerHTML = html;
}

function toggleFavMenu(event, el) {
    if (event) { event.stopPropagation(); event.preventDefault(); }
    const card = el.closest('.fav-card');
    const menu = card ? card.querySelector('.fav-menu') : null;
    document.querySelectorAll('.fav-menu.open').forEach(m => { if (m !== menu) m.classList.remove('open'); });
    if (menu) menu.classList.toggle('open');
}

let favPreviewId = null;

function abrirFavItem(event, idOuImagem) {
    if (event) { event.stopPropagation(); event.preventDefault(); }
    document.querySelectorAll('.fav-menu.open').forEach(m => m.classList.remove('open'));
    const alvo = normalizar(idOuImagem);
    const item = (favCache || []).find(it => normalizar(it.id) === alvo || normalizar(it.imagem) === alvo);
    if (!item) return;

    favPreviewId = alvo;
    const img = document.getElementById('favPreviewImg');
    const titulo = document.getElementById('favPreviewTitulo');
    const sub = document.getElementById('favPreviewSub');
    const modal = document.getElementById('favPreview');
    if (img) {
        img.onerror = function() { this.onerror = null; this.src = 'img/placeholder.jpg'; };
        img.src = item.imagem || 'img/placeholder.jpg';
        img.alt = item.titulo || 'Mídia salva';
    }
    if (titulo) titulo.textContent = item.titulo || 'Sem título';
    if (sub) sub.textContent = categoriaDe(item).label + ' · ' + (item.curtidas || 0) + ' curtidas';
    if (modal) modal.classList.add('show');
}

function fecharFavPreview() {
    const modal = document.getElementById('favPreview');
    if (modal) modal.classList.remove('show');
    favPreviewId = null;
}

function desfavoritarNoPreview(event) {
    if (!favPreviewId) return;
    const id = favPreviewId;
    let card = null;
    const container = document.getElementById('favoritosContainer');
    if (container) {
        try {
            card = container.querySelector('.fav-card[data-id="' + id.replace(/"/g, '\\"') + '"]');
        } catch (e) { card = null; }
    }
    fecharFavPreview();
    removerSalvo(event, id, card);
    if (!card) aplicarFiltrosERenderizar();
}

function atualizarTotais(total) {
    const b = document.getElementById('totalFavSide');
    if (b) b.textContent = total;
}

function renderPastas(favoritos) {
    const list = document.getElementById('pastaList');
    // Só pastas criadas pela pessoa (o site sobe limpo, sem pastas padrão)
    let custom = [];
    try { custom = JSON.parse(localStorage.getItem('minhasPastas')) || []; } catch(e) { custom = []; }
    custom = custom.filter(n => n && String(n).trim() !== '');

    const contar = (key) => favoritos.filter(it => pastaEfetiva(it).key === key).length;

    if (!list) return;
    list.innerHTML = '';

    if (custom.length === 0) {
        list.innerHTML = '<li class="fav-folders-empty">Nenhuma pasta ainda.<br>Crie a primeira abaixo.</li>';
    }

    custom.forEach(nome => {
        const li = document.createElement('li');
        li.className = 'fav-folder-row';
        li.innerHTML = `<button class="fav-folder">
                <span class="fav-folder-ic roxo"><i class="fa-solid fa-folder"></i></span>
                <span class="fav-folder-tx"><strong>${escaparHtml(nome)}</strong><small>${contar('custom:' + nome)} itens</small></span>
                <i class="fa-solid fa-chevron-right"></i>
            </button>
            <button class="fav-folder-del" title="Excluir pasta"><i class="fa-solid fa-xmark"></i></button>`;
        li.querySelector('.fav-folder').addEventListener('click', () => {
            definirFiltro('custom:' + nome);
        });
        li.querySelector('.fav-folder-del').addEventListener('click', (e) => {
            excluirPasta(nome, e);
        });
        list.appendChild(li);
    });

    // Compat: espelha pastas criadas no container antigo
    const antigo = document.getElementById('pastasContainer');
    if (antigo) {
        antigo.innerHTML = custom.map(n => `<div class="pasta-card"><div class="pasta-capa capa-vazia"><i class="fa-solid fa-folder"></i></div><div class="pasta-info"><h3>${escaparHtml(n)}</h3></div></div>`).join('');
    }
}

function definirFiltro(key) {
    favFiltroAtual = key;
    document.querySelectorAll('.fav-chip').forEach(b => {
        const on = b.dataset.filter === key;
        b.classList.toggle('active', on);
        b.setAttribute('aria-selected', on ? 'true' : 'false');
    });
    aplicarFiltrosERenderizar();
}

function excluirPasta(nome, event) {
    if (event) { event.stopPropagation(); event.preventDefault(); }
    if (!confirm('Excluir a pasta "' + nome + '"?\nOs itens salvos nela continuam em Favoritos.')) return;

    let custom = [];
    try { custom = JSON.parse(localStorage.getItem('minhasPastas')) || []; } catch(e) { custom = []; }
    custom = custom.filter(n => n !== nome);
    try { localStorage.setItem('minhasPastas', JSON.stringify(custom)); } catch(e) {}

    // Solta os itens da pasta excluída (continuam salvos, sem pasta)
    let favoritos = [];
    try { favoritos = JSON.parse(localStorage.getItem('meusFavoritos')) || []; } catch(e) { favoritos = []; }
    let mudou = false;
    favoritos.forEach(item => {
        if ((item.pasta || '') === nome) { delete item.pasta; mudou = true; }
    });
    if (mudou) {
        try { localStorage.setItem('meusFavoritos', JSON.stringify(favoritos)); } catch(e) {}
    }
    favCache = favoritos.slice();

    if (favFiltroAtual === 'custom:' + nome) {
        favFiltroAtual = 'tudo';
        document.querySelectorAll('.fav-chip').forEach(b => {
            const on = b.dataset.filter === 'tudo';
            b.classList.toggle('active', on);
            b.setAttribute('aria-selected', on ? 'true' : 'false');
        });
    }
    renderPastas(favoritos);
    aplicarFiltrosERenderizar();
}

function removerSalvo(event, idOuImagem, iconeElemento) {
    // IMPEDE que o clique se espalhe para o script.js
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }

    let favoritos = JSON.parse(localStorage.getItem('meusFavoritos')) || [];
    const alvo = normalizar(idOuImagem);

    // Filtra e remove
    favoritos = favoritos.filter(item => {
        const itemId = normalizar(item.id);
        const itemImg = normalizar(item.imagem);
        return itemId !== alvo && itemImg !== alvo;
    });

    // Salva o array limpo
    localStorage.setItem('meusFavoritos', JSON.stringify(favoritos));
    favCache = favoritos.slice();

    // Remove da tela
    const card = iconeElemento ? iconeElemento.closest('.media-card, .card') : null;
    if (card) {
        card.remove();
    }

    atualizarTotais(favoritos.length);
    renderPastas(favoritos);

    const container = document.getElementById('favoritosContainer');
    if (container && container.querySelectorAll('.fav-card').length === 0) {
        aplicarFiltrosERenderizar();
    } else {
        const hint = document.getElementById('favCountHint');
        if (hint && container) {
            const n = container.querySelectorAll('.fav-card').length;
            hint.textContent = n === 1 ? '1 item' : n + ' itens';
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    // Sidebar fixa nesta página: sem abrir/fechar (evita o "piscar" do nome)
    const sidebar = document.getElementById('sidebar');
    const openBtn = document.getElementById('open_btn');
    if (sidebar) sidebar.classList.remove('open-sidebar');
    if (openBtn) {
        openBtn.style.display = 'none';
        openBtn.replaceWith(openBtn.cloneNode(true));
        const fresh = document.getElementById('open_btn');
        if (fresh) fresh.style.display = 'none';
    }

    carregarMidiasSalvas();

    document.querySelectorAll('.fav-chip').forEach(btn => {
        btn.addEventListener('click', () => definirFiltro(btn.dataset.filter));
    });

    const search = document.getElementById('favSearch');
    if (search) {
        search.addEventListener('input', (e) => {
            favBuscaAtual = e.target.value.trim();
            aplicarFiltrosERenderizar();
        });
    }

    const tipo = document.getElementById('favTipoMidia');
    if (tipo) {
        tipo.addEventListener('change', (e) => {
            favOrdemAtual = e.target.value;
            aplicarFiltrosERenderizar();
        });
    }

    const form = document.getElementById('formNovaPasta');
    if (form) {
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            const input = document.getElementById('nomePastaInput');
            const nome = (input.value || '').trim();
            if (!nome) return;
            try {
                const arr = JSON.parse(localStorage.getItem('minhasPastas')) || [];
                arr.push(nome);
                localStorage.setItem('minhasPastas', JSON.stringify(arr));
            } catch(err) {}
            input.value = '';
            fecharModalPasta();
            renderPastas(JSON.parse(localStorage.getItem('meusFavoritos')) || []);
        });
    }

    document.addEventListener('click', () => {
        document.querySelectorAll('.fav-menu.open').forEach(m => m.classList.remove('open'));
    });

    const modalPasta = document.getElementById('modalCriarPasta');
    if (modalPasta) {
        modalPasta.addEventListener('click', (e) => {
            if (e.target === modalPasta) fecharModalPasta();
        });
    }

    const preview = document.getElementById('favPreview');
    if (preview) {
        preview.addEventListener('click', (e) => {
            if (e.target === preview) fecharFavPreview();
        });
    }
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            fecharFavPreview();
            fecharModalPasta();
        }
    });
});
</script>

</body>
</html>
