<!-- Arquivo: index.php -->
<?php
session_start();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>REDDART - Login</title>
    <link rel="stylesheet" href="../css/logAdm.css">
</head>
<body>

    <h1 class="logo">Página Administrativa</h1>

    <div class="admin-card">

        <?php if (isset($_GET['erro'])): ?>
            <p class="msg-erro"><?php echo htmlspecialchars($_GET['erro']); ?></p>
        <?php endif; ?>

        <form action="validar.php" method="POST" class="form">
            <div class="input-group">
                <label>E-mail:</label>
                <input type="email" name="email" placeholder="seuemail@exemplo.com" required>
            </div>
            
            <div class="input-group">
                <label>Senha:</label>
                <input type="password" name="senha" placeholder="••••••••••••" required>
            </div>

            <button type="submit" class="btn-admin">Entrar</button>
        </form>

        
    </div>

</body>
</html>