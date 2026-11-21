<?php
session_start();
include "setup/conexao.php";

// Se o usuário não estiver logado, manda ele para a tela de login
if (!isset($_SESSION['idUsuario'])) {
    header("Location: login.php");
    exit;
}

$idUsuario = intval($_SESSION['idUsuario']);

// 1. Busca os dados do usuário com Prepared Statement (Segurança contra SQL Injection)
$sqlUsuario = "SELECT userNome, userDescricao, userFoto, userBanner FROM tblUsuario WHERE idUsuario = ?";
$stmtUser = mysqli_prepare($conn, $sqlUsuario);
mysqli_stmt_bind_param($stmtUser, "i", $idUsuario);
mysqli_stmt_execute($stmtUser);
$resUsuario = mysqli_stmt_get_result($stmtUser);
$usuario = mysqli_fetch_assoc($resUsuario);

if (!$usuario) {
    header("Location: login.php");
    exit;
}

$nomeExibicao = htmlspecialchars($usuario['userNome'] ?? 'Artista');
$descricao    = htmlspecialchars($usuario['userDescricao'] ?? 'Este usuário ainda não escreveu uma biografia.');
$fotoPerfil   = !empty($usuario['userFoto'])   ? htmlspecialchars($usuario['userFoto'])   : 'img/default-avatar.png';
$bannerPerfil = !empty($usuario['userBanner']) ? htmlspecialchars($usuario['userBanner']) : 'img/default-banner.jpg';

// 2. Busca o total de publicações do usuário
$sqlCount = "SELECT COUNT(idPublicacao) as total FROM tblPublicacoes WHERE idUsuario = ?";
$stmtCount = mysqli_prepare($conn, $sqlCount);
mysqli_stmt_bind_param($stmtCount, "i", $idUsuario);
mysqli_stmt_execute($stmtCount);
$resCount = mysqli_stmt_get_result($stmtCount);
$totalObras = mysqli_fetch_assoc($resCount)['total'] ?? 0;

// 3. Busca as publicações do usuário para galeria e modal
$publicacoes = [];
$sqlPublicacoes = "SELECT idPublicacao, pubLink, pubLegenda, pubHora FROM tblPublicacoes WHERE idUsuario = ? ORDER BY pubHora DESC";
$stmtPub = mysqli_prepare($conn, $sqlPublicacoes);
mysqli_stmt_bind_param($stmtPub, "i", $idUsuario);
mysqli_stmt_execute($stmtPub);
$resPublicacoes = mysqli_stmt_get_result($stmtPub);

if ($resPublicacoes) {
    while ($linha = mysqli_fetch_assoc($resPublicacoes)) {
        $publicacoes[] = [
            'id'     => $linha['idPublicacao'],
            'img'    => $linha['pubLink'],
            'titulo' => $linha['pubLegenda'] ?? 'Sem título',
            'data'   => date('d/m/Y H:i', strtotime($linha['pubHora']))
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $nomeExibicao; ?> | ReddArt</title>
    <link rel="icon" type="image/png" href="img/logo.png">
    <link rel="stylesheet" href="css/headerTopo.css">
    <link rel="stylesheet" href="css/perfil.css?v=4">
    <link rel="stylesheet" href="css/footer.css">
    <!-- Fontes & Ícones -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo+Black&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <?php include_once 'include/headerTopo.php'; ?>

    <!-- BANNER E FOTO DO PERFIL -->
    <header class="profile-header">
        <div class="banner-container">
            <img src="<?php echo $bannerPerfil; ?>" alt="Banner de <?php echo $nomeExibicao; ?>" class="banner-img">
        </div>

        <div class="profile-nav-bar">
            <div class="container nav-content">
                <div class="avatar-wrapper">
                    <img src="<?php echo $fotoPerfil; ?>" alt="Foto de <?php echo $nomeExibicao; ?>" class="avatar-img">
                </div>

                <nav class="profile-menu" aria-label="Seções do perfil">
                    <a href="perfil.php" class="active" aria-current="page"><i class="fa-solid fa-user"></i> Perfil</a>
                    <a href="galeria.php"><i class="fa-solid fa-image"></i> Galeria</a>
                    <a href="#"><i class="fa-solid fa-layer-group"></i> Coleções</a>
                    <a href="#"><i class="fa-solid fa-share-nodes"></i> Redes sociais</a>
                </nav>
            </div>
        </div>
    </header>

    <!-- INFORMAÇÕES DO ARTISTA E BOTÃO DE AÇÃO -->
    <main class="container profile-main">
        <div class="profile-info-header">
            <div class="user-details">
                <h1 class="user-name"><?php echo $nomeExibicao; ?></h1>
                <div class="user-meta">
                    <span><i class="fa-solid fa-location-dot"></i> Brasil</span>
                    <span><i class="fa-solid fa-calendar-day"></i> Entrou em 2026</span>
                    <span><i class="fa-solid fa-palette"></i> <?php echo $totalObras; ?> obras</span>
                </div>
            </div>

            <div class="user-actions">
                <a href="editarPerfil.php" class="btn-edit"><i class="fa-solid fa-pen-to-square"></i> Editar perfil</a>
            </div>
        </div>

        <!-- CORPO PRINCIPAL: RECENTES E BIO -->
        <div class="profile-grid">

            <!-- PUBLICAÇÕES RECENTES -->
            <section class="profile-section posts-section">
                <div class="section-header">
                    <h2><i class="fa-solid fa-clock-rotate-left"></i> Publicações recentes</h2>
                    <?php if (!empty($publicacoes)): ?>
                        <a href="galeria.php" class="see-all">Ver todas</a>
                    <?php endif; ?>
                </div>

                <?php if (!empty($publicacoes)): ?>
                    <div class="posts-grid">
                        <?php foreach ($publicacoes as $pub): ?>
                            <article class="post-card" onclick="abrirModalPub('<?php echo $pub['img']; ?>', '<?php echo addslashes($pub['titulo']); ?>', '<?php echo $pub['data']; ?>', <?php echo $pub['id']; ?>)">
                                <div class="post-thumb">
                                    <img src="<?php echo htmlspecialchars($pub['img']); ?>"
                                         alt="<?php echo htmlspecialchars($pub['titulo']); ?>"
                                         loading="lazy">
                                </div>
                                <div class="post-body">
                                    <h3 class="post-title"><?php echo htmlspecialchars($pub['titulo']); ?></h3>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fa-solid fa-paintbrush icon-empty"></i>
                        <p>Você ainda não publicou nenhuma arte.</p>
                        <a href="adicionar.php" class="btn-primary">Publicar primeira arte</a>
                    </div>
                <?php endif; ?>
            </section>

            <!-- SOBRE MIM (BIO) -->
            <aside class="profile-section bio-section">
                <div class="section-header">
                    <h2><i class="fa-solid fa-id-card"></i> Sobre mim</h2>
                </div>
                <div class="bio-content">
                    <p><?php echo nl2br($descricao); ?></p>
                </div>
            </aside>

        </div>
    </main>

    <!-- MODAL DE VISUALIZAÇÃO E EDIÇÃO DA PUBLICAÇÃO -->
    <div id="modalPub" class="modal-overlay" onclick="fecharModalFora(event)">
        <div class="modal-content-box">
            <!-- Botão de Fechar no Canto Superior Direito Absoluto -->
            <button class="modal-close-btn" onclick="fecharModalPub()"><i class="fa-solid fa-xmark"></i></button>

            <div class="modal-left-img">
                <img id="modalImg" src="" alt="Imagem Ampliada">
            </div>
            <div class="modal-right-info">
                <div>
                    <h2 id="modalTitulo" style="margin-top: 15px; font-size: 1.3rem; color: #fff;">Título</h2>
                    <p id="modalData" style="font-size: 0.85rem; color: #aaa; margin-top: 5px;"></p>
                </div>

                <div class="modal-actions-box">
                    <a id="modalBtnEditar" href="#" class="btn-modal-action btn-edit-pub">
                        <i class="fa-solid fa-pen"></i> Editar Título/Gênero
                    </a>
                    <button id="modalBtnExcluir" onclick="" class="btn-modal-action btn-delete-pub">
                        <i class="fa-solid fa-trash"></i> Excluir Publicação
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function abrirModalPub(imgSrc, titulo, data, idPub) {
            document.getElementById('modalImg').src = imgSrc;
            document.getElementById('modalTitulo').innerText = titulo;
            document.getElementById('modalData').innerText = "Publicado em: " + data;
            
            // Configura os links e ações de editar e excluir
            document.getElementById('modalBtnEditar').href = "editarPublicacao.php?id=" + idPub;
            document.getElementById('modalBtnExcluir').setAttribute('onclick', 'confirmarExclusao(' + idPub + ')');

            // Adiciona a classe active para exibir o modal via CSS flex
            document.getElementById('modalPub').classList.add('active');
        }

        function fecharModalPub() {
            document.getElementById('modalPub').classList.remove('active');
        }

        function fecharModalFora(event) {
            if (event.target.id === 'modalPub') {
                fecharModalPub();
            }
        }

        function confirmarExclusao(idPub) {
            if (confirm("Tem certeza que deseja excluir esta publicação?")) {
                window.location.href = "excluirPublicacao.php?id=" + idPub;
            }
        }
    </script>

    <!-- FOOTER DA APLICAÇÃO -->
    <?php include_once 'include/footer.php'; ?>
</body>

</html>