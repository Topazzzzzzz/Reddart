<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');
ini_set('display_errors', '0');
error_reporting(E_ALL);

function responderDenuncia($codigo, $dados)
{
    http_response_code($codigo);
    echo json_encode($dados, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

include_once __DIR__ . '/../include/denunciasFuncoes.php';
include_once __DIR__ . '/../setup/conexao.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responderDenuncia(405, ['ok' => false, 'mensagem' => 'Método não permitido.']);
}

$idDelator = (int) ($_SESSION['idUsuario'] ?? 0);

if ($idDelator <= 0) {
    responderDenuncia(401, ['ok' => false, 'mensagem' => 'Entre na sua conta para denunciar uma publicação.']);
}

$entrada = json_decode(file_get_contents('php://input'), true);
$entrada = is_array($entrada) ? $entrada : $_POST;
$token = (string) ($entrada['csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? ''));

if (!hash_equals((string) ($_SESSION['denuncia_csrf'] ?? ''), $token) || $token === '') {
    responderDenuncia(403, ['ok' => false, 'mensagem' => 'Token de segurança inválido. Recarregue a página.']);
}

$idPublicacao = (int) ($entrada['idPublicacao'] ?? 0);
$idTipo = (int) ($entrada['idTipo'] ?? 0);
$tipos = denunciaTipos();

if ($idPublicacao <= 0 || !isset($tipos[$idTipo])) {
    responderDenuncia(400, ['ok' => false, 'mensagem' => 'Confira a publicação e o motivo selecionado.']);
}

$stmtPub = $conn->prepare('SELECT idUsuario FROM tblPublicacoes WHERE idPublicacao = ? LIMIT 1');
$stmtPub->bind_param('i', $idPublicacao);
$stmtPub->execute();
$publicacao = $stmtPub->get_result()->fetch_assoc();
$stmtPub->close();

if (!$publicacao) {
    responderDenuncia(404, ['ok' => false, 'mensagem' => 'Essa publicação não foi encontrada.']);
}

if ((int) $publicacao['idUsuario'] === $idDelator) {
    responderDenuncia(403, ['ok' => false, 'mensagem' => 'Você não pode denunciar sua própria publicação.']);
}

$nomeTrava = 'rd_denuncia_' . $idDelator . '_' . $idPublicacao;
$stmtTrava = $conn->prepare('SELECT GET_LOCK(?, 5) AS obtida');
$stmtTrava->bind_param('s', $nomeTrava);
$stmtTrava->execute();
$travaObtida = (int) ($stmtTrava->get_result()->fetch_assoc()['obtida'] ?? 0) === 1;
$stmtTrava->close();

if (!$travaObtida) {
    responderDenuncia(503, ['ok' => false, 'mensagem' => 'Não foi possível registrar agora. Tente novamente.']);
}

try {
    $stmtExistente = $conn->prepare('SELECT idDenuncias FROM tblDenuncias WHERE idPublicacao = ? AND idDelator = ? LIMIT 1');
    $stmtExistente->bind_param('ii', $idPublicacao, $idDelator);
    $stmtExistente->execute();
    $jaDenunciou = $stmtExistente->get_result()->num_rows > 0;
    $stmtExistente->close();

    if ($jaDenunciou) {
        $resultado = ['codigo' => 409, 'dados' => ['ok' => false, 'mensagem' => 'Você já denunciou esta publicação.']];
    } else {
        $stmtInserir = $conn->prepare('INSERT INTO tblDenuncias (idPublicacao, idDelator, idTipo) VALUES (?, ?, ?)');
        $stmtInserir->bind_param('iii', $idPublicacao, $idDelator, $idTipo);
        $ok = $stmtInserir->execute();
        $stmtInserir->close();
        $resultado = $ok
            ? ['codigo' => 200, 'dados' => ['ok' => true, 'mensagem' => 'Denúncia enviada. Obrigado por ajudar a manter a comunidade segura.']]
            : ['codigo' => 500, 'dados' => ['ok' => false, 'mensagem' => 'Não foi possível registrar a denúncia.']];
    }
} finally {
    $stmtLiberar = $conn->prepare('SELECT RELEASE_LOCK(?)');
    $stmtLiberar->bind_param('s', $nomeTrava);
    $stmtLiberar->execute();
    $stmtLiberar->close();
}

responderDenuncia($resultado['codigo'], $resultado['dados']);
