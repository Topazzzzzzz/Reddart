<?php
session_start();
require_once '../setup/conexao.php';
require_once '../include/notificacoesFuncoes.php';

if ((int) ($_SESSION['usuario_cargo'] ?? 0) !== 2) {
    header('Location: home.php');
    exit;
}

$idAdmin = (int) ($_SESSION['usuario_id'] ?? $_SESSION['idUsuario'] ?? 0);
$mensagemPagina = '';
$tipoMensagem = 'erro';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = (string) ($_POST['csrf'] ?? '');
    $titulo = trim((string) ($_POST['titulo'] ?? ''));
    $mensagem = trim((string) ($_POST['mensagem'] ?? ''));
    $destinatario = (string) ($_POST['destinatario'] ?? '');

    if (!notificacoesValidarToken($token)) {
        $mensagemPagina = 'Token de segurança inválido. Atualize a página e tente novamente.';
    } elseif ($idAdmin <= 0) {
        $mensagemPagina = 'Não foi possível identificar a conta administradora.';
    } elseif ($titulo === '' || $mensagem === '') {
        $mensagemPagina = 'Preencha o título e a mensagem.';
    } elseif (mb_strlen($titulo, 'UTF-8') > 120) {
        $mensagemPagina = 'O título deve ter no máximo 120 caracteres.';
    } elseif (mb_strlen($mensagem, 'UTF-8') > 5000) {
        $mensagemPagina = 'A mensagem deve ter no máximo 5000 caracteres.';
    } elseif ($destinatario !== 'todos' && (int) $destinatario <= 0) {
        $mensagemPagina = 'Escolha um destinatário válido.';
    } else {
        $destinatarios = [];

        if ($destinatario === 'todos') {
            $stmtUsuarios = $conn->prepare('SELECT idUsuario FROM tblUsuario WHERE idUsuario <> ? ORDER BY userNome ASC');
            $stmtUsuarios->bind_param('i', $idAdmin);
            $stmtUsuarios->execute();
            $resultadoUsuarios = $stmtUsuarios->get_result();
            while ($resultadoUsuarios && ($linha = $resultadoUsuarios->fetch_assoc())) {
                $destinatarios[] = (int) $linha['idUsuario'];
            }
            $stmtUsuarios->close();
        } else {
            $idDestinatario = (int) $destinatario;
            $stmtUsuario = $conn->prepare('SELECT idUsuario FROM tblUsuario WHERE idUsuario = ? LIMIT 1');
            $stmtUsuario->bind_param('i', $idDestinatario);
            $stmtUsuario->execute();
            $existe = $stmtUsuario->get_result()->num_rows > 0;
            $stmtUsuario->close();

            if ($existe && $idDestinatario !== $idAdmin) {
                $destinatarios[] = $idDestinatario;
            }
        }

        if (!$destinatarios) {
            $mensagemPagina = 'Não há destinatários válidos para esse aviso.';
        } else {
            $conn->begin_transaction();
            $ok = true;

            foreach ($destinatarios as $idDestinatario) {
                if (!notificacoesCriar($conn, $idDestinatario, $idAdmin, 'aviso_admin', $titulo, $mensagem)) {
                    $ok = false;
                    break;
                }
            }

            if ($ok) {
                $conn->commit();
                $mensagemPagina = 'Aviso enviado para ' . count($destinatarios) . (count($destinatarios) === 1 ? ' usuário.' : ' usuários.');
                $tipoMensagem = 'ok';
            } else {
                $conn->rollback();
                $mensagemPagina = 'Não foi possível enviar o aviso. Nenhum destinatário foi alterado.';
            }
        }
    }
}

$usuarios = [];
$stmtLista = $conn->prepare('SELECT idUsuario, userNome, userNick FROM tblUsuario ORDER BY userNome ASC');
$stmtLista->execute();
$resultadoLista = $stmtLista->get_result();
while ($resultadoLista && ($linha = $resultadoLista->fetch_assoc())) {
    if ((int) $linha['idUsuario'] !== $idAdmin) {
        $usuarios[] = $linha;
    }
}
$stmtLista->close();

function rdAvisoHtml($valor)
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enviar aviso | ReddArt</title>
    <link rel="stylesheet" href="../css/home.css">
    <link rel="stylesheet" href="../css/notificacoes.css?v=1">
    <link rel="stylesheet" href="../css/responsivo.css?v=1">
    <style>
        .admin-card--aviso { max-width: 620px; text-align: left; }
        .admin-card--aviso .badge-admin, .admin-card--aviso h2, .admin-card--aviso .subtitulo { display: block; text-align: center; }
        .aviso-form { display: grid; gap: 12px; }
        .aviso-form label { color: #d4d4d8; font-size: .88rem; font-weight: 600; }
        .aviso-form input, .aviso-form select, .aviso-form textarea { width: 100%; padding: 12px; border: 1px solid #29293b; border-radius: 9px; background: #0c0c13; color: #fff; font: inherit; }
        .aviso-form textarea { min-height: 150px; resize: vertical; }
        .aviso-form button { width: 100%; padding: 13px; border: 0; border-radius: 9px; background: #5542f6; color: #fff; font-weight: 700; cursor: pointer; }
        .aviso-form button:hover { background: #4332d1; }
        .aviso-retorno { padding: 11px 14px; border-radius: 9px; margin-bottom: 16px; text-align: center; }
        .aviso-retorno--ok { color: #86efac; background: rgba(34,197,94,.12); }
        .aviso-retorno--erro { color: #fca5a5; background: rgba(239,68,68,.12); }
        .admin-card--aviso .btn-logout { margin-top: 16px; }
    </style>
</head>
<body>
    <h1 class="logo">REDDART</h1>
    <main class="admin-card admin-card--aviso">
        <span class="badge-admin">PAINEL ADMINISTRATIVO</span>
        <h2>Enviar aviso</h2>
        <p class="subtitulo">O aviso aparecerá no sino dos destinatários.</p>

        <?php if ($mensagemPagina !== ''): ?>
            <div class="aviso-retorno aviso-retorno--<?php echo $tipoMensagem === 'ok' ? 'ok' : 'erro'; ?>" role="status"><?php echo rdAvisoHtml($mensagemPagina); ?></div>
        <?php endif; ?>

        <form class="aviso-form" method="post" action="enviarAviso.php">
            <input type="hidden" name="csrf" value="<?php echo rdAvisoHtml(notificacoesToken()); ?>">
            <label for="destinatario">Destinatário</label>
            <select name="destinatario" id="destinatario" required>
                <option value="">Selecione um destinatário</option>
                <option value="todos">Todos os usuários</option>
                <?php foreach ($usuarios as $usuario): ?>
                    <option value="<?php echo (int) $usuario['idUsuario']; ?>"><?php echo rdAvisoHtml($usuario['userNome'] ?: 'Artista'); ?><?php echo !empty($usuario['userNick']) ? ' (@' . rdAvisoHtml($usuario['userNick']) . ')' : ''; ?></option>
                <?php endforeach; ?>
            </select>
            <label for="titulo">Título</label>
            <input type="text" name="titulo" id="titulo" maxlength="120" required>
            <label for="mensagem">Mensagem</label>
            <textarea name="mensagem" id="mensagem" maxlength="5000" required></textarea>
            <button type="submit">Enviar aviso</button>
        </form>

        <a href="itensSite.php" class="btn-logout">Voltar ao painel</a>
    </main>
</body>
</html>
