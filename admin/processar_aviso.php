<?php
session_start();
include_once "../setup/conexao.php";

// Mesma verificação de segurança do seu home.php
if (!isset($_SESSION['usuario_cargo'])) {
    header("Location: index.php");
    exit;
}

// 1. EXCLUSÃO DE AVISO
if (isset($_GET['deletar'])) {
    $idDeletar = intval($_GET['deletar']);

    if ($idDeletar > 0) {
        // Apaga a imagem física do servidor se existir
        $sqlImage = "SELECT imagem FROM tblNoticias WHERE idNoticia = ?";
        $stmtImg = mysqli_prepare($conn, $sqlImage);
        if ($stmtImg) {
            mysqli_stmt_bind_param($stmtImg, "i", $idDeletar);
            mysqli_stmt_execute($stmtImg);
            $resImg = mysqli_stmt_get_result($stmtImg);
            if ($rowImg = mysqli_fetch_assoc($resImg)) {
                if (!empty($rowImg['imagem']) && file_exists("../" . $rowImg['imagem'])) {
                    @unlink("../" . $rowImg['imagem']);
                }
            }
            mysqli_stmt_close($stmtImg);
        }

        // Deleta do banco
        $sql = "DELETE FROM tblNoticias WHERE idNoticia = ?";
        $stmt = mysqli_prepare($conn, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $idDeletar);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }

    header("Location: gerenciar_avisos.php?status=deletado");
    exit;
}

// 2. CADASTRO DE NOVO AVISO
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo    = trim($_POST['titulo'] ?? '');
    $categoria = trim($_POST['categoria'] ?? 'Aviso');
    $conteudo  = trim($_POST['conteudo'] ?? '');
    $caminhoImagem = NULL;

    if (empty($titulo) || empty($conteudo)) {
        header("Location: gerenciar_avisos.php?erro=vazio");
        exit;
    }

    // Processamento de Upload da Imagem
    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
        $diretorioUpload = '../img/uploads/';
        
        if (!is_dir($diretorioUpload)) {
            mkdir($diretorioUpload, 0777, true);
        }

        $extensao = strtolower(pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION));
        $extensoesPermitidas = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

        if (in_array($extensao, $extensoesPermitidas)) {
            $novoNome = 'aviso_' . time() . '_' . uniqid() . '.' . $extensao;
            $caminhoCompleto = $diretorioUpload . $novoNome;

            if (move_uploaded_file($_FILES['imagem']['tmp_name'], $caminhoCompleto)) {
                $caminhoImagem = 'img/uploads/' . $novoNome;
            }
        }
    }

    // Inserção no banco via Prepared Statement
    $sql = "INSERT INTO tblNoticias (titulo, conteudo, imagem, categoria, dataPublicacao) VALUES (?, ?, ?, ?, NOW())";
    $stmt = mysqli_prepare($conn, $sql);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ssss", $titulo, $conteudo, $caminhoImagem, $categoria);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        header("Location: gerenciar_avisos.php?status=sucesso");
        exit;
    }
}

header("Location: gerenciar_avisos.php");
exit;