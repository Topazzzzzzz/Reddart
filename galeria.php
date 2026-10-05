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

// 3. Busca as 4 publicações mais recentes do usuário
$publicacoes = [];
$sqlPublicacoes = "SELECT idPublicacao, pubLink, pubLegenda FROM tblPublicacoes WHERE idUsuario = ? ORDER BY pubHora DESC LIMIT 4";
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
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="PT-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Galeria | ReddArt </title>

    <!-- Estilos Personalizados -->
    <link rel="stylesheet" href="css/Galeria.css">

    <!-- Google Fonts: Archivo Black e Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo+Black&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Prociono&display=swap" rel="stylesheet">
</head>

<body>

    <div class="banner">
        <img src="<?php echo $bannerPerfil; ?>" alt="Banner de <?php echo $nomeExibicao; ?>" class="banner-img">
    </div>

    <div class="Header">
        <div class="avatar">
            
        </div>
    </div>

    <!--Footer do Desktop -->
    <footer class="footer">
        <div class="footer-top">
            <div class="footer-brand">
                <a href="https:reddart.hubsapiens.com.br">
                    <h2 class="footer-logo">ReddArt</h2>
                </a>
            </div>

            <div class="footer-col">
                <h3>Navegação</h3>
                <a href="Perfil.php">Perfil</a>
                <a href="publicacoesPerfil.php">Publicações</a>
                <a href="perfil2.php">Coleções</a>
            </div>

            <div class="footer-col">
                <h3>Redes Sociais</h3>
                <a href="">Instagram</a>
                <a href="">Twitter / X</a>
                <a href="">Pixiv</a>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; 2026 ReddArt. Todos os direitos reservados.</p>
        </div>
    </footer>

</body>

</html>