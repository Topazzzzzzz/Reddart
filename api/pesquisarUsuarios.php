<?php
/**
 * Pesquisa de usuários por nome de exibição (userNome) e username (userNick).
 * Retorna apenas dados públicos: id, nome, username e foto.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');
ini_set('display_errors', '0');
error_reporting(E_ALL);

function responderBusca($status, $dados)
{
    http_response_code($status);
    echo json_encode($dados, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    responderBusca(405, ['resultados' => [], 'mensagem' => 'Método não permitido.']);
}

if (empty($_SESSION['idUsuario'])) {
    responderBusca(401, ['resultados' => [], 'mensagem' => 'Sessão expirada. Faça login novamente.']);
}

include_once __DIR__ . '/../setup/conexao.php';

if (!isset($conn) || $conn->connect_errno) {
    responderBusca(500, ['resultados' => [], 'mensagem' => 'Erro ao conectar ao banco de dados.']);
}

$termo = trim((string) ($_GET['q'] ?? ''));

if (function_exists('mb_substr')) {
    $termo = mb_substr($termo, 0, 60);
} else {
    $termo = substr($termo, 0, 60);
}

$tamanhoTermo = function_exists('mb_strlen') ? mb_strlen($termo) : strlen($termo);

if ($tamanhoTermo < 2) {
    responderBusca(200, ['resultados' => []]);
}

$termoEscapado = str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $termo);
$termoLike     = '%' . $termoEscapado . '%';
$termoPrefixo  = $termoEscapado . '%';

try {
    $sql = "SELECT idUsuario, userNome, userNick, userFoto
            FROM tblUsuario
            WHERE userNome LIKE ? ESCAPE '!' OR userNick LIKE ? ESCAPE '!'
            ORDER BY (userNome = ? OR userNick = ?) DESC,
                     (userNome LIKE ? ESCAPE '!' OR userNick LIKE ? ESCAPE '!') DESC,
                     userNome ASC
            LIMIT 8";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        responderBusca(500, ['resultados' => [], 'mensagem' => 'Não foi possível concluir a pesquisa.']);
    }

    $stmt->bind_param('ssssss', $termoLike, $termoLike, $termo, $termo, $termoPrefixo, $termoPrefixo);
    $stmt->execute();
    $resultado = $stmt->get_result();

    $usuarios = [];

    while ($resultado && ($linha = $resultado->fetch_assoc())) {
        $nome = trim((string) ($linha['userNome'] ?? ''));
        $nick = trim((string) ($linha['userNick'] ?? ''));
        $foto = trim((string) ($linha['userFoto'] ?? ''));

        $usuarios[] = [
            'id'   => (int) $linha['idUsuario'],
            'nome' => $nome !== '' ? $nome : 'Artista',
            'nick' => $nick,
            'foto' => $foto !== '' ? $foto : 'uploads/usuNovoPerfil.jpg',
        ];
    }

    $stmt->close();

    responderBusca(200, ['resultados' => $usuarios]);

} catch (Throwable $e) {
    error_log('[ReddArt] pesquisar-usuarios: ' . $e->getMessage());
    responderBusca(500, ['resultados' => [], 'mensagem' => 'Erro interno ao pesquisar usuários.']);
}
