<?php
/**
 * Valida o código de 6 dígitos digitado pelo usuário.
 * Substitui a rota /api/validar-codigo que existia no servidor Node.js.
 *
 * Quando o código está certo, a linha da tblEmailVerificacao vira usado = 1.
 * "E-mail verificado" = existe uma linha com usado = 1 para o usuário.
 * O limite de tentativas erradas fica na coluna tentativas da tabela.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
ini_set('display_errors', '0');
error_reporting(E_ALL);

const MAX_TENTATIVAS = 5;

function responder($status, $dados)
{
    http_response_code($status);
    echo json_encode($dados, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responder(405, ['mensagem' => 'Método não permitido.']);
}

if (empty($_SESSION['idUsuario'])) {
    responder(401, ['mensagem' => 'Sessão expirada. Faça login novamente.']);
}

$entrada = json_decode(file_get_contents('php://input'), true);
$codigo  = preg_replace('/\D/', '', (string) ($entrada['codigo'] ?? ''));

if (strlen($codigo) !== 6) {
    responder(400, ['mensagem' => 'Digite o código de 6 dígitos.']);
}

include_once __DIR__ . '/../setup/conexao.php';

if (!isset($conn) || $conn->connect_errno) {
    responder(500, ['mensagem' => 'Erro ao conectar ao banco de dados.']);
}

$idUsuario = (int) $_SESSION['idUsuario'];

try {
    // Código pendente mais recente do usuário
    $stmt = $conn->prepare(
        "SELECT idVerificacao, codigo, tentativas, (expiraEm > NOW()) AS valido
         FROM tblEmailVerificacao
         WHERE idUsuario = ? AND usado = 0
         ORDER BY idVerificacao DESC LIMIT 1"
    );
    $stmt->bind_param("i", $idUsuario);
    $stmt->execute();
    $registro = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$registro) {
        responder(400, ['mensagem' => 'Nenhum código pendente. Peça um novo código.']);
    }

    $idVerificacao = (int) $registro['idVerificacao'];

    if ((int) $registro['tentativas'] >= MAX_TENTATIVAS) {
        responder(429, ['mensagem' => 'Muitas tentativas. Peça um novo código.']);
    }

    if (!(int) $registro['valido']) {
        responder(400, ['mensagem' => 'Código expirado. Peça um novo código.']);
    }

    if (!hash_equals((string) $registro['codigo'], $codigo)) {
        $stmt = $conn->prepare("UPDATE tblEmailVerificacao SET tentativas = tentativas + 1 WHERE idVerificacao = ?");
        $stmt->bind_param("i", $idVerificacao);
        $stmt->execute();
        $stmt->close();

        responder(400, ['mensagem' => 'Código incorreto.']);
    }

    // Código correto: marca como usado (= e-mail verificado)
    $stmt = $conn->prepare("UPDATE tblEmailVerificacao SET usado = 1 WHERE idVerificacao = ?");
    $stmt->bind_param("i", $idVerificacao);
    $stmt->execute();
    $stmt->close();

    responder(200, ['sucesso' => true, 'mensagem' => 'E-mail verificado com sucesso!']);

} catch (Throwable $e) {
    error_log('[ReddArt] validar-codigo: ' . $e->getMessage());
    responder(500, ['mensagem' => 'Erro interno ao validar o código.']);
}