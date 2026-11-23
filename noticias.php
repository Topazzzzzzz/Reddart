<?php
include_once 'include/header.php'; // Carrega a conexão + sidebar do site

// Garante o helper de escape mesmo que o header mude
if (!function_exists('rd_html')) {
    function rd_html($valor)
    {
        return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
    }
}

$resNoticias = false;

try {
    $sqlNoticias = "SELECT * FROM tblNoticias ORDER BY dataPublicacao DESC";
    $resNoticias = @mysqli_query($conn, $sqlNoticias);
} catch (Throwable $e) {
    $resNoticias = false;
}
?>
    <main id="conteudo" class="noticias-main" tabindex="-1">
        <link rel="stylesheet" href="css/noticias.css?v=2">

        <div class="noticias-wrap">

            <header class="noticias-header">
                <h2 class="noticias-titulo">
                    <i class="fa-solid fa-newspaper" aria-hidden="true"></i>
                    Avisos &amp; Novidades
                </h2>
                <p class="noticias-subtitulo">Fique por dentro das atualizações da plataforma, regras e anúncios importantes.</p>
            </header>

            <div class="noticias-lista">
                <?php if ($resNoticias && mysqli_num_rows($resNoticias) > 0): ?>
                    <?php while ($noticia = mysqli_fetch_assoc($resNoticias)):
                        $titulo    = rd_html($noticia['titulo'] ?? 'Aviso Importante');
                        $conteudo  = rd_html($noticia['conteudo'] ?? '');
                        $imagem    = !empty($noticia['imagem']) ? rd_html($noticia['imagem']) : null;
                        $data      = !empty($noticia['dataPublicacao']) ? date('d/m/Y \à\s H:i', strtotime($noticia['dataPublicacao'])) : date('d/m/Y');
                        $categoria = rd_html($noticia['categoria'] ?? 'Aviso');
                    ?>
                        <article class="noticia-card">
                            <?php if ($imagem): ?>
                                <div class="card-banner">
                                    <img src="<?php echo $imagem; ?>"
                                         alt="<?php echo $titulo; ?>"
                                         loading="lazy"
                                         decoding="async"
                                         onerror="this.parentNode.remove()">
                                </div>
                            <?php endif; ?>

                            <div class="card-body">
                                <div class="card-meta">
                                    <span class="badge-categoria"><i class="fa-solid fa-tag" aria-hidden="true"></i> <?php echo $categoria; ?></span>
                                    <span class="card-data"><i class="fa-regular fa-clock" aria-hidden="true"></i> <?php echo $data; ?></span>
                                </div>

                                <h3 class="card-titulo"><?php echo $titulo; ?></h3>
                                <p class="card-texto"><?php echo nl2br($conteudo); ?></p>
                            </div>
                        </article>
                    <?php endwhile; ?>
                <?php else: ?>
                    <!-- CARD ESTÁTICO PADRÃO SE A TABELA ESTIVER VAZIA OU NÃO EXISTIR -->
                    <article class="noticia-card">
                        <div class="card-body">
                            <div class="card-meta">
                                <span class="badge-categoria"><i class="fa-solid fa-bell" aria-hidden="true"></i> Comunicado Oficial</span>
                                <span class="card-data"><i class="fa-regular fa-clock" aria-hidden="true"></i> <?php echo date('d/m/Y'); ?></span>
                            </div>
                            <h3 class="card-titulo">Bem-vindo ao Mural de Avisos da ReddArt!</h3>
                            <p class="card-texto">Este é o canal oficial onde compartilharemos notas de atualização da plataforma, novas funcionalidades disponíveis para artistas, eventos e comunicados gerais da comunidade.</p>
                        </div>
                    </article>
                <?php endif; ?>
            </div>

        </div>
    </main>

    <script src="public/script.js?v=14"></script>
</body>
</html>