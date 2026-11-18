<?php
session_start();
require_once '../setup/conexao.php';

if (!isset($_SESSION['usuario_cargo']) || $_SESSION['usuario_cargo'] !== 2) {
    header("Location: home.php");
    exit;
}

if (isset($_GET['acao']) && $_GET['acao'] === 'excluir') {
    $idNoticia = (int)($_GET['id'] ?? 0);

    if ($idNoticia > 0) {
        $stmt = $conn->prepare("DELETE FROM tblNoticias WHERE idNoticia = ?");
        $stmt->bind_param("i", $idNoticia);
        $stmt->execute();
        $stmt->close();

        header("Location: gerenciarNoticias.php?msg=" . urlencode("Notícia removida!"));
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'criar') {
    $titulo    = $_POST['titulo'] ?? '';
    $conteudo  = $_POST['conteudo'] ?? '';
    $imagem    = $_POST['imagem'] ?? '';
    $categoria = $_POST['categoria'] ?? '';

    if (!empty($titulo) && !empty($conteudo)) {
        $stmt = $conn->prepare("INSERT INTO tblNoticias (titulo, conteudo, imagem, categoria, dataPublicacao) VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param("ssss", $titulo, $conteudo, $imagem, $categoria);
        $stmt->execute();
        $stmt->close();

        header("Location: gerenciarNoticias.php?msg=" . urlencode("Notícia publicada com sucesso!"));
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'atualizar') {
    $idNoticia = (int)$_POST['idNoticia'];
    $titulo    = $_POST['titulo'] ?? '';
    $conteudo  = $_POST['conteudo'] ?? '';
    $imagem    = $_POST['imagem'] ?? '';
    $categoria = $_POST['categoria'] ?? '';

    if ($idNoticia > 0) {
        $stmt = $conn->prepare("UPDATE tblNoticias SET titulo = ?, conteudo = ?, imagem = ?, categoria = ? WHERE idNoticia = ?");
        $stmt->bind_param("ssssi", $titulo, $conteudo, $imagem, $categoria, $idNoticia);
        $stmt->execute();
        $stmt->close();

        header("Location: gerenciarNoticias.php?msg=" . urlencode("Notícia atualizada!"));
        exit;
    }
}

header("Location: gerenciarNoticias.php");
exit;
?>