<?php
session_start();
include "setup/conexao.php";

// Sem isso, não dá pra saber de quem é a publicação (nem sincronizar no perfil)
if (!isset($_SESSION['idUsuario'])) {
    header("Location: login.php");
    exit;
}

$idUsuarioLogado = $_SESSION['idUsuario'];

// Página de resultado personalizada (substitui o alert nativo do navegador)
function mostrarResultado($tipo, $titulo, $mensagem, $destino, $textoBotao, $autoSegundos = 0) {
    $sucesso = ($tipo === 'sucesso');
    $icone = $sucesso ? 'fa-solid fa-circle-check' : 'fa-solid fa-circle-xmark';
    $destinoAttr = htmlspecialchars($destino, ENT_QUOTES, 'UTF-8');
    $auto = ($sucesso && $autoSegundos > 0)
        ? '<div class="res-progress"><span style="animation-duration:' . intval($autoSegundos) . 's"></span></div>'
          . '<script>setTimeout(function(){ window.location.href=' . json_encode($destino) . '; }, ' . (intval($autoSegundos) * 1000) . ');</script>'
        : '';
    echo '<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>' . htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8') . ' - ReddArt</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
@import url(\'https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap\');
* { margin:0; padding:0; box-sizing:border-box; font-family:\'Poppins\',sans-serif; }
body { min-height:100vh; display:flex; align-items:center; justify-content:center; padding:20px;
background:linear-gradient(135deg,#000000,#0c0c11,#111113); color:#fff; }
.res-card { width:100%; max-width:420px; background:#121218; border:1px solid rgba(255,255,255,.08);
border-radius:16px; padding:36px 30px 30px; text-align:center; box-shadow:0 20px 60px rgba(0,0,0,.5);
animation:resIn .25s ease; }
@keyframes resIn { from { opacity:0; transform:translateY(14px) scale(.98); } to { opacity:1; transform:none; } }
.res-icon { width:72px; height:72px; border-radius:50%; margin:0 auto 18px; display:flex;
align-items:center; justify-content:center; font-size:34px; }
.res-icon.ok { background:rgba(37,99,235,.15); color:#60a5fa; border:1px solid rgba(59,130,246,.4); }
.res-icon.erro { background:rgba(225,29,72,.12); color:#fb7185; border:1px solid rgba(225,29,72,.4); }
.res-card h2 { font-size:20px; font-weight:600; margin-bottom:8px; }
.res-card p { color:#8a8a9e; font-size:13.5px; line-height:1.55; margin-bottom:22px; overflow-wrap:break-word; }
.res-btn { display:inline-flex; align-items:center; justify-content:center; gap:8px; width:100%;
padding:12px; border-radius:10px; font-size:14px; font-weight:600; text-decoration:none; cursor:pointer; border:none; }
.res-btn.primary { background:#2563eb; color:#fff; }
.res-btn.primary:hover { filter:brightness(1.15); }
.res-btn.ghost { background:#27272a; color:#fff; margin-top:10px; }
.res-btn.ghost:hover { background:#3f3f46; }
.res-progress { height:4px; background:#27272a; border-radius:4px; overflow:hidden; margin-top:18px; }
.res-progress span { display:block; height:100%; width:0; background:#2563eb; border-radius:4px;
animation-name:resBar; animation-timing-function:linear; animation-fill-mode:forwards; }
@keyframes resBar { to { width:100%; } }
</style>
</head>
<body>
<div class="res-card">
<div class="res-icon ' . ($sucesso ? 'ok' : 'erro') . '"><i class="' . $icone . '"></i></div>
<h2>' . htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8') . '</h2>
<p>' . htmlspecialchars($mensagem, ENT_QUOTES, 'UTF-8') . '</p>
<a class="res-btn primary" href="' . $destinoAttr . '">' . htmlspecialchars($textoBotao, ENT_QUOTES, 'UTF-8') . '</a>'
. ($sucesso
    ? ''
    : '<button class="res-btn ghost" onclick="window.history.back()">Voltar e tentar de novo</button>')
. $auto . '
</div>
</body>
</html>';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $titulo = $_POST['titulo'] ?? '';
    // Recebe o ID do gênero selecionado no formulário
    $idGenero = isset($_POST['categoria']) ? intval($_POST['categoria']) : 1;

    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] == 0) {
        $nomeArquivo = $_FILES['imagem']['name'];
        $arquivoTemp = $_FILES['imagem']['tmp_name'];

        $pastaDestino = "img/uploads/";

        if (!file_exists($pastaDestino)) {
            mkdir($pastaDestino, 0777, true);
        }

        $novoNome = uniqid() . "." . pathinfo($nomeArquivo, PATHINFO_EXTENSION);
        $caminhoFinal = $pastaDestino . $novoNome;

        if (move_uploaded_file($arquivoTemp, $caminhoFinal)) {
            // Insere na tabela vinculando o idGenero selecionado
            $stmt = $conn->prepare("INSERT INTO tblPublicacoes (idUsuario, pubHora, pubLink, pubLegenda, idGenero) VALUES (?, NOW(), ?, ?, ?)");

            $stmt->bind_param("issi", $idUsuarioLogado, $caminhoFinal, $titulo, $idGenero);

            if ($stmt->execute()) {
                $stmt->close();
                $conn->close();
                mostrarResultado('sucesso', 'Imagem publicada!', 'Sua arte já está no ar e visível para todos.', 'index.php', 'Ver no início', 3);
            } else {
                $erro = $stmt->error;
                $stmt->close();
                mostrarResultado('erro', 'Não foi possível publicar', 'Erro ao salvar no banco: ' . $erro, 'index.php', 'Voltar ao início');
            }
        } else {
            mostrarResultado('erro', 'Não foi possível publicar', 'Erro ao mover o arquivo de imagem. Tente novamente.', 'index.php', 'Voltar ao início');
        }
    } else {
        mostrarResultado('erro', 'Nenhuma imagem selecionada', 'Escolha um arquivo de imagem antes de publicar.', 'adicionar.php', 'Escolher imagem');
    }
}
?>
