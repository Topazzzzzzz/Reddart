<link rel="stylesheet" href="css/pesquisaUsuarios.css?v=5">
<div class="rd-busca-usuarios" data-rd-busca data-endpoint="api/pesquisarUsuarios.php">
    <button type="button" class="rd-busca-usuarios__gatilho" aria-label="Pesquisar usuários" aria-expanded="false" aria-controls="rdBuscaPainel">
        <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
    </button>

    <div class="rd-busca-usuarios__painel" id="rdBuscaPainel">
        <div class="rd-busca-usuarios__campo">
            <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
            <input
                type="search"
                id="rdBuscaInput"
                class="rd-busca-usuarios__input"
                placeholder="Pesquisar usuários..."
                aria-label="Pesquisar usuários"
                autocomplete="off"
                spellcheck="false"
                role="combobox"
                aria-expanded="false"
                aria-controls="rdBuscaResultados"
                aria-autocomplete="list"
            >
            <button type="button" class="rd-busca-usuarios__limpar" aria-label="Limpar pesquisa" hidden>
                <i class="fa-solid fa-xmark" aria-hidden="true"></i>
            </button>
        </div>

        <div class="rd-busca-usuarios__resultados" id="rdBuscaResultados" role="listbox" aria-label="Usuários encontrados" hidden></div>
    </div>
</div>
<script src="js/pesquisaUsuarios.js?v=2" defer></script>
