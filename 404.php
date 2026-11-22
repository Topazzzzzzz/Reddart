<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

http_response_code(404);

// Deteta automaticamente a pasta raiz (funciona no XAMPP e online)
$baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');

// Verifica se o usuário está logado para definir o destino e o texto do botão
if (isset($_SESSION['idUsuario'])) {
    $destinoUrl = $baseUrl . '/index.php';
    $textoBotao = 'Voltar para o Início';
    $iconeBotao = 'fa-house';
} else {
    $destinoUrl = $baseUrl . '/login.php';
    $textoBotao = 'Voltar para o Login';
    $iconeBotao = 'fa-right-to-bracket';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reddart - Página em Desenvolvimento</title>
    <link rel="icon" type="image/png" href="img/logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/responsivo.css?v=1">
    <link rel="stylesheet" href="css/404.css">
</head>
<body>

    <div class="container-404">
        <div class="icone-construcao">
            <i class="fa-solid fa-code"></i>
        </div>
        <div class="codigo-erro">404</div>
        <h1>Página Inexistente ou em Desenvolvimento</h1>
        <p>Esta funcionalidade ainda está sendo construída pela equipe do <strong>Reddart</strong> ou o endereço acessado não existe.</p>
        
        <a href="<?php echo htmlspecialchars($destinoUrl); ?>" class="btn-voltar">
            <i class="fa-solid <?php echo $iconeBotao; ?>"></i>
            <?php echo $textoBotao; ?>
        </a>
    </div>

</body>
</html>
