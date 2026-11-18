<?php
session_start();
include "setup/conexao.php";

// Se o usuário não estiver logado, manda ele para a tela de login
if (!isset($_SESSION['idUsuario'])) {
    header("Location: login.php");
    exit;
}

$idUsuario = intval($_SESSION['idUsuario']);

// Busca os dados do usuário usando Prepared Statements (Segurança)
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

// Foto e banner atuais ou padrões
$nomeExibicao       = htmlspecialchars($usuario['userNome'] ?? '');
$descricao          = htmlspecialchars($usuario['userDescricao'] ?? '');
$caminhoFotoAtual   = !empty($usuario['userFoto'])   ? htmlspecialchars($usuario['userFoto'])   : 'img/default-avatar.png';
$caminhoBannerAtual = !empty($usuario['userBanner']) ? htmlspecialchars($usuario['userBanner']) : 'img/default-banner.jpg';
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Perfil | ReddArt</title>

    <link rel="stylesheet" href="css/editarperfil.css">

    <!-- Fontes & Ícones -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo+Black&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>

    <main class="edit-container">
        
        <!-- CABEÇALHO DO FORMULÁRIO -->
        <div class="edit-header">
            <a href="perfil.php" class="btn-back"><i class="fa-solid fa-arrow-left"></i> Voltar ao Perfil</a>
            <h1><i class="fa-solid fa-user-pen"></i> EDITAR PERFIL</h1>
            <p>Atualize suas informações pessoais e personalize a aparência do seu perfil.</p>
        </div>

        <form action="atualizar_perfil.php" method="POST" enctype="multipart/form-data" class="edit-form">

            <!-- PREVIEW DO BANNER -->
            <div class="form-group banner-group">
                <label>Banner do Perfil</label>
                <div class="banner-preview-box">
                    <img src="<?php echo $caminhoBannerAtual; ?>" id="previewBanner" alt="Banner atual">
                    <label for="userBanner" class="upload-badge">
                        <i class="fa-solid fa-camera"></i> Alterar Banner
                    </label>
                </div>
                <input type="file" id="userBanner" name="userBanner" accept="image/*" class="file-input" onchange="previewImage(this, 'previewBanner')">
            </div>

            <!-- PREVIEW DA FOTO DE PERFIL -->
            <div class="form-group avatar-group">
                <label>Foto de Perfil</label>
                <div class="avatar-preview-box">
                    <img src="<?php echo $caminhoFotoAtual; ?>" id="previewFoto" alt="Foto atual">
                    <label for="userFoto" class="upload-badge-avatar">
                        <i class="fa-solid fa-camera"></i>
                    </label>
                </div>
                <input type="file" id="userFoto" name="userFoto" accept="image/*" class="file-input" onchange="previewImage(this, 'previewFoto')">
            </div>

            <!-- CAMPOS DE TEXTO -->
            <div class="form-group">
                <label for="userNome"><i class="fa-solid fa-signature"></i> Nome de Exibição / Apelido</label>
                <input type="text" id="userNome" name="userNome" value="<?php echo $nomeExibicao; ?>" required placeholder="Digite seu nome ou apelido artístico">
            </div>

            <div class="form-group">
                <label for="userDescricao"><i class="fa-solid fa-align-left"></i> Biografia / Descrição</label>
                <textarea id="userDescricao" name="userDescricao" rows="5" placeholder="Conte um pouco sobre você, seu estilo de arte ou redes sociais..."><?php echo $descricao; ?></textarea>
            </div>

            <!-- BOTÕES DE AÇÃO -->
            <div class="form-actions">
                <a href="perfil.php" class="btn-cancel">Cancelar</a>
                <button type="submit" class="btn-salvar"><i class="fa-solid fa-floppy-disk"></i> Salvar Alterações</button>
            </div>

        </form>

    </main>

    <!-- SCRIPT DE PRÉ-VISUALIZAÇÃO DE IMAGEM AO SELECIONAR ARQUIVO -->
    <script>
        function previewImage(input, previewId) {
            const file = input.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById(previewId).src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        }
    </script>

</body>
</html>