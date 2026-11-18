<?php
session_start();
include "setup/conexao.php";

if (!isset($_SESSION['idUsuario'])) {
    header("Location: login.php");
    exit;
}

$idUsuario = $_SESSION['idUsuario'];
$userNome = mysqli_real_escape_string($conn, $_POST['userNome'] ?? '');
$userDescricao = mysqli_real_escape_string($conn, $_POST['userDescricao'] ?? '');

// 1. Atualiza nome e descrição na tabela tblUsuario
$sqlUpdate = "UPDATE tblUsuario SET userNome = '$userNome', userDescricao = '$userDescricao' WHERE idUsuario = $idUsuario";
mysqli_query($conn, $sqlUpdate);

// 2. Processa a FOTO DE PERFIL (se o usuário enviou uma nova)
if (isset($_FILES['userFoto']) && $_FILES['userFoto']['error'] === UPLOAD_ERR_OK) {
    $nomeArquivo = uniqid() . '_' . $_FILES['userFoto']['name'];
    $destino = 'uploads/' . $nomeArquivo;

    if (move_uploaded_file($_FILES['userFoto']['tmp_name'], $destino)) {
        // GRAVA NO BANCO O CAMINHO DA FOTO DO USUÁRIO
        $sqlFoto = "UPDATE tblUsuario SET userFoto = '$destino' WHERE idUsuario = $idUsuario";
        mysqli_query($conn, $sqlFoto);
    }
}

// 3. Processa o BANNER (se o usuário enviou um novo)
if (isset($_FILES['userBanner']) && $_FILES['userBanner']['error'] === UPLOAD_ERR_OK) {
    $nomeBanner = uniqid() . '_' . $_FILES['userBanner']['name'];
    $destinoBanner = 'uploads/' . $nomeBanner;

    if (move_uploaded_file($_FILES['userBanner']['tmp_name'], $destinoBanner)) {

        // Salva o caminho do banner na própria tblUsuario
        $sqlBanner = "UPDATE tblUsuario
                      SET userBanner = '$destinoBanner'
                      WHERE idUsuario = $idUsuario";

        mysqli_query($conn, $sqlBanner);
    }
}

// Redireciona de volta para o perfil com sucesso
header("Location: perfil.php?sucesso=1");
exit;
?>