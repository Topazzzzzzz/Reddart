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
                $erro = "Senha incorreta!";
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
    <link rel="stylesheet" href="css/login.css">
</head>

<body>

    <!-- Coluna Esquerda -->
    <div class="coluna">
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
        <h1 class="logo">REDDART</h1>
        <div class="form-card">
            <form class="form" method="POST" action="">
                <div class="input-group">
                    <label>E-mail:</label>
                    <input type="email" name="userEmail" placeholder="seuemail@exemplo.com" required>
                </div>
                <div class="input-group">
                    <label>Senha:</label>
                    <input type="password" name="userSenha" placeholder="••••••••••••" required>
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
    <div class="coluna">
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