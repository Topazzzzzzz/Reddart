<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

ini_set('display_errors', 0);
error_reporting(E_ALL);

include "setup/conexao.php";

// ==========================================================================
// PROCESSAMENTO AJAX DO CADASTRO (Retorna JSON)
// ==========================================================================
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    header('Content-Type: application/json');

    $nome = trim($_POST['userNome'] ?? '');
    $nick = trim($_POST['userNick'] ?? '');
    $email = trim($_POST['userEmail'] ?? '');
    $senha = $_POST['userSenha'] ?? '';
    $senhaConfirm = $_POST['userSenhaConfirm'] ?? '';
    $descricao = trim($_POST['userDescricao'] ?? '');
    $idCargoPadrao = 1; 

    $erro = "";

    // Bloqueio de termos restritos no Nick
    $nickMinusculo = strtolower($nick);
    if (str_contains($nickMinusculo, 'logicfox') || str_contains($nickMinusculo, 'spinelli')) {
        $erro = "Alerta: O nickname possui termos restritos.";
    }

    // Validação de Confirmação de Senha
    if (empty($erro) && $senha !== $senhaConfirm) {
        $erro = "As senhas não coincidem! Por favor, digite novamente.";
    }

    // Upload de Foto de Perfil
    $caminhoFoto = "img/default-avatar.png"; 

    if (empty($erro) && isset($_FILES['userFoto']) && $_FILES['userFoto']['error'] === UPLOAD_ERR_OK) {
        $extensao = strtolower(pathinfo($_FILES['userFoto']['name'], PATHINFO_EXTENSION));
        $extensoesPermitidas = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($extensao, $extensoesPermitidas)) {
            $novoNomeFoto = md5(uniqid(time())) . "." . $extensao;
            $diretorioDestino = "uploads/perfil/";

            if (!is_dir($diretorioDestino)) {
                mkdir($diretorioDestino, 0777, true);
            }

            $caminhoFoto = $diretorioDestino . $novoNomeFoto;
            move_uploaded_file($_FILES['userFoto']['tmp_name'], $caminhoFoto);
        } else {
            $erro = "Formato de imagem inválido! Use JPG, PNG ou WEBP.";
        }
    }

    // Upload de Banner de Perfil
    $caminhoBanner = "img/default-banner.jpg";

    if (empty($erro) && isset($_FILES['userBanner']) && $_FILES['userBanner']['error'] === UPLOAD_ERR_OK) {
        $extensaoBanner = strtolower(pathinfo($_FILES['userBanner']['name'], PATHINFO_EXTENSION));
        $extensoesPermitidas = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($extensaoBanner, $extensoesPermitidas)) {
            $novoNomeBanner = md5(uniqid(time())) . "." . $extensaoBanner;
            $diretorioDestino = "uploads/perfil/";

            if (!is_dir($diretorioDestino)) {
                mkdir($diretorioDestino, 0777, true);
            }

            $caminhoBanner = $diretorioDestino . $novoNomeBanner;
            move_uploaded_file($_FILES['userBanner']['tmp_name'], $caminhoBanner);
        } else {
            $erro = "Formato de imagem inválido! Use JPG, PNG ou WEBP.";
        }
    }

    if (empty($erro)) {
        if (!empty($nome) && !empty($nick) && !empty($email) && !empty($senha)) {
            $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

            $sql = "INSERT INTO tblUsuario (userNome, userNick, userEmail, userSenha, userDescricao, userFoto, userBanner, idCargo) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssssi", $nome, $nick, $email, $senhaHash, $descricao, $caminhoFoto, $caminhoBanner, $idCargoPadrao);

            if ($stmt->execute()) {
                $novoIdUsuario = $stmt->insert_id;
                $stmt->close();

                $_SESSION['idUsuario'] = $novoIdUsuario;
                $_SESSION['userNome'] = $nome;

                $conn->close();

                echo json_encode([
                    'sucesso' => true,
                    'idUsuario' => $novoIdUsuario,
                    'email' => $email,
                    'mensagem' => 'Cadastro realizado! Verifique seu e-mail.'
                ]);
                exit();
            } else {
                if ($conn->errno === 1062) {
                    $erro = "E-mail ou Nick já cadastrados!";
                } else {
                    $erro = "Erro ao cadastrar no banco.";
                }
                $stmt->close();
            }
        } else {
            $erro = "Preencha todos os campos obrigatórios!";
        }
    }

    echo json_encode(['sucesso' => false, 'mensagem' => $erro]);
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - ReddArt</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/cada.css">
</head>

<body>

    <header class="site-header">
        <h1 class="site-title">ReddArt</h1>
    </header>

    <div class="cadastro-container">

        <div id="mensagem-erro" class="erro" style="display: none;"></div>

        <!-- Formulário de Cadastro Principal -->
        <form id="formCadastro" enctype="multipart/form-data" class="form-grid">
            
            <div class="form-group">
                <label for="userNome"> Nickname </label>
                <input type="text" id="userNome" name="userNome" maxlength="75" required placeholder="Ex: Topazzz">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="userNick"> Nome Completo </label>
                    <input type="text" id="userNick" name="userNick" maxlength="50" required placeholder="Ex: Pedro Silveira Santos">
                </div>

                <div class="form-group">
                    <label for="userEmail">E-mail</label>
                    <input type="email" id="userEmail" name="userEmail" maxlength="75" required placeholder="seunome@email.com">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="userSenha">Senha</label>
                    <input type="password" id="userSenha" name="userSenha" maxlength="20" required placeholder="Sua senha">
                </div>

                <div class="form-group">
                    <label for="userSenhaConfirm">Confirmar Senha</label>
                    <input type="password" id="userSenhaConfirm" name="userSenhaConfirm" maxlength="20" required placeholder="Repita a senha">
                </div>
            </div>

            <div class="form-group">
                <label>Foto de Perfil</label>
                <div class="file-drop-area">
                    <i class="fa-solid fa-cloud-arrow-up"></i>
                    <span id="foto-label">Escolha ou arraste sua foto</span>
                    <input type="file" id="userFoto" name="userFoto" accept="image/*" onchange="updateFileName(this, 'foto-label')">
                </div>
            </div>

            <div class="form-group">
                <label> Banner Do Perfil </label>
                <div class="file-drop-area">
                    <i class="fa-solid fa-cloud-arrow-up"></i>
                    <span id="banner-label">Escolha ou arraste seu banner</span>
                    <input type="file" id="userBanner" name="userBanner" accept="image/*" onchange="updateFileName(this, 'banner-label')">
                </div>
            </div>

            <div class="form-group">
                <label for="userDescricao">Bio / Descrição</label>
                <textarea id="userDescricao" name="userDescricao" maxlength="200" placeholder="Conte um pouco sobre você..."></textarea>
            </div>

            <button type="submit" id="btnSubmit" class="btn-cadastro">Criar Conta</button>

            <a href="login.php" class="link-login">
                Já tem uma conta? <strong>Faça Login</strong>
            </a>
        </form>

        <!-- Seção do Código de Confirmação (Inicia Oculta) -->
        <div id="secaoVerificacao" style="display: none; text-align: center; margin-top: 20px;">
            <h2>Digite o Código de Verificação</h2>
            <p style="margin-bottom: 15px; color: #ccc;">Enviamos um código de 6 dígitos para o seu e-mail.</p>
            
            <div class="form-group">
                <input type="text" id="inputCodigoConfirmacao" maxlength="6" placeholder="000000" style="text-align: center; font-size: 24px; letter-spacing: 5px;">
            </div>

            <button type="button" id="btnConfirmarCodigo" class="btn-cadastro" style="margin-top: 15px;">Validar E-mail</button>
        </div>

    </div>

    <!-- Script com a lógica do Node.js integrada -->
    <script src="public/script.js"></script>

    <script>
        function updateFileName(input, labelId) {
            const label = document.getElementById(labelId);
            const textoPadrao = label.dataset.default || label.textContent;
            label.dataset.default = textoPadrao;

            if (input.files && input.files[0]) {
                label.textContent = input.files[0].name;
            } else {
                label.textContent = textoPadrao;
            }
        }

        let idUsuarioCadastrado = null;

        // Submissão do formulário via JavaScript (AJAX)
        document.getElementById('formCadastro').addEventListener('submit', async function (e) {
            e.preventDefault();

            const erroDiv = document.getElementById('mensagem-erro');
            const btnSubmit = document.getElementById('btnSubmit');
            
            erroDiv.style.display = 'none';
            btnSubmit.disabled = true;
            btnSubmit.textContent = "Cadastrando...";

            const formData = new FormData(this);

            try {
                const response = await fetch('cada.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.sucesso) {
                    idUsuarioCadastrado = data.idUsuario;

                    // Oculta formulário e exibe tela de código
                    document.getElementById('formCadastro').style.display = 'none';
                    document.getElementById('secaoVerificacao').style.display = 'block';

                    // Envia e-mail de código via servidor Node.js (porta 3000)
                    if (typeof solicitarCodigoConfirmacao === 'function') {
                        await solicitarCodigoConfirmacao(idUsuarioCadastrado, data.email);
                    }
                } else {
                    erroDiv.textContent = data.mensagem;
                    erroDiv.style.display = 'block';
                    btnSubmit.disabled = false;
                    btnSubmit.textContent = "Criar Conta";
                }
            } catch (error) {
                console.error(error);
                erroDiv.textContent = "Erro ao processar o cadastro.";
                erroDiv.style.display = 'block';
                btnSubmit.disabled = false;
                btnSubmit.textContent = "Criar Conta";
            }
        });

        // Clique no botão de confirmar código
        document.getElementById('btnConfirmarCodigo').addEventListener('click', async function () {
            const codigo = document.getElementById('inputCodigoConfirmacao').value.trim();

            if (codigo.length !== 6) {
                alert('Digite o código de 6 dígitos recebido.');
                return;
            }

            if (typeof confirmarCodigoEmail === 'function') {
                const verificado = await confirmarCodigoEmail(idUsuarioCadastrado, codigo);
                if (verificado) {
                    alert('E-mail verificado! Redirecionando para o login...');
                    window.location.href = 'login.php';
                }
            }
        });
    </script>
</body>

</html>