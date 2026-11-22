<?php
/**
 * API das ações de amizade (usada pelos botões do perfil e pela página de amizades).
 * Somente POST, com verificação de sessão e token CSRF.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');
ini_set('display_errors', '0');
error_reporting(E_ALL);

function responderAmizade($codigo, $dados)
{
    http_response_code($codigo);
    echo json_encode($dados, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responderAmizade(405, ['ok' => false, 'mensagem' => 'Método não permitido.', 'estado' => null, 'pendentes' => 0]);
}

include_once __DIR__ . '/../include/amizadesFuncoes.php';

$me = amizadeSessaoId();

if ($me <= 0) {
    responderAmizade(401, ['ok' => false, 'mensagem' => 'Sessão expirada. Faça login novamente.', 'estado' => null, 'pendentes' => 0]);
}

$corpo        = file_get_contents('php://input');
$entrada      = json_decode($corpo, true);
$fonte        = is_array($entrada) ? $entrada : $_POST;
$acao         = trim((string) ($fonte['acao'] ?? ''));
$outro        = (int) ($fonte['outro'] ?? 0);
$csrf         = (string) ($fonte['csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? ''));
$conn         = amizadeConexao();

if (!$conn || $conn->connect_errno) {
    responderAmizade(500, ['ok' => false, 'mensagem' => 'Erro ao conectar ao banco de dados.', 'estado' => null, 'pendentes' => 0]);
}

if (!amizadeVerificarCsrf($csrf)) {
    responderAmizade(403, ['ok' => false, 'mensagem' => 'Token de segurança inválido. Recarregue a página.', 'estado' => null, 'pendentes' => 0, 'csrf' => amizadeTokenCsrf()]);
}

if ($acao === 'status') {
    responderAmizade(200, [
        'ok'        => true,
        'mensagem'  => '',
        'estado'    => $outro > 0 ? amizadeEstado($conn, $me, $outro) : 'nenhuma',
        'pendentes' => amizadeContarRecebidas($conn, $me),
        'csrf'      => amizadeTokenCsrf(),
    ]);
}

$resultado = amizadeExecutarAcao($conn, $me, $outro, $acao);

responderAmizade($resultado['codigo'], [
    'ok'        => $resultado['ok'],
    'mensagem'  => $resultado['mensagem'],
    'estado'    => $resultado['estado'],
    'pendentes' => amizadeContarRecebidas($conn, $me),
    'csrf'      => amizadeTokenCsrf(),
]);
