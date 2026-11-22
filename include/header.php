<?php
/**
 * ReddArt — cabeçalho compartilhado (sidebar, barra superior e abertura do conteúdo).
 *
 * Variáveis opcionais antes do include:
 * $tituloPagina, $corpoClasse, $ocultarTopbar
 */

// Se a sessão não estiver ativa, inicia
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once __DIR__ . '/../setup/conexao.php';

// Se o usuário não estiver logado, manda ele para a tela de login
if (!isset($_SESSION['idUsuario'])) {
    header("Location: login.php");
    exit;
}

$idUsuario = (int) $_SESSION['idUsuario'];

// Busca os dados atualizados do usuário
$sqlUsuario = "SELECT * FROM tblUsuario WHERE idUsuario = ? LIMIT 1";
$stmtUsuario = mysqli_prepare($conn, $sqlUsuario);
mysqli_stmt_bind_param($stmtUsuario, "i", $idUsuario);
mysqli_stmt_execute($stmtUsuario);
$resUsuario = mysqli_stmt_get_result($stmtUsuario);
$usuario = $resUsuario ? mysqli_fetch_assoc($resUsuario) : null;

if (!$usuario) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit;
}

$nomeExibicao = trim((string) ($usuario['userNome'] ?? '')) ?: 'Artista';
$biografia    = $usuario['userDescricao'] ?? '';
$fotoPerfil   = !empty($usuario['userFoto']) ? $usuario['userFoto'] : 'uploads/usuNovoPerfil.jpg';
$bannerAtual  = !empty($usuario['userBanner']) ? $usuario['userBanner'] : 'uploads/BannerPrin2.jpg';
$emailUsuario = $usuario['userEmail'] ?? '';
$nickUsuario  = trim((string) ($usuario['userNick'] ?? ''));
$ehAdmin      = ((int) ($_SESSION['usuario_cargo'] ?? 0) === 2);

// Descobre qual é a página atual para marcar o item ativo do menu
$paginaAtual = basename($_SERVER['PHP_SELF'] ?? 'index.php');

$titulosPagina = [
    'index.php'      => 'Início',
    'favoritos.php'  => 'Salvos',
    'adicionar.php'  => 'Publicar',
    'perfil.php'     => 'Perfil',
    'editarPerfil.php' => 'Editar perfil',
    'galeria.php'    => 'Galeria',
    'suporte.php'    => 'Suporte',
    'noticias.php'   => 'Notícias',
];

$tituloPagina = isset($tituloPagina) && $tituloPagina !== ''
    ? $tituloPagina
    : ($titulosPagina[$paginaAtual] ?? 'ReddArt');

$tituloDocumento = $tituloPagina !== 'ReddArt' ? $tituloPagina . ' · ReddArt' : 'ReddArt — arte e comunidade';
$corpoClasse = $corpoClasse ?? '';

$navPrincipal = [
    ['chave' => 'index.php',     'href' => 'index.php',     'icone' => 'fa-house',    'rotulo' => 'Início'],
    ['chave' => 'adicionar.php', 'href' => 'adicionar.php', 'icone' => 'fa-plus',     'rotulo' => 'Publicar'],
    ['chave' => 'favoritos.php', 'href' => 'favoritos.php', 'icone' => 'fa-bookmark', 'rotulo' => 'Salvos'],
    ['chave' => 'perfil.php',    'href' => 'perfil.php',    'icone' => 'fa-user',     'rotulo' => 'Perfil'],
    ['chave' => 'suporte.php',   'href' => 'suporte.php',   'icone' => 'fa-headset',  'rotulo' => 'Suporte'],
];

// Compatibilidade: atalho de notícias apenas quando a página tiver conteúdo
if (is_file(__DIR__ . '/../noticias.php') && (int) @filesize(__DIR__ . '/../noticias.php') > 64) {
    array_splice($navPrincipal, 3, 0, [[
        'chave'  => 'noticias.php',
        'href'   => 'noticias.php',
        'icone'  => 'fa-newspaper',
        'rotulo' => 'Notícias',
    ]]);
}

function rd_html($valor)
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#0b0a10">
    <meta name="description" content="<?php echo rd_html('Comunidade criativa ReddArt — descubra, publique e salve artes.'); ?>">
    <title><?php echo rd_html($tituloDocumento); ?></title>

    <link rel="icon" type="image/svg+xml" href="img/favicon.svg">

    <!-- CDN do FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/index.css?v=14">
    <link rel="stylesheet" href="css/header.css?v=1">
    <link rel="stylesheet" href="css/responsivo.css?v=1">
    <link rel="icon" type="image/png" href="img/logo.png">
</head>
<body class="<?php echo rd_html($corpoClasse); ?>">

    <!-- Aplica o tema salvo antes da página renderizar para evitar piscar -->
    <script>
        (function () {
            try {
                if (localStorage.getItem('tema') === 'escuro') {
                    document.body.classList.add('dark');
                }
            } catch (e) { }
        })();
    </script>

    <a class="rd-pular" href="#conteudo">Pular para o conteúdo</a>

    <!-- ============================ SIDEBAR ============================ -->
    <aside class="rd-sidebar" id="rdSidebar" aria-label="Navegação principal">
        <div class="rd-sidebar__topo">
            <a class="rd-logo" href="index.php" aria-label="ReddArt — página inicial">
                <span class="rd-logo__marca" aria-hidden="true">R</span>
                <span class="rd-logo__texto">Redd<em>Art</em></span>
            </a>
            <button type="button" class="rd-sidebar__fechar" id="rdFecharMenu" aria-label="Fechar menu">
                <i class="fa-solid fa-xmark" aria-hidden="true"></i>
            </button>
        </div>

        <a class="rd-sidebar__usuario" href="perfil.php">
            <img src="<?php echo rd_html($fotoPerfil); ?>" alt="Foto de <?php echo rd_html($nomeExibicao); ?>" width="44" height="44" data-fallback="uploads/usuNovoPerfil.jpg">
            <span class="rd-sidebar__usuario-info">
                <strong><?php echo rd_html($nomeExibicao); ?></strong>
                <small><?php echo $nickUsuario !== '' ? '@' . rd_html($nickUsuario) : rd_html($emailUsuario); ?></small>
            </span>
        </a>

        <nav class="rd-nav" aria-label="Seções da plataforma">
            <?php foreach ($navPrincipal as $item):
                $chave = $item['chave'];
                $ativo = ($paginaAtual === $chave);
                if ($chave === 'perfil.php') {
                    $ativo = in_array($paginaAtual, ['perfil.php', 'galeria.php', 'editarPerfil.php'], true);
                }
            ?>
                <a class="rd-nav__item<?php echo $ativo ? ' is-ativo' : ''; ?>" href="<?php echo rd_html($item['href']); ?>"<?php echo $ativo ? ' aria-current="page"' : ''; ?>>
                    <i class="fa-solid <?php echo rd_html($item['icone']); ?>" aria-hidden="true"></i>
                    <span><?php echo rd_html($item['rotulo']); ?></span>
                </a>
            <?php endforeach; ?>

            <?php if ($ehAdmin): ?>
                <a class="rd-nav__item" href="admin/home.php">
                    <i class="fa-solid fa-shield-halved" aria-hidden="true"></i>
                    <span>Administração</span>
                </a>
            <?php endif; ?>
        </nav>

        <div class="rd-sidebar__rodape">
            <button type="button" class="rd-nav__item" data-tema-toggle aria-pressed="false">
                <i class="fa-solid fa-circle-half-stroke" aria-hidden="true"></i>
                <span>Aparência</span>
                <span class="rd-nav__tema-indicador" data-tema-rotulo>Escuro</span>
            </button>
            <button type="button" class="rd-nav__item" data-config-abrir>
                <i class="fa-solid fa-gear" aria-hidden="true"></i>
                <span>Configurações</span>
            </button>
            <form action="logout.php" method="post">
                <button type="submit" class="rd-nav__item rd-nav__item--sair">
                    <i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i>
                    <span>Sair</span>
                </button>
            </form>
        </div>
    </aside>

    <div class="rd-drawer-overlay" id="rdDrawerOverlay"></div>

    <!-- ============================ TOPBAR ============================ -->
    <?php if (empty($ocultarTopbar)): ?>
    <header class="rd-topbar">
        <button type="button" class="rd-topbar__menu" id="rdAbrirMenu" aria-label="Abrir menu" aria-controls="rdSidebar" aria-expanded="false">
            <i class="fa-solid fa-bars" aria-hidden="true"></i>
        </button>

        <a class="rd-topbar__logo" href="index.php" aria-label="ReddArt — início">
            <span class="rd-logo__marca" aria-hidden="true">R</span>
            <span class="rd-logo__texto">Redd<em>Art</em></span>
        </a>

        <h1 class="rd-topbar__titulo"><?php echo rd_html($tituloPagina); ?></h1>

        <div class="rd-topbar__acoes">
            <?php include_once __DIR__ . '/pesquisaUsuarios.php'; ?>
            <?php include_once __DIR__ . '/notificacoes.php'; ?>
            <a class="rd-topbar__icone" href="index.php" aria-label="Explorar artes" title="Explorar">
                <i class="fa-solid fa-compass" aria-hidden="true"></i>
            </a>
            <button type="button" class="rd-topbar__icone" data-tema-toggle aria-label="Alternar tema claro e escuro" title="Alternar tema">
                <i class="fa-solid fa-circle-half-stroke" aria-hidden="true"></i>
            </button>
            <details class="rd-menu-usuario">
                <summary aria-label="Menu da conta">
                    <img src="<?php echo rd_html($fotoPerfil); ?>" alt="" width="34" height="34" data-fallback="uploads/usuNovoPerfil.jpg">
                </summary>
                <div class="rd-menu-usuario__painel">
                    <div class="rd-menu-usuario__cabecalho">
                        <strong><?php echo rd_html($nomeExibicao); ?></strong>
                        <small><?php echo rd_html($emailUsuario); ?></small>
                    </div>
                    <a href="perfil.php"><i class="fa-solid fa-user" aria-hidden="true"></i> Meu perfil</a>
                    <a href="editarPerfil.php"><i class="fa-solid fa-pen-to-square" aria-hidden="true"></i> Editar perfil</a>
                    <a href="favoritos.php"><i class="fa-solid fa-bookmark" aria-hidden="true"></i> Salvos</a>
                    <a href="amizades.php"><i class="fa-solid fa-user-group" aria-hidden="true"></i> Amigos</a>
                    <a href="suporte.php"><i class="fa-solid fa-headset" aria-hidden="true"></i> Suporte</a>
                    <form action="logout.php" method="post">
                        <button type="submit"><i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i> Sair</button>
                    </form>
                </div>
            </details>
        </div>
    </header>
    <?php endif; ?>

    <!-- ============================ CONFIGURAÇÕES ============================ -->
    <div id="overlay"></div>

    <div id="config_popup" role="dialog" aria-modal="true" aria-labelledby="configTitulo">
        <div class="popup_header">
            <h2 id="configTitulo">Configurações</h2>
            <button type="button" id="close_popup" aria-label="Fechar configurações">
                <i class="fa-solid fa-xmark" aria-hidden="true"></i>
            </button>
        </div>

        <div class="popup_body">
            <a href="perfil.php" class="config_option">
                <i class="fa-solid fa-user" aria-hidden="true"></i>
                <span>Perfil</span>
            </a>

            <a href="editarPerfil.php" class="config_option">
                <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i>
                <span>Editar perfil</span>
            </a>

            <button type="button" class="config_option" data-tema-toggle>
                <i class="fa-solid fa-circle-half-stroke" aria-hidden="true"></i>
                <span>Aparência</span>
            </button>

            <a href="suporte.php" class="config_option">
                <i class="fa-solid fa-headset" aria-hidden="true"></i>
                <span>Contato e Suporte</span>
            </a>

            <form action="logout.php" method="post">
                <button type="submit" class="config_option logout">
                    <i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i>
                    <span>Sair</span>
                </button>
            </form>
        </div>
    </div>
