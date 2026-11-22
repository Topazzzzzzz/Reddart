<?php
/**
 * Envia o código de verificação de e-mail (6 dígitos) por SMTP (PHPMailer).
 * Substitui a rota /api/enviar-codigo que existia no servidor Node.js.
 *
 * Usa a tabela tblEmailVerificacao:
 *   idVerificacao, idUsuario, codigo (varchar(6)), expiraEm, usado, criadoEm, tentativas
 *
 * O ID e o e-mail NÃO vêm do navegador: usamos a sessão criada pelo cada.php
 * e buscamos o e-mail no banco (assim ninguém manda código para outro e-mail).
 *
 * Precisa de:
 *   api/configEmail.php
 *   api/PHPMailer/PHPMailer.php, SMTP.php e Exception.php
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as MailException;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
ini_set('display_errors', '0');
error_reporting(E_ALL);

const VALIDADE_MINUTOS      = 15;
const INTERVALO_REENVIO_SEG = 60;

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

// ---- Configuração de e-mail e biblioteca ----
$arquivoConfig = __DIR__ . '/configEmail.php';
$pastaMailer   = __DIR__ . '/PHPMailer';

if (!is_file($arquivoConfig)) {
    error_log('[ReddArt] api/configEmail.php não encontrado.');
    responder(500, ['mensagem' => 'Configuração de e-mail ausente (api/configEmail.php).']);
}

foreach (['PHPMailer.php', 'SMTP.php', 'Exception.php'] as $arq) {
    if (!is_file($pastaMailer . '/' . $arq)) {
        error_log('[ReddArt] PHPMailer ausente: ' . $arq);
        responder(500, ['mensagem' => 'Biblioteca PHPMailer não encontrada em api/PHPMailer/ (falta ' . $arq . ').']);
    }
}

$cfg = require $arquivoConfig;

require_once $pastaMailer . '/Exception.php';
require_once $pastaMailer . '/PHPMailer.php';
require_once $pastaMailer . '/SMTP.php';

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

    // 5. Monta e envia o e-mail por SMTP
    $corpo = '<div style="font-family:Arial,sans-serif;max-width:480px;margin:auto;padding:24px;">'
           . '<h2 style="margin:0 0 12px;">Verifique seu e-mail</h2>'
           . '<p>Use o código abaixo para concluir seu cadastro no ReddArt:</p>'
           . '<p style="font-size:32px;font-weight:bold;letter-spacing:8px;margin:20px 0;">' . $codigo . '</p>'
           . '<p style="color:#666;font-size:13px;">O código vale por ' . $minutos . ' minutos. '
           . 'Se você não criou uma conta, ignore este e-mail.</p>'
           . '</div>';

    $erroEnvio = '';

    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = $cfg['host'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $cfg['usuario'];
        $mail->Password   = $cfg['senha'];
        $mail->SMTPSecure = ($cfg['seguranca'] === 'ssl')
            ? PHPMailer::ENCRYPTION_SMTPS
            : PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = (int) $cfg['porta'];
        $mail->CharSet    = 'UTF-8';
        $mail->Timeout    = 15;

        $mail->setFrom($cfg['remetente_email'], $cfg['remetente_nome']);
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Seu código de verificação - ReddArt';
        $mail->Body    = $corpo;
        $mail->AltBody = "Seu código de verificação do ReddArt: {$codigo}. Ele vale por {$minutos} minutos.";

        $mail->send();
    } catch (MailException $e) {
        $erroEnvio = isset($mail) ? $mail->ErrorInfo : $e->getMessage();
    } catch (Throwable $e) {
        $erroEnvio = $e->getMessage();
    }

    if ($erroEnvio !== '') {
        // Apaga o código para o usuário poder tentar de novo sem esperar
        $stmt = $conn->prepare("DELETE FROM tblEmailVerificacao WHERE idVerificacao = ?");
        $stmt->bind_param("i", $idVerificacao);
        $stmt->execute();
        $stmt->close();

        error_log('[ReddArt] SMTP falhou (usuário ' . $idUsuario . '): ' . $erroEnvio);

        $msg = 'Não foi possível enviar o e-mail agora. Tente novamente em instantes.';
        if (!empty($cfg['debug'])) {
            $msg .= ' [Detalhe: ' . $erroEnvio . ']';
        }
        responder(500, ['mensagem' => $msg]);
    }

    responder(200, ['sucesso' => true, 'mensagem' => 'Código enviado! Verifique sua caixa de entrada e o spam.']);

} catch (Throwable $e) {
    error_log('[ReddArt] enviar-codigo: ' . $e->getMessage());
    responder(500, ['mensagem' => 'Erro interno ao enviar o código.']);
}