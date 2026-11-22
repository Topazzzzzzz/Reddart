<?php

include "setup/conexao.php";

$erro = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Reseta sessões anteriores
    session_start();
    session_unset();
    session_destroy();
    session_start();

    $email = trim($_POST['userEmail'] ?? '');
    $senha = $_POST['userSenha'] ?? '';

    if (!empty($email) && !empty($senha)) {
        // Uso de Prepared Statements para evitar SQL Injection
        $stmt = $conn->prepare("SELECT idUsuario, userNome, userSenha FROM tblUsuario WHERE userEmail = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado && $resultado->num_rows > 0) {
            $usuario = $resultado->fetch_assoc();

            // password_verify para comparar o texto com o hash do BD (já alterado para VARCHAR 255)
            if (password_verify($senha, $usuario['userSenha'])) {
                $_SESSION['idUsuario'] = $usuario['idUsuario'];
                $_SESSION['userNome'] = $usuario['userNome'];

                header("Location: index.php");
                exit;
            } else {
                $erro = "Email ou Senha incorreto!";
            }
        } else {
            $erro = "E-mail não encontrado!";
        }
        $stmt->close();
    } else {
        $erro = "Preencha todos os campos!";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>REDDART - Login</title>
    <link rel="icon" type="image/png" href="img/logo.png">
    <link rel="stylesheet" href="css/login.css?v=6">
</head>

<body>

    <!-- Coluna Esquerda -->
    <div class="coluna coluna-faixa-superior" data-faixa-login="superior">
        <div class="carrossel">
            <div class="track track-up">
                <img src="fotosLogin/FireBird.png" alt="Art">
                <img src="fotosLogin/Legendary.png" alt="Art">
                <img src="fotosLogin/FloralHeaven.png" alt="Art">
                <img src="fotosLogin/FurImmer.webp" alt="Art">
                <img src="fotosLogin/OperaOfTheWasteland.png" alt="Art">
                <img src="fotosLogin/LehreRose.png" alt="Art">
            </div>
            <div class="track track-down">
                <img src="fotosLogin/Era.jpg" alt="Art">
                <img src="fotosLogin/ExposeBurnout.png" alt="Art">
                <img src="fotosLogin/FightAttitude.png" alt="Art">
                <img src="fotosLogin/HowlyAmbition.jpg" alt="Art">
                <img src="fotosLogin/WhatAnExplosion.jpg" alt="Art">
                <img src="fotosLogin/Savage.jpg" alt="Art">
            </div>
        </div>
    </div>

    <!-- Centro -->
    <div class="centro">
        <div class="centro-simbolo" aria-hidden="true">
            <svg viewBox="0 0 360 520" role="presentation">
                <path d="M78 58h142c62 0 101 35 101 91 0 43-25 73-69 84l81 163h-83l-72-153h-25v153H78V58Zm75 66v55h65c18 0 28-10 28-28 0-17-10-27-28-27h-65Z" />
                <path d="m180 15 16 43 43 16-43 16-16 43-16-43-43-16 43-16 16-43Z" />
            </svg>
        </div>

        <h1 class="logo">
            <svg class="logo-insignia" viewBox="0 0 40 40" aria-hidden="true" focusable="false">
                <path d="M20 2 24.5 15.5 38 20l-13.5 4.5L20 38l-4.5-13.5L2 20l13.5-4.5L20 2Z" />
                <circle cx="20" cy="20" r="3" />
            </svg>
            <span>REDDART</span>
        </h1>
        <div class="form-card">
            <form class="form" method="POST" action="">
                <div class="input-group">
                    <label for="loginEmail">E-mail:</label>
                    <div class="input-shell">
                        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                            <rect x="3" y="5" width="18" height="14" rx="2" />
                            <path d="m4 7 8 6 8-6" />
                        </svg>
                        <input id="loginEmail" type="email" name="userEmail" placeholder="seuemail@exemplo.com" autocomplete="email" required>
                    </div>
                </div>
                <div class="input-group">
                    <label for="loginSenha">Senha:</label>
                    <div class="input-shell">
                        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                            <rect x="4" y="10" width="16" height="11" rx="2" />
                            <path d="M8 10V7a4 4 0 0 1 8 0v3m-4 5v2" />
                        </svg>
                        <input id="loginSenha" type="password" name="userSenha" placeholder="••••••••••••" autocomplete="current-password" required>
                    </div>
                </div>
                <button type="submit" class="btn-primary">Entrar</button>
                <?php if ($erro): ?>
                    <p style="color:#ff5c5c; font-size:0.85rem;"><?php echo htmlspecialchars($erro); ?></p>
                <?php endif; ?>
                <p class="signup-text">Não tem uma conta? <a href="cada.php">Faça Cadastro</a></p>
            </form>
        </div>
    </div>

    <!-- Coluna Direita -->
    <div class="coluna coluna-faixa-inferior" data-faixa-login="inferior">
        <div class="carrossel">
            <div class="track track-down">
                <img src="fotosLogin/STheWay.png" alt="Art">
                <img src="fotosLogin/KiLLKiSS.png" alt="Art">
                <img src="fotosLogin/KamiSamaBaka.png" alt="Art">
                <img src="fotosLogin/ImPrisonedXII.png" alt="Art">
                <img src="fotosLogin/CrucifixX.png" alt="Art">
                <img src="fotosLogin/ChoirSChoir.png" alt="Art">
            </div>
            <div class="track track-up">
                <img src="fotosLogin/FE1.png" alt="Art">
                <img src="fotosLogin/FE2.png" alt="Art">
                <img src="fotosLogin/FE3.png" alt="Art">
                <img src="fotosLogin/FE4.jpg" alt="Art">
                <img src="fotosLogin/FE5.jpg" alt="Art">
                <img src="fotosLogin/TP.jpg" alt="Art">
            </div>
        </div>
    </div>

</body>

</html>
