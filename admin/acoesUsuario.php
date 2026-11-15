<?php
session_start();
require_once '../setup/conexao.php';

if (!isset($_SESSION['usuario_cargo']) || $_SESSION['usuario_cargo'] !== 2) {
    header("Location: home.php");
    exit;
}

if (isset($_GET['acao']) && $_GET['acao'] === 'excluir') {
    $idUsuario = (int)($_GET['id'] ?? 0);

    if ($idUsuario > 0 && $idUsuario !== $_SESSION['usuario_id']) {
        $stmt = $conn->prepare("DELETE FROM tblUsuario WHERE idUsuario = ?");
        $stmt->bind_param("i", $idUsuario);
        $stmt->execute();
        $stmt->close();

        header("Location: gerenciarUsuarios.php?msg=" . urlencode("Usuário removido!"));
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'atualizar') {
    $idUsuario = (int)$_POST['idUsuario'];
    $nome      = $_POST['userNome'] ?? '';
    $nick      = $_POST['userNick'] ?? '';
    $email     = $_POST['userEmail'] ?? '';
    $cargo     = (int)$_POST['idCargo'];

    if ($idUsuario > 0 && !empty($nome) && !empty($email)) {
        $stmt = $conn->prepare("UPDATE tblUsuario SET userNome = ?, userNick = ?, userEmail = ?, idCargo = ? WHERE idUsuario = ?");
        $stmt->bind_param("sssii", $nome, $nick, $email, $cargo, $idUsuario);
        $stmt->execute();
        $stmt->close();

        header("Location: gerenciarUsuarios.php?msg=" . urlencode("Dados atualizados!"));
        exit;
    }
}

header("Location: gerenciarUsuarios.php");
exit;
?>