<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');
ini_set('display_errors', '0');
error_reporting(E_ALL);

function responderNotificacoes($codigo, $dados)
{
    http_response_code($codigo);
    echo json_encode($dados, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

include_once __DIR__ . '/../include/notificacoesFuncoes.php';
include_once __DIR__ . '/../setup/conexao.php';

$idUsuario = (int) ($_SESSION['idUsuario'] ?? 0);

if ($idUsuario <= 0) {
    responderNotificacoes(401, ['ok' => false, 'mensagem' => 'Sessão expirada.']);
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['resumo'])) {
        responderNotificacoes(200, [
            'ok' => true,
            'naoLidas' => notificacoesContarNaoLidas($conn, $idUsuario),
        ]);
    }

    $stmt = $conn->prepare("SELECT n.idNotificacao, n.idAtor, n.notTipo, n.notTitulo, n.notMensagem, n.idPublicacao, n.notLida, n.notHora, u.userNome AS atorNome, p.pubLegenda
                            FROM tblNotificacoes n
                            LEFT JOIN tblUsuario u ON u.idUsuario = n.idAtor
                            LEFT JOIN tblPublicacoes p ON p.idPublicacao = n.idPublicacao
                            WHERE n.idUsuario = ?
                            ORDER BY n.notHora DESC, n.idNotificacao DESC
                            LIMIT 30");

    if (!$stmt) {
        responderNotificacoes(500, ['ok' => false, 'mensagem' => 'Não foi possível carregar as notificações.']);
    }

    $stmt->bind_param('i', $idUsuario);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $itens = [];

    while ($resultado && ($linha = $resultado->fetch_assoc())) {
        $tipo = (string) $linha['notTipo'];
        $ator = trim((string) ($linha['atorNome'] ?? '')) ?: 'Alguém';
        $titulo = trim((string) ($linha['notTitulo'] ?? ''));
        $mensagem = trim((string) ($linha['notMensagem'] ?? ''));
        $url = 'notificacoes.php';

        if ($tipo === 'visualizacao') {
            $titulo = $titulo !== '' ? $titulo : 'Sua arte foi visualizada';
            $mensagem = $mensagem !== '' ? $mensagem : ($ator . ' abriu sua arte “' . (trim((string) ($linha['pubLegenda'] ?? '')) ?: 'Sem título') . '”.');
            $url = 'perfil.php?id=' . (int) $linha['idAtor'];
        } elseif ($tipo === 'amizade_solicitacao') {
            $titulo = $titulo !== '' ? $titulo : 'Nova solicitação de amizade';
            $mensagem = $mensagem !== '' ? $mensagem : ($ator . ' enviou uma solicitação de amizade.');
            $url = 'amizades.php?aba=recebidas';
        } elseif ($tipo === 'amizade_aceita') {
            $titulo = $titulo !== '' ? $titulo : 'Solicitação aceita';
            $mensagem = $mensagem !== '' ? $mensagem : ($ator . ' aceitou sua solicitação de amizade.');
            $url = 'amizades.php?aba=amigos';
        } elseif ($tipo === 'seguidor') {
            $titulo = $titulo !== '' ? $titulo : 'Novo seguidor';
            $mensagem = $mensagem !== '' ? $mensagem : ($ator . ' começou a seguir você.');
            $url = 'perfil.php?id=' . (int) $linha['idAtor'];
        } elseif ($titulo === '') {
            $titulo = 'Notificação';
        }

        $itens[] = [
            'id' => (int) $linha['idNotificacao'],
            'tipo' => $tipo,
            'titulo' => $titulo,
            'mensagem' => $mensagem,
            'ator' => $ator,
            'atorId' => (int) $linha['idAtor'],
            'lida' => (int) $linha['notLida'] === 1,
            'hora' => (string) $linha['notHora'],
            'url' => $url,
        ];
    }

    $stmt->close();
    responderNotificacoes(200, [
        'ok' => true,
        'itens' => $itens,
        'naoLidas' => notificacoesContarNaoLidas($conn, $idUsuario),
        'csrf' => notificacoesToken(),
    ]);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responderNotificacoes(405, ['ok' => false, 'mensagem' => 'Método não permitido.']);
}

$entrada = json_decode(file_get_contents('php://input'), true);
$entrada = is_array($entrada) ? $entrada : $_POST;

if (!notificacoesValidarToken((string) ($entrada['csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '')))) {
    responderNotificacoes(403, ['ok' => false, 'mensagem' => 'Token de segurança inválido.']);
}

$acao = (string) ($entrada['acao'] ?? '');

if ($acao === 'visualizacao') {
    $idPublicacao = (int) ($entrada['idPublicacao'] ?? 0);

    if ($idPublicacao <= 0) {
        responderNotificacoes(400, ['ok' => false, 'mensagem' => 'Publicação inválida.']);
    }

    $stmt = $conn->prepare('SELECT idUsuario, pubLegenda FROM tblPublicacoes WHERE idPublicacao = ? LIMIT 1');
    $stmt->bind_param('i', $idPublicacao);
    $stmt->execute();
    $publicacao = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$publicacao || (int) $publicacao['idUsuario'] <= 0 || (int) $publicacao['idUsuario'] === $idUsuario) {
        responderNotificacoes(200, ['ok' => true, 'ignorada' => true]);
    }

    $idAutor = (int) $publicacao['idUsuario'];
    $stmt = $conn->prepare("SELECT idNotificacao FROM tblNotificacoes WHERE idUsuario = ? AND idAtor = ? AND notTipo = 'visualizacao' AND idPublicacao = ? LIMIT 1");
    $stmt->bind_param('iii', $idAutor, $idUsuario, $idPublicacao);
    $stmt->execute();
    $jaNotificado = $stmt->get_result()->num_rows > 0;
    $stmt->close();

    if (!$jaNotificado) {
        $titulo = 'Sua arte foi visualizada';
        $legenda = trim((string) ($publicacao['pubLegenda'] ?? '')) ?: 'Sem título';
        $stmtNome = $conn->prepare('SELECT userNome FROM tblUsuario WHERE idUsuario = ? LIMIT 1');
        $stmtNome->bind_param('i', $idUsuario);
        $stmtNome->execute();
        $nomeAtor = trim((string) ($stmtNome->get_result()->fetch_assoc()['userNome'] ?? '')) ?: 'Um usuário';
        $stmtNome->close();
        $mensagem = $nomeAtor . ' abriu sua arte “' . $legenda . '”.';
        $tipo = 'visualizacao';
        $notificacao = $conn->prepare('INSERT INTO tblNotificacoes (idUsuario, idAtor, notTipo, notTitulo, notMensagem, idPublicacao, notLida, notHora) VALUES (?, ?, ?, ?, ?, ?, 0, NOW())');
        $notificacao->bind_param('iisssi', $idAutor, $idUsuario, $tipo, $titulo, $mensagem, $idPublicacao);
        $notificacao->execute();
        $notificacao->close();
    }

    responderNotificacoes(200, ['ok' => true]);
}

if ($acao === 'marcar_lida') {
    $idNotificacao = (int) ($entrada['id'] ?? 0);
    $stmt = $conn->prepare('UPDATE tblNotificacoes SET notLida = 1 WHERE idNotificacao = ? AND idUsuario = ?');
    $stmt->bind_param('ii', $idNotificacao, $idUsuario);
    $ok = $stmt->execute();
    $stmt->close();
} elseif ($acao === 'marcar_todas') {
    $stmt = $conn->prepare('UPDATE tblNotificacoes SET notLida = 1 WHERE idUsuario = ? AND notLida = 0');
    $stmt->bind_param('i', $idUsuario);
    $ok = $stmt->execute();
    $stmt->close();
} else {
    responderNotificacoes(400, ['ok' => false, 'mensagem' => 'Ação inválida.']);
}

responderNotificacoes(200, [
    'ok' => (bool) $ok,
    'naoLidas' => notificacoesContarNaoLidas($conn, $idUsuario),
]);
