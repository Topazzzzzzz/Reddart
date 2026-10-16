<?php
session_start();

if (!isset($_SESSION['usuario_cargo'])) {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>REDDART - Início</title>
    <link rel="stylesheet" href="../css/home.css">
</head>
<body>

    <h1 class="logo">REDDART</h1>

    <div class="admin-card">
        
        <?php if ((int)$_SESSION['usuario_cargo'] === 2): ?>

            <span class="badge-admin">PAINEL ADMINISTRATIVO</span>
            <br><br>
            <h2>Bem-vindo, <?php echo htmlspecialchars($_SESSION['userNome'] ?? $_SESSION['usuario_nome'] ?? 'Admin'); ?>!</h2>
            <p class="subtitulo">Você possui acesso de administrador ao sistema.</p>

            <ul class="admin-menu">
                <li><a href="gerenciar_usuarios.php"> Gerenciar Usuários</a></li>
                <li><a href="gerenciar_publicacoes.php"> Configurações do Sistema</a></li>
                <li><a href="relatorio.php"> Relatórios de Acesso</a></li>
            </ul>
        <?php else: ?>

            <span class="badge-admin" style="color: #a0a0b0; border-color: #a0a0b0; background: transparent;">ÁREA DO USUÁRIO</span>
            <h2>Bem-vindo, <?php echo htmlspecialchars($_SESSION['userNome'] ?? $_SESSION['usuario_nome'] ?? 'Usuário'); ?>!</h2>
            <p class="subtitulo">Você está logado na sua conta.</p>
        <?php endif; ?>

        <a href="logout.php" class="btn-logout">Sair da Conta</a>

    </div>

</body>
</html>