<?php
session_start();
require_once '../setup/conexao.php';

if (!isset($_SESSION['usuario_cargo']) || $_SESSION['usuario_cargo'] !== 2) {
    header("Location: home.php");
    exit;
}

if (isset($_GET['acao']) && $_GET['acao'] === 'excluir') {
    $idPublicacao = (int)($_GET['id'] ?? 0);

    if ($idPublicacao > 0) {
        $stmt = $conn->prepare("DELETE FROM tblPublicacoes WHERE idPublicacao = ?");
        $stmt->bind_param("i", $idPublicacao);
        $stmt->execute();
        $stmt->close();

        header("Location: gerenciar_publicacoes.php?msg=" . urlencode("Publicação removida!"));
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'atualizar') {
    $idPublicacao = (int)$_POST['idPublicacao'];
    $legenda      = $_POST['pubLegenda'] ?? '';
    $link         = $_POST['pubLink'] ?? '';
    $idGenero     = (int)$_POST['idGenero'];
    $curtidas     = (int)$_POST['pubCurtidas'];

    if ($idPublicacao > 0) {
        $stmt = $conn->prepare("UPDATE tblPublicacoes SET pubLegenda = ?, pubLink = ?, idGenero = ?, pubCurtidas = ? WHERE idPublicacao = ?");
        $stmt->bind_param("ssiii", $legenda, $link, $idGenero, $curtidas, $idPublicacao);
        $stmt->execute();
        $stmt->close();

        header("Location: gerenciar_publicacoes.php?msg=" . urlencode("Publicação atualizada!"));
        exit;
    }
}

header("Location: gerenciar_publicacoes.php");
exit;
?>