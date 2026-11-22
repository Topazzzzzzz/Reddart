<?php
/**
 * Funções compartilhadas do sistema de amizades.
 * Não gera saída: apenas consulta e altera a tabela `tblAmizades`.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once __DIR__ . '/notificacoesFuncoes.php';

function amizadeNotificacaoSolicitacao($conn, $destinatario, $remetente)
{
    return notificacoesCriar(
        $conn,
        $destinatario,
        $remetente,
        'amizade_solicitacao',
        'Nova solicitação de amizade',
        'Você recebeu uma solicitação de amizade.'
    );
}

function amizadeNotificacaoMarcarLida($conn, $destinatario, $remetente)
{
    $stmt = $conn->prepare("UPDATE tblNotificacoes SET notLida = 1 WHERE idUsuario = ? AND idAtor = ? AND notTipo = 'amizade_solicitacao' AND notLida = 0");

    if (!$stmt) {
        return false;
    }

    $stmt->bind_param('ii', $destinatario, $remetente);
    $ok = $stmt->execute();
    $stmt->close();

    return $ok;
}

function amizadeConexao()
{
    global $conn;

    if ((!isset($conn) || $conn->connect_errno) && is_file(__DIR__ . '/../setup/conexao.php')) {
        include_once __DIR__ . '/../setup/conexao.php';
    }

    return isset($conn) ? $conn : null;
}

function amizadeSessaoId()
{
    return (int) ($_SESSION['idUsuario'] ?? 0);
}

function amizadeTokenCsrf()
{
    if (empty($_SESSION['amizades_csrf']) || !is_string($_SESSION['amizades_csrf'])) {
        $_SESSION['amizades_csrf'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['amizades_csrf'];
}

function amizadeVerificarCsrf($token)
{
    $esperado = $_SESSION['amizades_csrf'] ?? '';

    return is_string($token) && $token !== ''
        && is_string($esperado) && $esperado !== ''
        && hash_equals($esperado, $token);
}

function amizadeLinha($conn, $me, $outro, $forUpdate = false)
{
    if (!$conn || $me <= 0 || $outro <= 0 || $me === $outro) {
        return null;
    }

    $sql = "SELECT idAmizade, idSolicitante, idDestinatario, amiStatus
            FROM tblAmizades
            WHERE idUsuarioMenor = LEAST(?, ?) AND idUsuarioMaior = GREATEST(?, ?)";

    if ($forUpdate) {
        $sql .= " FOR UPDATE";
    }

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        return null;
    }

    $stmt->bind_param('iiii', $me, $outro, $me, $outro);
    $stmt->execute();
    $res = $stmt->get_result();
    $linha = $res ? $res->fetch_assoc() : null;
    $stmt->close();

    return $linha ?: null;
}

function amizadeEstadoLinha($linha, $me)
{
    if (!$linha) {
        return 'nenhuma';
    }

    if ($linha['amiStatus'] === 'aceita') {
        return 'amigos';
    }

    if ($linha['amiStatus'] === 'pendente') {
        return ((int) $linha['idSolicitante'] === (int) $me) ? 'enviada' : 'recebida';
    }

    return 'nenhuma';
}

function amizadeEstado($conn, $me, $outro)
{
    return amizadeEstadoLinha(amizadeLinha($conn, $me, $outro, false), $me);
}

function amizadeContarRecebidas($conn, $me)
{
    if (!$conn || $me <= 0) {
        return 0;
    }

    $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM tblAmizades WHERE idDestinatario = ? AND amiStatus = 'pendente'");

    if (!$stmt) {
        return 0;
    }

    $stmt->bind_param('i', $me);
    $stmt->execute();
    $res = $stmt->get_result();
    $total = $res ? (int) ($res->fetch_assoc()['total'] ?? 0) : 0;
    $stmt->close();

    return $total;
}

function amizadeUsuarioExiste($conn, $id)
{
    if (!$conn || $id <= 0) {
        return false;
    }

    $stmt = $conn->prepare("SELECT idUsuario FROM tblUsuario WHERE idUsuario = ? LIMIT 1");

    if (!$stmt) {
        return false;
    }

    $stmt->bind_param('i', $id);
    $stmt->execute();
    $res = $stmt->get_result();
    $existe = $res && $res->num_rows > 0;
    $stmt->close();

    return $existe;
}

function amizadeExecutarAcao($conn, $me, $outro, $acao)
{
    $resposta = ['ok' => false, 'mensagem' => 'Ação inválida.', 'estado' => null, 'codigo' => 400];

    if (!$conn) {
        $resposta['mensagem'] = 'Erro ao conectar ao banco de dados.';
        $resposta['codigo'] = 500;
        return $resposta;
    }

    if ($me <= 0) {
        $resposta['mensagem'] = 'Sessão expirada. Faça login novamente.';
        $resposta['codigo'] = 401;
        return $resposta;
    }

    if ($outro <= 0) {
        $resposta['mensagem'] = 'Usuário não informado.';
        return $resposta;
    }

    if ($me === $outro) {
        $resposta['mensagem'] = 'Você não pode enviar uma solicitação para si mesmo.';
        return $resposta;
    }

    $acao = (string) $acao;

    if (!in_array($acao, ['enviar', 'cancelar', 'aceitar', 'recusar', 'desfazer'], true)) {
        $resposta['mensagem'] = 'Ação não permitida.';
        return $resposta;
    }

    if (!amizadeUsuarioExiste($conn, $outro)) {
        $resposta['mensagem'] = 'Usuário não encontrado.';
        $resposta['codigo'] = 404;
        return $resposta;
    }

    $sucesso  = false;
    $mensagem = '';
    $estado   = 'nenhuma';

    $conn->begin_transaction();

    try {
        $linha = amizadeLinha($conn, $me, $outro, true);

        switch ($acao) {

            case 'enviar':
                if ($linha && $linha['amiStatus'] === 'aceita') {
                    $mensagem = 'Vocês já são amigos.';
                    $estado   = 'amigos';
                    break;
                }

                if ($linha && $linha['amiStatus'] === 'pendente') {
                    $estado = amizadeEstadoLinha($linha, $me);
                    $mensagem = ($estado === 'enviada')
                        ? 'Você já enviou uma solicitação para este usuário.'
                        : 'Este usuário já enviou uma solicitação para você. Aceite na área de solicitações.';
                    break;
                }

                if ($linha) {
                    $stmt = $conn->prepare("UPDATE tblAmizades
                                            SET idSolicitante = ?, idDestinatario = ?, amiStatus = 'pendente', amiCriadaEm = CURRENT_TIMESTAMP
                                            WHERE idAmizade = ?");
                    $stmt->bind_param('iii', $me, $outro, $linha['idAmizade']);

                    if (!$stmt->execute()) {
                        throw new RuntimeException('update solicitacao');
                    }

                    $stmt->close();
                } else {
                    $stmt = $conn->prepare("INSERT INTO tblAmizades (idSolicitante, idDestinatario, amiStatus) VALUES (?, ?, 'pendente')");
                    $stmt->bind_param('ii', $me, $outro);

                    if (!$stmt->execute()) {
                        $stmt->close();
                        $conn->rollback();

                        if ($conn->errno === 1062) {
                            $linhaConcorrente = amizadeLinha($conn, $me, $outro, false);
                            $estadoConcorrente = amizadeEstadoLinha($linhaConcorrente, $me);

                            if ($estadoConcorrente === 'amigos') {
                                $resposta = ['ok' => false, 'mensagem' => 'Vocês já são amigos.', 'estado' => 'amigos', 'codigo' => 409];
                            } elseif ($estadoConcorrente === 'enviada') {
                                $resposta = ['ok' => false, 'mensagem' => 'Você já enviou uma solicitação para este usuário.', 'estado' => 'enviada', 'codigo' => 409];
                            } elseif ($estadoConcorrente === 'recebida') {
                                $resposta = ['ok' => false, 'mensagem' => 'Este usuário já enviou uma solicitação para você. Aceite na área de solicitações.', 'estado' => 'recebida', 'codigo' => 409];
                            } else {
                                $resposta = ['ok' => false, 'mensagem' => 'Não foi possível enviar a solicitação.', 'estado' => null, 'codigo' => 409];
                            }

                            return $resposta;
                        }

                        throw new RuntimeException('insert solicitacao');
                    }

                    $stmt->close();
                }

                $sucesso  = true;
                $mensagem = 'Solicitação enviada.';
                $estado   = 'enviada';
                if (!amizadeNotificacaoSolicitacao($conn, $outro, $me)) {
                    throw new RuntimeException('notificacao solicitacao');
                }
                break;

            case 'cancelar':
                if (!$linha || $linha['amiStatus'] !== 'pendente' || (int) $linha['idSolicitante'] !== $me) {
                    $mensagem = 'Não há solicitação pendente para cancelar.';
                    $estado   = amizadeEstadoLinha($linha, $me);
                    break;
                }

                $stmt = $conn->prepare("DELETE FROM tblAmizades WHERE idAmizade = ?");
                $stmt->bind_param('i', $linha['idAmizade']);

                if (!$stmt->execute()) {
                    throw new RuntimeException('delete cancelar');
                }

                $stmt->close();

                $sucesso  = true;
                $mensagem = 'Solicitação cancelada.';
                $estado   = 'nenhuma';
                amizadeNotificacaoMarcarLida($conn, $outro, $me);
                break;

            case 'aceitar':
                if (!$linha || $linha['amiStatus'] !== 'pendente' || (int) $linha['idDestinatario'] !== $me) {
                    $mensagem = 'Solicitação não encontrada.';
                    $estado   = amizadeEstadoLinha($linha, $me);
                    break;
                }

                $stmt = $conn->prepare("UPDATE tblAmizades SET amiStatus = 'aceita' WHERE idAmizade = ?");
                $stmt->bind_param('i', $linha['idAmizade']);

                if (!$stmt->execute()) {
                    throw new RuntimeException('update aceitar');
                }

                $stmt->close();

                $sucesso  = true;
                $mensagem = 'Solicitação aceita. Vocês agora são amigos.';
                $estado   = 'amigos';
                amizadeNotificacaoMarcarLida($conn, $me, (int) $linha['idSolicitante']);
                if (!notificacoesCriar($conn, (int) $linha['idSolicitante'], $me, 'amizade_aceita', 'Solicitação aceita', 'Sua solicitação de amizade foi aceita.')) {
                    throw new RuntimeException('notificacao amizade aceita');
                }
                break;

            case 'recusar':
                if (!$linha || $linha['amiStatus'] !== 'pendente' || (int) $linha['idDestinatario'] !== $me) {
                    $mensagem = 'Solicitação não encontrada.';
                    $estado   = amizadeEstadoLinha($linha, $me);
                    break;
                }

                $stmt = $conn->prepare("UPDATE tblAmizades SET amiStatus = 'recusada' WHERE idAmizade = ?");
                $stmt->bind_param('i', $linha['idAmizade']);

                if (!$stmt->execute()) {
                    throw new RuntimeException('update recusar');
                }

                $stmt->close();

                $sucesso  = true;
                $mensagem = 'Solicitação recusada.';
                $estado   = 'nenhuma';
                amizadeNotificacaoMarcarLida($conn, $me, (int) $linha['idSolicitante']);
                break;

            case 'desfazer':
                if (!$linha || $linha['amiStatus'] !== 'aceita') {
                    $mensagem = 'Vocês não são amigos.';
                    $estado   = amizadeEstadoLinha($linha, $me);
                    break;
                }

                $stmt = $conn->prepare("DELETE FROM tblAmizades WHERE idAmizade = ?");
                $stmt->bind_param('i', $linha['idAmizade']);

                if (!$stmt->execute()) {
                    throw new RuntimeException('delete desfazer');
                }

                $stmt->close();

                $sucesso  = true;
                $mensagem = 'Amizade desfeita.';
                $estado   = 'nenhuma';
                break;
        }

        $conn->commit();
    } catch (Throwable $e) {
        $conn->rollback();
        error_log('[ReddArt] amizades: ' . $e->getMessage());

        return ['ok' => false, 'mensagem' => 'Erro interno. Tente novamente.', 'estado' => null, 'codigo' => 500];
    }

    return [
        'ok'       => $sucesso,
        'mensagem' => $mensagem !== '' ? $mensagem : 'Nenhuma alteração foi necessária.',
        'estado'   => $estado,
        'codigo'   => $sucesso ? 200 : 409,
    ];
}

function amizadeNormalizarUsuario($linha)
{
    $nome = trim((string) ($linha['userNome'] ?? ''));
    $nick = trim((string) ($linha['userNick'] ?? ''));
    $foto = trim((string) ($linha['userFoto'] ?? ''));

    return [
        'id'   => (int) ($linha['idUsuario'] ?? 0),
        'nome' => $nome !== '' ? $nome : 'Artista',
        'nick' => $nick,
        'foto' => $foto !== '' ? $foto : 'uploads/usuNovoPerfil.jpg',
    ];
}

function amizadeListarAmigos($conn, $me)
{
    if (!$conn || $me <= 0) {
        return [];
    }

    $sql = "SELECT u.idUsuario, u.userNome, u.userNick, u.userFoto, f.amiAtualizadaEm
            FROM tblAmizades f
            JOIN tblUsuario u
              ON u.idUsuario = CASE WHEN f.idSolicitante = ? THEN f.idDestinatario ELSE f.idSolicitante END
            WHERE f.amiStatus = 'aceita' AND (f.idSolicitante = ? OR f.idDestinatario = ?)
            ORDER BY u.userNome ASC";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        return [];
    }

    $stmt->bind_param('iii', $me, $me, $me);
    $stmt->execute();
    $res = $stmt->get_result();
    $lista = [];

    while ($res && ($linha = $res->fetch_assoc())) {
        $lista[] = amizadeNormalizarUsuario($linha);
    }

    $stmt->close();

    return $lista;
}

function amizadeListarRecebidas($conn, $me)
{
    if (!$conn || $me <= 0) {
        return [];
    }

    $sql = "SELECT u.idUsuario, u.userNome, u.userNick, u.userFoto, f.amiCriadaEm
            FROM tblAmizades f
            JOIN tblUsuario u ON u.idUsuario = f.idSolicitante
            WHERE f.idDestinatario = ? AND f.amiStatus = 'pendente'
            ORDER BY f.amiCriadaEm DESC";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        return [];
    }

    $stmt->bind_param('i', $me);
    $stmt->execute();
    $res = $stmt->get_result();
    $lista = [];

    while ($res && ($linha = $res->fetch_assoc())) {
        $lista[] = amizadeNormalizarUsuario($linha);
    }

    $stmt->close();

    return $lista;
}

function amizadeListarEnviadas($conn, $me)
{
    if (!$conn || $me <= 0) {
        return [];
    }

    $sql = "SELECT u.idUsuario, u.userNome, u.userNick, u.userFoto, f.amiCriadaEm
            FROM tblAmizades f
            JOIN tblUsuario u ON u.idUsuario = f.idDestinatario
            WHERE f.idSolicitante = ? AND f.amiStatus = 'pendente'
            ORDER BY f.amiCriadaEm DESC";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        return [];
    }

    $stmt->bind_param('i', $me);
    $stmt->execute();
    $res = $stmt->get_result();
    $lista = [];

    while ($res && ($linha = $res->fetch_assoc())) {
        $lista[] = amizadeNormalizarUsuario($linha);
    }

    $stmt->close();

    return $lista;
}
