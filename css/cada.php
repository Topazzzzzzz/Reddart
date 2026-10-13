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
    $aceitouTermos = isset($_POST['aceitouTermos']) ? $_POST['aceitouTermos'] : false;
    $idCargoPadrao = 1;

    $erro = "";

    // Validação do aceite dos termos
    if (!$aceitouTermos) {
        $erro = "Você precisa concordar com os Termos de Uso e Política de Privacidade para se cadastrar.";
    }

    // Bloqueio de termos restritos no Nick
    $nickMinusculo = strtolower($nick);
    if (empty($erro) && (str_contains($nickMinusculo, 'logicfox') || str_contains($nickMinusculo, 'spinelli'))) {
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
            $erro = "Formato de imagem inválido para a foto! Use JPG, PNG ou WEBP.";
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
            $erro = "Formato de imagem inválido para o banner! Use JPG, PNG ou WEBP.";
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
    
    <link rel="icon" type="image/jpeg" href="img/logo.jpg">
    <link rel="shortcut icon" type="image/jpeg" href="img/logo.jpg">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/cada.css">
    <link rel="stylesheet" href="css/index.css?v=8">

    <style>
        /* Estilos da caixa de Termos e Modais */
        .checkbox-termos {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin-top: 10px;
            font-size: 13px;
            color: #a0a0b0;
            line-height: 1.4;
        }

        .checkbox-termos input[type="checkbox"] {
            margin-top: 3px;
            accent-color: #5846f6;
            width: 16px;
            height: 16px;
            cursor: pointer;
        }

        .checkbox-termos a {
            color: #5846f6;
            text-decoration: underline;
            cursor: pointer;
            font-weight: 500;
        }

        .checkbox-termos a:hover {
            color: #7263f8;
        }

        /* Modal Background Overlay */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0, 0, 0, 0.75);
            backdrop-filter: blur(4px);
            z-index: 9999;
            align-items: center;
            justify-content: center;
        }

        .modal-overlay.active {
            display: flex;
        }

        /* Modal Box */
        .modal-container {
            background-color: #121219;
            border: 1px solid #1f1f2e;
            border-radius: 16px;
            width: 90%;
            max-width: 520px;
            max-height: 85vh;
            padding: 24px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.6);
            display: flex;
            flex-direction: column;
            position: relative;
            animation: fadeIn 0.2s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .modal-title {
            font-size: 22px;
            font-weight: 700;
            color: #ffffff;
        }

        .btn-close-modal {
            background: none;
            border: none;
            color: #8a8a9e;
            font-size: 20px;
            cursor: pointer;
            transition: color 0.2s;
        }

        .btn-close-modal:hover {
            color: #ffffff;
        }

        /* Modal Content (Scroll Customizado) */
        .modal-body {
            overflow-y: auto;
            padding-right: 12px;
            margin-bottom: 20px;
            color: #b0b0c0;
            font-size: 13.5px;
            line-height: 1.6;
        }

        .modal-body::-webkit-scrollbar {
            width: 5px;
        }

        .modal-body::-webkit-scrollbar-track {
            background: #0d0d12;
            border-radius: 4px;
        }

        .modal-body::-webkit-scrollbar-thumb {
            background: #4f46e5;
            border-radius: 4px;
        }

        .modal-body h3 {
            color: #ffffff;
            font-size: 15px;
            font-weight: 600;
            margin-top: 16px;
            margin-bottom: 6px;
        }

        .modal-body h3:first-child {
            margin-top: 0;
        }

        .modal-body p {
            margin-bottom: 12px;
        }

        /* Botões do Modal */
        .modal-footer {
            display: flex;
            gap: 12px;
        }

        .btn-modal-primary {
            flex: 1;
            padding: 12px;
            background: #5846f6;
            color: #ffffff;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: background 0.2s;
        }

        .btn-modal-primary:hover {
            background: #4735e5;
        }

        .btn-modal-secondary {
            flex: 1;
            padding: 12px;
            background: #232330;
            color: #ffffff;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: background 0.2s;
        }

        .btn-modal-secondary:hover {
            background: #2d2d3d;
        }
    </style>
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

            <!-- Checkbox de Termos de Uso conforme imagem -->
            <div class="checkbox-termos">
                <input type="checkbox" id="aceitouTermos" name="aceitouTermos" required>
                <label for="aceitouTermos">
                    Li e concordo com os <a onclick="openModal('modalTermos')">Termos de Uso</a> e estou ciente da <a onclick="openModal('modalPrivacidade')">Política de Privacidade</a> do ReddArt.
                </label>
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

    <!-- MODAL 1: TERMOS DE USO -->
    <div id="modalTermos" class="modal-overlay">
        <div class="modal-container">
            <div class="modal-header">
                <h2 class="modal-title">Termos de Uso</h2>
                <button type="button" class="btn-close-modal" onclick="closeModal('modalTermos')">&times;</button>
            </div>
            <div class="modal-body">
                <h3>ReddArt — Termos de Uso</h3>
                <p>O ReddArt é uma plataforma desenvolvida no âmbito da ETEC Prof. Alfredo de Barros Santos, com finalidade educacional e destinada ao compartilhamento de trabalhos e manifestações artísticas.</p>

                <h3>Uso da plataforma</h3>
                <p>Ao utilizar o ReddArt, o usuário concorda em respeitar os demais usuários, as regras da plataforma e a legislação brasileira aplicável.</p>

                <h3>Conteúdo</h3>
                <p>O usuário é responsável pelos conteúdos que publicar na plataforma e deverá possuir os direitos ou autorizações necessários para utilizar imagens, vídeos, textos ou outros materiais de terceiros.</p>
                <p>Não é permitido publicar conteúdos ilegais, ofensivos, discriminatórios, ameaçadores, predatórios ou que violem direitos autorais, direitos de imagem ou outros direitos de terceiros.</p>

                <h3>Moderação</h3>
                <p>O ReddArt poderá remover conteúdos que violem suas regras e poderá aplicar medidas como advertência, suspensão ou encerramento da conta, considerando a gravidade da situação e eventuais reincidências.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal-primary" onclick="concordarTermos()">Li e concordo</button>
                <button type="button" class="btn-modal-secondary" onclick="closeModal('modalTermos')">Voltar</button>
            </div>
        </div>
    </div>

    <!-- MODAL 2: POLÍTICA DE PRIVACIDADE -->
    <div id="modalPrivacidade" class="modal-overlay">
        <div class="modal-container">
            <div class="modal-header">
                <h2 class="modal-title">Política de Privacidade</h2>
                <button type="button" class="btn-close-modal" onclick="closeModal('modalPrivacidade')">&times;</button>
            </div>
            <div class="modal-body">
                <h3>ReddArt — Proteção de Dados</h3>
                <p>O ReddArt valoriza a privacidade dos seus usuários e busca tratar os dados pessoais de acordo com a legislação brasileira aplicável, incluindo a Lei nº 13.709/2018 — Lei Geral de Proteção de Dados Pessoais (LGPD).</p>

                <h3>Dados coletados</h3>
                <p>Durante o cadastro, poderão ser solicitadas informações necessárias para o funcionamento da plataforma, como nome, nickname, endereço de e-mail, senha, descrição e imagens escolhidas pelo próprio usuário.</p>

                <h3>Finalidade</h3>
                <p>Os dados poderão ser utilizados para criação e gerenciamento da conta, autenticação, segurança, funcionamento da plataforma e comunicação relacionada ao serviço.</p>

                <h3>Senha</h3>
                <p>A senha cadastrada pelo usuário é protegida por meio de mecanismo apropriado de hash antes de ser armazenada.</p>

                <h3>Direitos do usuário</h3>
                <p>O titular dos dados poderá exercer os direitos previstos na legislação aplicável, mediante os canais fornecidos pela plataforma.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal-secondary" style="width: 100%;" onclick="closeModal('modalPrivacidade')">Fechar</button>
            </div>
        </div>
    </div>

    <!-- Script com a lógica do Node.js integrada -->
    <script src="public/script.js"></script>

    <script>
        // Funções para controle dos Modais
        function openModal(id) {
            document.getElementById(id).classList.add('active');
        }

        function closeModal(id) {
            document.getElementById(id).classList.remove('active');
        }

        function concordarTermos() {
            document.getElementById('aceitouTermos').checked = true;
            closeModal('modalTermos');
        }

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
        document.getElementById('formCadastro').addEventListener('submit', async function(e) {
            e.preventDefault();

            const erroDiv = document.getElementById('mensagem-erro');
            const btnSubmit = document.getElementById('btnSubmit');

            erroDiv.style.display = 'none';

            // Verificação do checkbox no JavaScript antes do envio
            if (!document.getElementById('aceitouTermos').checked) {
                erroDiv.textContent = "Você precisa concordar com os Termos de Uso e Política de Privacidade!";
                erroDiv.style.display = 'block';
                return;
            }

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
        document.getElementById('btnConfirmarCodigo').addEventListener('click', async function() {
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