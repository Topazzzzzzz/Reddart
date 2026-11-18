<?php


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Se a página que incluiu o header já buscou o $usuario, reaproveita.
// Se não, busca aqui (nome, e-mail e foto).
if (!isset($usuario) || !is_array($usuario)) {
    include_once __DIR__ . "/../setup/conexao.php";

    $usuario = [];
    $idTop = (int) ($_SESSION['idUsuario'] ?? 0);

    if ($idTop > 0 && isset($conn)) {
        $stmtTop = mysqli_prepare($conn, "SELECT userNome, userEmail, userFoto FROM tblUsuario WHERE idUsuario = ?");
        if ($stmtTop) {
            mysqli_stmt_bind_param($stmtTop, "i", $idTop);
            mysqli_stmt_execute($stmtTop);
            $resTop = mysqli_stmt_get_result($stmtTop);
            $usuario = $resTop ? (mysqli_fetch_assoc($resTop) ?: []) : [];
            mysqli_stmt_close($stmtTop);
        }
    }
}

$topNome  = !empty($usuario['userNome']) ? $usuario['userNome'] : 'Meu Perfil';
$topEmail = $usuario['userEmail'] ?? '';
$topFoto  = !empty($usuario['userFoto']) ? $usuario['userFoto'] : 'uploads/usuNovoPerfil.jpg';

// Página atual, para marcar o item ativo
$paginaTop = basename($_SERVER['PHP_SELF']);

// Itens de navegação: [arquivos que ativam, link, ícone, rótulo]
$itensTop = [
    [['index.php'],                 'index.php',      'fa-solid fa-house-chimney', 'Início'],
    [['favoritos.php'],             'favoritos.php',  'fa-regular fa-bookmark',    'Salvos'],
    [['noticias.php'],              'noticias.php',   'fa-regular fa-newspaper',   'Notícias'],
    [['top.php', 'top.html'],       'top.html',       'fa-solid fa-ranking-star',  'Ranking'],
];
?>

<!-- Aplica o tema salvo (mesma regra do header da lateral) -->
<script>
    if (localStorage.getItem('tema') === 'escuro') {
        document.body.classList.add('dark');
    }
</script>

<header class="topnav" id="topnav">
    <div class="topnav-inner">

        <a class="topnav-brand" href="index.php" aria-label="Reddart - Início">
            <img src="img/logo.png" alt="" width="32" height="32">
            <span>Reddart</span>
        </a>

        <nav class="topnav-items" aria-label="Navegação principal">
            <ul>
                <?php foreach ($itensTop as [$arquivos, $link, $icone, $rotulo]): ?>
                    <?php $ativo = in_array($paginaTop, $arquivos, true); ?>
                    <li class="topnav-item<?php echo $ativo ? ' active' : ''; ?>">
                        <a href="<?php echo $link; ?>" <?php echo $ativo ? 'aria-current="page"' : ''; ?> title="<?php echo $rotulo; ?>">
                            <i class="<?php echo $icone; ?>"></i>
                            <span><?php echo $rotulo; ?></span>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </nav>

        <div class="topnav-right">
            <a class="topnav-user" href="perfil.php" title="Meu perfil">
                <img src="<?php echo htmlspecialchars($topFoto); ?>" alt="Avatar">
                <span class="topnav-user-tx">
                    <strong><?php echo htmlspecialchars($topNome); ?></strong>
                    <small><?php echo htmlspecialchars($topEmail); ?></small>
                </span>
            </a>

            <div class="topnav-config">
                <button type="button" class="topnav-gear<?php echo $paginaTop === 'suporte.php' ? ' active' : ''; ?>"
                        id="topnavGear" aria-haspopup="true" aria-expanded="false" aria-controls="topnavMenu"
                        title="Configurações">
                    <i class="fa-solid fa-gear"></i>
                    <span>Mais Opções</span>
                </button>

                <div class="topnav-menu" id="topnavMenu" hidden>
                    <a href="perfil.php"><i class="fa-solid fa-user"></i> Perfil</a>
                    <a href="index.php" class="logout"><i class="fa-solid fa-right-from-bracket"></i> Voltar</a>
                </div>
            </div>
        </div>

    </div>
</header>

<script>
(function () {
    const gear = document.getElementById('topnavGear');
    const menu = document.getElementById('topnavMenu');
    if (!gear || !menu) return;

    function fechar() {
        menu.hidden = true;
        gear.setAttribute('aria-expanded', 'false');
    }

    gear.addEventListener('click', function (e) {
        e.stopPropagation();
        const abrir = menu.hidden;
        menu.hidden = !abrir;
        gear.setAttribute('aria-expanded', abrir ? 'true' : 'false');
    });

    document.addEventListener('click', function (e) {
        if (!menu.contains(e.target)) fechar();
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') { fechar(); gear.focus(); }
    });
})();
</script>