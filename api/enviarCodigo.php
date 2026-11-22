<?php
/**
 * Envia o código de verificação de e-mail (6 dígitos).
 * Substitui a rota /api/enviar-codigo que existia no servidor Node.js.
 *
 * Usa a tabela tblEmailVerificacao:
 *   idVerificacao, idUsuario, codigo (varchar(6)), expiraEm, usado, criadoEm, tentativas
 *
 * O ID e o e-mail NÃO vêm do navegador: usamos a sessão criada pelo cada.php
 * e buscamos o e-mail no banco (assim ninguém manda código para outro e-mail).
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
ini_set('display_errors', '0');
error_reporting(E_ALL);

const EMAIL_REMETENTE       = 'reddart@gmail.com';
const NOME_REMETENTE        = 'ReddArt';
const VALIDADE_MINUTOS      = 15;
const INTERVALO_REENVIO_SEG = 60;
// ============================================================

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
    responder(401, ['mensagem' => 'Sessão expirada. Faça o cadastro ou login novamente.']);
}

include_once __DIR__ . '/../setup/conexao.php';

if (!isset($conn) || $conn->connect_errno) {
    responder(500, ['mensagem' => 'Erro ao conectar ao banco de dados.']);
}

$idUsuario = (int) $_SESSION['idUsuario'];

try {
    // 1. E-mail do usuário (vem do banco, não do navegador)
    $stmt = $conn->prepare("SELECT userEmail FROM tblUsuario WHERE idUsuario = ? LIMIT 1");
    $stmt->bind_param("i", $idUsuario);
    $stmt->execute();
    $usuario = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $email = trim((string) ($usuario['userEmail'] ?? ''));

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        responder(400, ['mensagem' => 'E-mail do usuário inválido.']);
    }

    // 2. Se já existe verificação concluída, não precisa enviar de novo
    $stmt = $conn->prepare("SELECT 1 FROM tblEmailVerificacao WHERE idUsuario = ? AND usado = 1 LIMIT 1");
    $stmt->bind_param("i", $idUsuario);
    $stmt->execute();
    $jaVerificado = (bool) $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($jaVerificado) {
        responder(409, ['mensagem' => 'Este e-mail já foi verificado.']);
    }

    // 3. Limite de reenvio (evita spam de e-mails)
    $stmt = $conn->prepare(
        "SELECT TIMESTAMPDIFF(SECOND, criadoEm, NOW()) AS segundos
         FROM tblEmailVerificacao
         WHERE idUsuario = ? AND usado = 0
         ORDER BY idVerificacao DESC LIMIT 1"
    );
    $stmt->bind_param("i", $idUsuario);
    $stmt->execute();
    $ultimo = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($ultimo && (int) $ultimo['segundos'] < INTERVALO_REENVIO_SEG) {
        $falta = INTERVALO_REENVIO_SEG - (int) $ultimo['segundos'];
        responder(429, ['mensagem' => "Aguarde {$falta}s para pedir um novo código."]);
    }

    // 4. Gera o código (6 dígitos cabem em varchar(6)) e troca o pendente antigo
    $codigo  = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $minutos = (int) VALIDADE_MINUTOS;

    $stmt = $conn->prepare("DELETE FROM tblEmailVerificacao WHERE idUsuario = ? AND usado = 0");
    $stmt->bind_param("i", $idUsuario);
    $stmt->execute();
    $stmt->close();

    $stmt = $conn->prepare(
        "INSERT INTO tblEmailVerificacao (idUsuario, codigo, expiraEm, usado, criadoEm, tentativas)
         VALUES (?, ?, DATE_ADD(NOW(), INTERVAL {$minutos} MINUTE), 0, NOW(), 0)"
    );
    $stmt->bind_param("is", $idUsuario, $codigo);
    $stmt->execute();
    $idVerificacao = $stmt->insert_id;
    $stmt->close();

    // 5. Monta e envia o e-mail
    $assunto = 'Seu código de verificação - ReddArt';
    $corpo = '<div style="font-family:Arial,sans-serif;max-width:480px;margin:auto;padding:24px;">'
           . '<h2 style="margin:0 0 12px;">Verifique seu e-mail</h2>'
           . '<p>Use o código abaixo para concluir seu cadastro no ReddArt:</p>'
           . '<p style="font-size:32px;font-weight:bold;letter-spacing:8px;margin:20px 0;">' . $codigo . '</p>'
           . '<p style="color:#666;font-size:13px;">O código vale por ' . $minutos . ' minutos. '
           . 'Se você não criou uma conta, ignore este e-mail.</p>'
           . '</div>';

    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= 'From: ' . NOME_REMETENTE . ' <' . EMAIL_REMETENTE . ">\r\n";
    $headers .= 'Reply-To: ' . EMAIL_REMETENTE . "\r\n";

    $assuntoCodificado = '=?UTF-8?B?' . base64_encode($assunto) . '?=';

    $enviado = @mail($email, $assuntoCodificado, $corpo, $headers, '-f' . EMAIL_REMETENTE);

    if (!$enviado) {
        // Apaga o código para o usuário poder tentar de novo sem esperar
        $stmt = $conn->prepare("DELETE FROM tblEmailVerificacao WHERE idVerificacao = ?");
        $stmt->bind_param("i", $idVerificacao);
        $stmt->execute();
        $stmt->close();

        error_log('[ReddArt] mail() falhou para o usuário ' . $idUsuario);
        responder(500, ['mensagem' => 'Não foi possível enviar o e-mail agora. Tente novamente em instantes.']);
    }

    responder(200, ['sucesso' => true, 'mensagem' => 'Código enviado! Verifique sua caixa de entrada e o spam.']);

} catch (Throwable $e) {
    error_log('[ReddArt] enviar-codigo: ' . $e->getMessage());
    responder(500, ['mensagem' => 'Erro interno ao enviar o código.']);
}