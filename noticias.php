<?php
include_once 'include/header.php'; // Carrega a tua Conexão + Sidebar Original do Site

$resNoticias = false;

try {
    $sqlNoticias = "SELECT * FROM tblNoticias ORDER BY dataPublicacao DESC";
    $resNoticias = @mysqli_query($conn, $sqlNoticias);
} catch (Exception $e) {
    $resNoticias = false;
}
?>
    <main id="conteudo" tabindex="-1">
        <link rel="stylesheet" href="css/noticias.css">

        <div class="section-container" style="padding: 20px 40px;">
            <div class="section-header" style="margin-bottom: 30px;">
                <h2 style="font-family: 'Archivo Black', sans-serif; font-size: 32px; color: #fff; display: flex; align-items: center; gap: 12px;">
                    <i class="fa-solid fa-newspaper" style="color: #6366F1;"></i> AVISOS & NOVIDADES
                </h2>
                <p style="color: #a1a1aa; margin-top: 5px;">Fique por dentro das atualizações da plataforma, regras e anúncios importantes.</p>
            </div>

            <div class="noticias-grid" style="display: flex; flex-direction: column; gap: 25px;">
                <?php if ($resNoticias && mysqli_num_rows($resNoticias) > 0): ?>
                    <?php while ($noticia = mysqli_fetch_assoc($resNoticias)): 
                        $titulo    = htmlspecialchars($noticia['titulo'] ?? 'Aviso Importante');
                        $conteudo  = htmlspecialchars($noticia['conteudo'] ?? '');
                        $imagem    = !empty($noticia['imagem']) ? htmlspecialchars($noticia['imagem']) : null;
                        $data      = !empty($noticia['dataPublicacao']) ? date('d/m/Y \à\s H:i', strtotime($noticia['dataPublicacao'])) : date('d/m/Y');
                        $categoria = htmlspecialchars($noticia['categoria'] ?? 'Aviso');
                    ?>
                        <article class="noticia-card">
                            <?php if ($imagem): ?>
                                <div class="card-banner">
                                    <img src="<?php echo $imagem; ?>" alt="<?php echo $titulo; ?>">
                                </div>
                            <?php endif; ?>

                            <div class="card-body">
                                <div class="card-meta">
                                    <span class="badge-categoria"><i class="fa-solid fa-tag"></i> <?php echo $categoria; ?></span>
                                    <span class="card-data"><i class="fa-regular fa-clock"></i> <?php echo $data; ?></span>
                                </div>

                                <h3 class="card-titulo"><?php echo $titulo; ?></h3>
                                <p class="card-texto"><?php echo nl2br($conteudo); ?></p>
                            </div>
                        </article>
                    <?php endwhile; ?>
                <?php else: ?>
                    <!-- CARD DE AVISO ESTÁTICO PADRÃO SE A TABELA ESTIVER VAZIA OU NÃO EXISTIR -->
                    <article class="noticia-card">
                        <div class="card-body">
                            <div class="card-meta">
                                <span class="badge-categoria"><i class="fa-solid fa-bell"></i> Comunicado Oficial</span>
                                <span class="card-data"><i class="fa-regular fa-clock"></i> <?php echo date('d/m/Y'); ?></span>
                            </div>
                            <h3 class="card-titulo">Bem-vindo ao Mural de Avisos da ReddArt!</h3>
                            <p class="card-texto">Este é o canal oficial onde compartilharemos notas de atualização da plataforma, novas funcionalidades disponíveis para artistas, eventos e comunicados gerais da comunidade.</p>
                        </div>
                    </article>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <script src="public/script.js?v=10"></script>
</body>
</html>