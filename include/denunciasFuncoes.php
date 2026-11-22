<?php

function denunciaTipos()
{
    return [
        1 => 'Conteúdo sexual ou impróprio',
        2 => 'Violência ou conteúdo chocante',
        3 => 'Assédio ou discurso de ódio',
        4 => 'Spam ou fraude',
        5 => 'Violação de direitos autorais',
        6 => 'Outro motivo',
    ];
}

function denunciaToken()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (empty($_SESSION['denuncia_csrf']) || !is_string($_SESSION['denuncia_csrf'])) {
        $_SESSION['denuncia_csrf'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['denuncia_csrf'];
}
