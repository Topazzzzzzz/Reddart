<?php
include_once 'include/header.php';

$termoBusca = trim($_GET['busca'] ?? '');
$generoFiltro = isset($_GET['genero']) ? intval($_GET['genero']) : null;
?>
    <main>
        <link rel="stylesheet" href="css/index.css">
        <!-- TOP BAR (Apenas com o botão Dark/Light) -->
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

        <!-- BANNER DE DESTAQUE -->
        <div class="banner-container">
            <div class="banner-card">
                <img src="<?php echo htmlspecialchars($bannerAtual ?? 'img/placeholder.jpg'); ?>" alt="Banner Destaque">
                <?php if (!empty($usuario['userDescricao'])): ?>
                    <div class="banner-info">
                        <h2><?php echo htmlspecialchars($usuario['userDescricao']); ?></h2>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- BARRA DE PESQUISA (Abaixo do Banner) -->
        <div class="search-container">
            <form action="index.php" method="GET" class="search">
                <label for="SearchInput" id="srch">
                    <button type="submit" style="background: none; border: none; color: inherit; cursor: pointer;">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </button>
                </label>
                <input 
                    type="text" 
                    name="busca" 
                    id="SearchInput" 
                    placeholder="Pesquisar..." 
                    value="<?php echo htmlspecialchars($termoBusca); ?>"
                >
                <?php if ($generoFiltro): ?>
                    <input type="hidden" name="genero" value="<?php echo $generoFiltro; ?>">
                <?php endif; ?>
            </form>
        </div>

        <!-- BARRA DE CATEGORIAS / FILTROS PRONTOS -->
        <div class="categorias-wrapper">
            <button type="button" class="cat-arrow cat-arrow-left" id="catArrowLeft" aria-label="Rolar categorias para a esquerda">
                <i class="fa-solid fa-chevron-left"></i>
            </button>
                <div class="categorias-bar" id="categoriasBar">
                    <a href="index.php<?php echo !empty($termoBusca) ? '?busca=' . urlencode($termoBusca) : ''; ?>" class="cat-btn <?php echo is_null($generoFiltro) ? 'active' : ''; ?>">Todos</a>
                    <a href="index.php?genero=1<?php echo !empty($termoBusca) ? '&busca=' . urlencode($termoBusca) : ''; ?>" class="cat-btn <?php echo $generoFiltro === 1 ? 'active' : ''; ?>">Animes</a>
                    <a href="index.php?genero=2<?php echo !empty($termoBusca) ? '&busca=' . urlencode($termoBusca) : ''; ?>" class="cat-btn <?php echo $generoFiltro === 2 ? 'active' : ''; ?>">Jogos</a>
                    <a href="index.php?genero=3<?php echo !empty($termoBusca) ? '&busca=' . urlencode($termoBusca) : ''; ?>" class="cat-btn <?php echo $generoFiltro === 3 ? 'active' : ''; ?>">Fan-Art</a>
                    <a href="index.php?genero=4<?php echo !empty($termoBusca) ? '&busca=' . urlencode($termoBusca) : ''; ?>" class="cat-btn <?php echo $generoFiltro === 4 ? 'active' : ''; ?>">Filmes</a>
                    <a href="index.php?genero=5<?php echo !empty($termoBusca) ? '&busca=' . urlencode($termoBusca) : ''; ?>" class="cat-btn <?php echo $generoFiltro === 5 ? 'active' : ''; ?>">Shitpost</a>
                    <a href="index.php?genero=6<?php echo !empty($termoBusca) ? '&busca=' . urlencode($termoBusca) : ''; ?>" class="cat-btn <?php echo $generoFiltro === 6 ? 'active' : ''; ?>">Esportes</a>
                    <a href="index.php?genero=7<?php echo !empty($termoBusca) ? '&busca=' . urlencode($termoBusca) : ''; ?>" class="cat-btn <?php echo $generoFiltro === 7 ? 'active' : ''; ?>">Livros</a>
                    <a href="index.php?genero=8<?php echo !empty($termoBusca) ? '&busca=' . urlencode($termoBusca) : ''; ?>" class="cat-btn <?php echo $generoFiltro === 8 ? 'active' : ''; ?>">Mangá</a>
                    <a href="index.php?genero=9<?php echo !empty($termoBusca) ? '&busca=' . urlencode($termoBusca) : ''; ?>" class="cat-btn <?php echo $generoFiltro === 9 ? 'active' : ''; ?>">Cartoons</a>
                    <a href="index.php?genero=10<?php echo !empty($termoBusca) ? '&busca=' . urlencode($termoBusca) : ''; ?>" class="cat-btn <?php echo $generoFiltro === 10 ? 'active' : ''; ?>">Moda</a>
                    <a href="index.php?genero=11<?php echo !empty($termoBusca) ? '&busca=' . urlencode($termoBusca) : ''; ?>" class="cat-btn <?php echo $generoFiltro === 11 ? 'active' : ''; ?>">Paisagem</a>
                    <a href="index.php?genero=12<?php echo !empty($termoBusca) ? '&busca=' . urlencode($termoBusca) : ''; ?>" class="cat-btn <?php echo $generoFiltro === 12 ? 'active' : ''; ?>">Viagens</a>
                    <a href="index.php?genero=13<?php echo !empty($termoBusca) ? '&busca=' . urlencode($termoBusca) : ''; ?>" class="cat-btn <?php echo $generoFiltro === 13 ? 'active' : ''; ?>">Geek</a>
                    <a href="index.php?genero=14<?php echo !empty($termoBusca) ? '&busca=' . urlencode($termoBusca) : ''; ?>" class="cat-btn <?php echo $generoFiltro === 14 ? 'active' : ''; ?>">Natureza</a>
                    <a href="index.php?genero=15<?php echo !empty($termoBusca) ? '&busca=' . urlencode($termoBusca) : ''; ?>" class="cat-btn <?php echo $generoFiltro === 15 ? 'active' : ''; ?>">Retratos</a>
                    <a href="index.php?genero=16<?php echo !empty($termoBusca) ? '&busca=' . urlencode($termoBusca) : ''; ?>" class="cat-btn <?php echo $generoFiltro === 16 ? 'active' : ''; ?>">Realismo</a>
                    <a href="index.php?genero=17<?php echo !empty($termoBusca) ? '&busca=' . urlencode($termoBusca) : ''; ?>" class="cat-btn <?php echo $generoFiltro === 17 ? 'active' : ''; ?>">Fantasia</a>
                    <a href="index.php?genero=18<?php echo !empty($termoBusca) ? '&busca=' . urlencode($termoBusca) : ''; ?>" class="cat-btn <?php echo $generoFiltro === 18 ? 'active' : ''; ?>">Terror</a>
                    <a href="index.php?genero=19<?php echo !empty($termoBusca) ? '&busca=' . urlencode($termoBusca) : ''; ?>" class="cat-btn <?php echo $generoFiltro === 19 ? 'active' : ''; ?>">Veículos</a>
                    <a href="index.php?genero=20<?php echo !empty($termoBusca) ? '&busca=' . urlencode($termoBusca) : ''; ?>" class="cat-btn <?php echo $generoFiltro === 20 ? 'active' : ''; ?>">Wallpapers</a>
                    <a href="index.php?genero=21<?php echo !empty($termoBusca) ? '&busca=' . urlencode($termoBusca) : ''; ?>" class="cat-btn <?php echo $generoFiltro === 21 ? 'active' : ''; ?>">Minimalista</a>
                    <a href="index.php?genero=22<?php echo !empty($termoBusca) ? '&busca=' . urlencode($termoBusca) : ''; ?>" class="cat-btn <?php echo $generoFiltro === 22 ? 'active' : ''; ?>">Arquitetura</a>
                    <a href="index.php?genero=23<?php echo !empty($termoBusca) ? '&busca=' . urlencode($termoBusca) : ''; ?>" class="cat-btn <?php echo $generoFiltro === 23 ? 'active' : ''; ?>">Abstrato</a>
                    <a href="index.php?genero=24<?php echo !empty($termoBusca) ? '&busca=' . urlencode($termoBusca) : ''; ?>" class="cat-btn <?php echo $generoFiltro === 24 ? 'active' : ''; ?>">Ficção Científica</a>
                </div>

            <button type="button" class="cat-arrow cat-arrow-right" id="catArrowRight" aria-label="Rolar categorias para a direita">
                <i class="fa-solid fa-chevron-right"></i>
            </button>
        </div>

        <div class="section-container">
            <div class="section-header">
                <h3><?php echo !empty($termoBusca) ? "Resultados para: '" . htmlspecialchars($termoBusca) . "'" : "Recentes"; ?></h3>
            </div>

            <div class="cards-row cards-recentes">
                <?php
                if (!empty($termoBusca)) {
                    $paramBusca = "%" . $termoBusca . "%";
                    $termoInicio = $termoBusca . "%";

                    if ($generoFiltro) {
                        $sqlEmAlta = "SELECT tblPublicacoes.*, tblUsuario.userNome AS autorNome
                                      FROM tblPublicacoes
                                      LEFT JOIN tblUsuario ON tblUsuario.idUsuario = tblPublicacoes.idUsuario
                                      WHERE pubLegenda LIKE ? AND idGenero = ?
                                      ORDER BY 
                                          CASE 
                                              WHEN pubLegenda LIKE ? THEN 1 
                                              ELSE 2 
                                          END ASC, 
                                          idPublicacao DESC 
                                      LIMIT 10";
                        $stmt = $conn->prepare($sqlEmAlta);
                        $stmt->bind_param("sis", $paramBusca, $generoFiltro, $termoInicio);
                    } else {
                        $sqlEmAlta = "SELECT tblPublicacoes.*, tblUsuario.userNome AS autorNome
                                      FROM tblPublicacoes
                                      LEFT JOIN tblUsuario ON tblUsuario.idUsuario = tblPublicacoes.idUsuario
                                      WHERE pubLegenda LIKE ? 
                                      ORDER BY 
                                          CASE 
                                              WHEN pubLegenda LIKE ? THEN 1 
                                              ELSE 2 
                                          END ASC, 
                                          idPublicacao DESC 
                                      LIMIT 10";
                        $stmt = $conn->prepare($sqlEmAlta);
                        $stmt->bind_param("ss", $paramBusca, $termoInicio);
                    }

                    $stmt->execute();
                    $resultadoEmAlta = $stmt->get_result();
                } else {
                    if ($generoFiltro) {
                        $sqlEmAlta = "SELECT tblPublicacoes.*, tblUsuario.userNome AS autorNome
                                      FROM tblPublicacoes
                                      LEFT JOIN tblUsuario ON tblUsuario.idUsuario = tblPublicacoes.idUsuario
                                      WHERE idGenero = ? ORDER BY idPublicacao DESC LIMIT 5";
                        $stmt = $conn->prepare($sqlEmAlta);
                        $stmt->bind_param("i", $generoFiltro);
                        $stmt->execute();
                        $resultadoEmAlta = $stmt->get_result();
                    } else {
                        $sqlEmAlta = "SELECT tblPublicacoes.*, tblUsuario.userNome AS autorNome
                                      FROM tblPublicacoes
                                      LEFT JOIN tblUsuario ON tblUsuario.idUsuario = tblPublicacoes.idUsuario
                                      ORDER BY idPublicacao DESC LIMIT 5";
                        $resultadoEmAlta = mysqli_query($conn, $sqlEmAlta);
                    }
                }

                if ($resultadoEmAlta && mysqli_num_rows($resultadoEmAlta) > 0) {
                    while ($row = mysqli_fetch_assoc($resultadoEmAlta)) {
                        $link = htmlspecialchars($row['pubLink'] ?? 'img/placeholder.jpg', ENT_QUOTES);
                        $legenda = htmlspecialchars($row['pubLegenda'] ?? 'Arte');
                        $tituloJs = htmlspecialchars(addslashes($row['pubLegenda'] ?? 'Arte'), ENT_QUOTES);
                        $autorJs = htmlspecialchars(addslashes($row['autorNome'] ?? 'Usuário'), ENT_QUOTES);
                        // Ajustado para pubCurtidas (com 's')
                        $curtidasPub = intval($row['pubCurtidas'] ?? 0);
                        $dataPub = !empty($row['pubHora']) ? date('d/m/Y H:i', strtotime($row['pubHora'])) : '';
                ?>
                    <div class="media-card" onclick="abrirModal('<?php echo $link; ?>', '<?php echo $autorJs; ?>', '<?php echo $row['idPublicacao']; ?>', '<?php echo $tituloJs; ?>', 'Publicado por <?php echo $autorJs; ?> em <?php echo $dataPub; ?>', <?php echo $curtidasPub; ?>)">
                        <img src="<?php echo $link; ?>" alt="<?php echo $legenda; ?>">
                        <div class="card-footer-info">
                            <span class="curtida" onclick="curtir(event, <?php echo $row['idPublicacao']; ?>)">
                                <i class="fa-regular fa-heart"></i>
                                <span id="curtidas-<?php echo $row['idPublicacao']; ?>">
                                    <?php echo $curtidasPub; ?>
                                </span>
                            </span>
                            <span><i class="fa-regular fa-comment"></i> 0</span>
                            <i class="fa-regular fa-bookmark bookmark-icon"></i>
                        </div>
                    </div>
                <?php
                    }
                } else {
                    echo "<p style='padding: 10px;'>Nenhuma imagem encontrada.</p>";
                }
                ?>
            </div>

            <section class="recentes">
                <div class="section-header">
                    <h3>Galeria</h3>
                </div>

                <div class="cards-row">
                    <?php
                    if ($generoFiltro) {
                        $sqlGaleria = "SELECT tblPublicacoes.*, tblUsuario.userNome AS autorNome
                                       FROM tblPublicacoes
                                       LEFT JOIN tblUsuario ON tblUsuario.idUsuario = tblPublicacoes.idUsuario
                                       WHERE idGenero = ? ORDER BY idPublicacao DESC LIMIT 100 OFFSET 5";
                        $stmtGaleria = $conn->prepare($sqlGaleria);
                        $stmtGaleria->bind_param("i", $generoFiltro);
                        $stmtGaleria->execute();
                        $resultadoGeral = $stmtGaleria->get_result();
                    } else {
                        $sqlGaleria = "SELECT tblPublicacoes.*, tblUsuario.userNome AS autorNome
                                       FROM tblPublicacoes
                                       LEFT JOIN tblUsuario ON tblUsuario.idUsuario = tblPublicacoes.idUsuario
                                       ORDER BY idPublicacao DESC LIMIT 100 OFFSET 5";
                        $resultadoGeral = mysqli_query($conn, $sqlGaleria);
                    }

                    if ($resultadoGeral && mysqli_num_rows($resultadoGeral) > 0) {
                        while ($row = mysqli_fetch_assoc($resultadoGeral)) {
                            $linkGaleria = htmlspecialchars($row['pubLink'], ENT_QUOTES);
                            $legendaGaleria = htmlspecialchars($row['pubLegenda']);
                            $tituloGaleriaJs = htmlspecialchars(addslashes($row['pubLegenda']), ENT_QUOTES);
                            $autorGaleriaJs = htmlspecialchars(addslashes($row['autorNome'] ?? 'Usuário'), ENT_QUOTES);
                            // Ajustado para pubCurtidas (com 's')
                            $curtidasGaleria = intval($row['pubCurtidas'] ?? 0);
                            $dataGaleria = !empty($row['pubHora']) ? date('d/m/Y H:i', strtotime($row['pubHora'])) : '';
                    ?>
                        <div class="media-card" onclick="abrirModal('<?php echo $linkGaleria; ?>', '<?php echo $autorGaleriaJs; ?>', '<?php echo $row['idPublicacao']; ?>', '<?php echo $tituloGaleriaJs; ?>', 'Publicado por <?php echo $autorGaleriaJs; ?> em <?php echo $dataGaleria; ?>', <?php echo $curtidasGaleria; ?>)">
                            <img src="<?php echo $linkGaleria; ?>" alt="<?php echo $legendaGaleria; ?>">
                            <div class="card-footer-info">
                                <span class="curtida" onclick="curtir(event, <?php echo $row['idPublicacao']; ?>)">
                                    <i class="fa-regular fa-heart"></i>
                                    <span id="curtidas-<?php echo $row['idPublicacao']; ?>">
                                        <?php echo $curtidasGaleria; ?>
                                    </span>
                                </span>
                                <span><i class="fa-regular fa-comment"></i> 0</span>
                                <i class="fa-regular fa-bookmark bookmark-icon"></i>
                            </div>
                        </div>
                    <?php
                        }
                    } else {
                        echo "<p style='color: #666; padding: 10px;'>Mais nenhuma imagem por enquanto.</p>";
                    }
                    ?>
                </div>
            </section>
        </div>
    </main>

    <!-- MODAL DE PREVISÃO -->
    <div id="previewModal" class="preview-modal">
        <div class="conteudo-modal-container">
            <span class="close-preview">&times;</span>
            <div class="modal-esquerda">
                <img id="previewImage" src="" alt="Imagem Ampliada">
            </div>
            <div class="modal-direita">
                <h3 id="modalTitulo">Sem título</h3>
                <p id="modalDescricao"></p>

                <!-- ESTRUTURA DOS 3 RETÂNGULOS UNIFICADOS -->
                <div class="modal-stats">
                    <div class="stat-btn" id="btnCurtirModal" onclick="curtir(event, idPostAtualModal)">
                        <i class="fa-regular fa-heart"></i>
                        <span id="modalCurtidas">0</span>
                    </div>
                    <div class="stat-btn">
                        <i class="fa-regular fa-comment"></i>
                        <span id="modalComentarios">0</span>
                    </div>
                    <div class="stat-btn" id="btnSalvarModal" onclick="alternarIconeSalvar()">
                        <i class="fa-regular fa-bookmark"></i>
                    </div>
                </div>

                <div class="modal-comentarios">
                    <div id="listaComentarios">
                        <p class="sem-comentario">Nenhum comentário ainda.</p>
                    </div>
                    <div class="comentario-form">
                        <input type="text" id="inputComentario" placeholder="Escreva um comentário..." maxlength="300">
                        <button type="button" onclick="enviarComentario()"><i class="fa-solid fa-paper-plane"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="public/script.js?v=14"></script>
</body>
</html>