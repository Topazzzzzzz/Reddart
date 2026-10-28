<?php
// Se a sessão não estiver ativa, inicia
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once __DIR__ . "/../setup/conexao.php";

// Se o usuário não estiver logado, manda ele para a tela de login
if (!isset($_SESSION['idUsuario'])) {
    header("Location: login.php");
    exit;
}

$idUsuario = $_SESSION['idUsuario'];

// Busca os dados atualizados do usuário
$sqlUsuario = "SELECT * FROM tblUsuario WHERE idUsuario = $idUsuario";
$resUsuario = mysqli_query($conn, $sqlUsuario);
$usuario = mysqli_fetch_assoc($resUsuario);

$nomeExibicao = !empty($usuario['userNome']) ? $usuario['userNome'] : 'Meu Perfil';
$biografia = $usuario['userDescricao'] ?? '';
$fotoPerfil = !empty($usuario['userFoto']) ? $usuario['userFoto'] : 'uploads/usuNovoPerfil.jpg';
$bannerAtual = !empty($usuario['userBanner']) ? $usuario['userBanner'] : 'uploads/BannerPrin2.jpg';

// Descobre qual é a página atual para marcar a classe 'active' dinamicamente
$paginaAtual = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reddart</title>

    <!-- CDN do FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/index.css?v=13">
</head>
<body>

    <!-- Aplica o tema salvo antes da página renderizar para evitar piscar -->
    <script>
        if (localStorage.getItem('tema') === 'escuro') {
            document.body.classList.add('dark');
        }
    </script>

    <nav id="sidebar">
        <div id="sidebar_content">
            <div id="user">
                <a href="perfil.php">
                    <img src="<?php echo htmlspecialchars($fotoPerfil); ?>" alt="Avatar" id="user_avatar">
                </a>

                <p id="user_infos">
                    <span class="item_descricao">
                        <?php echo htmlspecialchars($nomeExibicao); ?>
                    </span>
                    <span class="item_descricao" style="font-size: 12px; opacity: 0.7;">
                        <?php echo htmlspecialchars($usuario['userEmail'] ?? ''); ?>
                    </span>
                    <span class="item_descricao">
                        <?php echo htmlspecialchars($biografia); ?>
                    </span>
                </p>
            </div>

            <ul id="side_items">
                <li class="side-item <?php echo ($paginaAtual == 'index.php') ? 'active' : ''; ?>">
                    <a href="index.php">
                        <i class="fa-solid fa-house-chimney"></i>
                        <span class="item_descricao">Home</span>
                    </a>
                </li>

                <li class="side-item <?php echo ($paginaAtual == 'favoritos.php') ? 'active' : ''; ?>">
                    <a href="favoritos.php">
                        <i class="fa-regular fa-bookmark"></i>
                        <span class="item_descricao">Bookmarks</span>
                    </a>
                </li>

                <!-- NOVA ABA DE NOTÍCIAS -->
                <li class="side-item <?php echo ($paginaAtual == 'noticias.php') ? 'active' : ''; ?>">
                    <a href="noticias.php">
                        <i class="fa-regular fa-newspaper"></i>
                        <span class="item_descricao">Notícias</span>
                    </a>
                </li>

                <li class="side-item <?php echo ($paginaAtual == 'top.php' || $paginaAtual == 'top.html') ? 'active' : ''; ?>">
                    <a href="top.html">
                        <i class="fa-solid fa-ranking-star"></i>
                        <span class="item_descricao">Ranking</span>
                    </a>
                </li>

                <li class="side-item <?php echo ($paginaAtual == 'adicionar.php') ? 'active' : ''; ?>">
                    <a href="adicionar.php">
                        <i class="fa-solid fa-plus"></i>
                        <span class="item_descricao">Adicionar</span>
                    </a>
                </li>
            </ul>

            <button id="open_btn">
                <i id="open_btn_icon" class="fa-solid fa-caret-right"></i>
            </button>
        </div>

        <div id="logout">
            <button id="logout_btn">
                <i class="fa-solid fa-gear"></i>
                <span class="item_descricao">Configurações</span>
            </button>
        </div>
    </nav>

    <div id="overlay"></div>

    <div id="config_popup">
        <div class="popup_header">
            <h2>Configurações</h2>
            <button id="close_popup">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="popup_body">
            <a href="perfil.php" class="config_option">
                <i class="fa-solid fa-user"></i>
                <span>Perfil</span>
            </a>

            <button class="config_option">
                <i class="fa-solid fa-palette"></i>
                <span>Aparência</span>
            </button>

            <button class="config_option">
                <i class="fa-solid fa-bell"></i>
                <span>Notificações</span>
            </button>

            <a href="suporte.php" class="config_option">
                <i class="fa-solid fa-headset"></i>
                <span>Contato e Suporte</span>
            </a>

            <a href="login.php" class="config_option logout">
                <i class="fa-solid fa-right-from-bracket"></i>
                <span>Logout</span>
            </a>
        </div>
    </div>