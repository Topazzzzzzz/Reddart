<?php
session_start();
include "setup/conexao.php";

// Se o usuário não estiver logado, manda para o login
if (!isset($_SESSION['idUsuario'])) {
    header("Location: login.php");
    exit;
}

$idUsuario = intval($_SESSION['idUsuario']);
$idPub = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($idPub > 0) {
    $sqlBusca = "SELECT pubLink FROM tblPublicacoes WHERE idPublicacao = ? AND idUsuario = ?";
    $stmtBusca = mysqli_prepare($conn, $sqlBusca);
    mysqli_stmt_bind_param($stmtBusca, "ii", $idPub, $idUsuario);
    mysqli_stmt_execute($stmtBusca);
    $resBusca = mysqli_stmt_get_result($stmtBusca);
    
    if ($pub = mysqli_fetch_assoc($resBusca)) {
        $caminhoArquivo = $pub['pubLink'];
        // Se a imagem estiver na pasta local de uploads e existir, remove do servidor
        if (!empty($caminhoArquivo) && file_exists($caminhoArquivo)) {
            @unlink($caminhoArquivo);
        }
    }

    // 2. Executa a exclusão da publicação no banco de dados com segurança
    $sqlDelete = "DELETE FROM tblPublicacoes WHERE idPublicacao = ? AND idUsuario = ?";
    $stmtDelete = mysqli_prepare($conn, $sqlDelete);
    mysqli_stmt_bind_param($stmtDelete, "ii", $idPub, $idUsuario);
    mysqli_stmt_execute($stmtDelete);
}

// Redireciona de volta para o perfil após excluir
header("Location: perfil.php");
exit;
?>