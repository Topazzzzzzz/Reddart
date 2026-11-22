<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function notificacoesToken()
{
    if (empty($_SESSION['notificacoes_csrf']) || !is_string($_SESSION['notificacoes_csrf'])) {
        $_SESSION['notificacoes_csrf'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['notificacoes_csrf'];
}

function notificacoesValidarToken($token)
{
    $esperado = $_SESSION['notificacoes_csrf'] ?? '';

    return is_string($token) && $token !== ''
        && is_string($esperado) && $esperado !== ''
        && hash_equals($esperado, $token);
}

function notificacoesContarNaoLidas($conn, $idUsuario)
{
    if (!$conn || $idUsuario <= 0) {
        return 0;
    }

    $stmt = $conn->prepare('SELECT COUNT(*) AS total FROM tblNotificacoes WHERE idUsuario = ? AND notLida = 0');

    if (!$stmt) {
        return 0;
    }

    $stmt->bind_param('i', $idUsuario);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $total = $resultado ? (int) ($resultado->fetch_assoc()['total'] ?? 0) : 0;
    $stmt->close();

    return $total;
}

function notificacoesCriar($conn, $idUsuario, $idAtor, $tipo, $titulo, $mensagem, $idPublicacao = null, $idComentario = null)
{
    if (!$conn || $idUsuario <= 0 || $idAtor <= 0 || $idUsuario === $idAtor) {
        return false;
    }

    $stmt = $conn->prepare('INSERT INTO tblNotificacoes (idUsuario, idAtor, notTipo, notTitulo, notMensagem, idPublicacao, idComentario, notLida, notHora) VALUES (?, ?, ?, ?, ?, ?, ?, 0, NOW())');

    if (!$stmt) {
        return false;
    }

    $stmt->bind_param('iisssii', $idUsuario, $idAtor, $tipo, $titulo, $mensagem, $idPublicacao, $idComentario);
    $ok = $stmt->execute();
    $stmt->close();

    return $ok;
}
