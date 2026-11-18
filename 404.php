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
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background-color: #12121a;
            color: #ffffff;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            text-align: center;
            padding: 20px;
        }

        .container-404 {
            background-color: #1a1a26;
            border: 1px solid #2a2a3d;
            border-radius: 12px;
            padding: 40px 30px;
            max-width: 480px;
            width: 100%;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.4);
        }

        .icone-construcao {
            font-size: 3.5rem;
            color: #5542f6;
            margin-bottom: 15px;
        }

        .codigo-erro {
            font-size: 4rem;
            font-weight: bold;
            color: #5542f6;
            line-height: 1;
            margin-bottom: 10px;
        }

        h1 {
            font-size: 1.5rem;
            margin-bottom: 12px;
        }

        p {
            color: #aaa;
            font-size: 0.95rem;
            line-height: 1.5;
            margin-bottom: 28px;
        }

        .btn-voltar {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background-color: #5542f6;
            color: #ffffff;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: bold;
            transition: background-color 0.2s, transform 0.2s;
        }

        .btn-voltar:hover {
            background-color: #4331d6;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>

    <div class="container-404">
        <div class="icone-construcao">
            <i class="fa-solid fa-code"></i>
        </div>
        <div class="codigo-erro">404</div>
        <h1>Página em Desenvolvimento</h1>
        <p>Esta funcionalidade ainda está sendo construída pela equipe do <strong>Reddart</strong> ou o endereço acessado não existe.</p>
        
        <a href="<?php echo htmlspecialchars($destinoUrl); ?>" class="btn-voltar">
            <i class="fa-solid <?php echo $iconeBotao; ?>"></i>
            <?php echo $textoBotao; ?>
        </a>
    </div>

</body>
</html>