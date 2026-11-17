<?php
session_start();
header('Content-Type: application/json');

// Inclua aqui o arquivo de conexão com o seu banco (ex: conexao.php que define a variável $conn)
include_once 'include/conexao.php'; 

// Verifica se o usuário está logado (ajuste 'usuario_id' para o nome da variável de sessão do seu login)
$idUsuario = $_SESSION['usuario_id'] ?? null; 

if (!$idUsuario) {
    echo json_encode(['sucesso' => false, 'erro' => 'Você precisa estar logado para comentar.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idPublicacao = intval($_POST['idPublicacao'] ?? 0);
    $cmtTexto = trim($_POST['cmtTexto'] ?? '');

    // Validações respeitando o tipo VARCHAR(300)
    if ($idPublicacao > 0 && !empty($cmtTexto)) {
        if (mb_strlen($cmtTexto) > 300) {
            echo json_encode(['sucesso' => false, 'erro' => 'O comentário deve ter no máximo 300 caracteres.']);
            exit;
        }

        try {
            // Inserção usando a sua estrutura exata de campos
            $sql = "INSERT INTO comentarios (idUsuario, idPublicacao, cmtTexto, cmtData) VALUES (?, ?, ?, NOW(6))";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iis", $idUsuario, $idPublicacao, $cmtTexto);
            
            if ($stmt->execute()) {
                // Busca o nome do usuário para retornar e exibir na tela na hora
                $sqlUser = "SELECT userNome FROM tblUsuario WHERE idUsuario = ?";
                $stmtUser = $conn->prepare($sqlUser);
                $stmtUser->bind_param("i", $idUsuario);
                $stmtUser->execute();
                $resUser = $stmtUser->get_result();
                $dadosUser = $resUser->fetch_assoc();
                $nomeUsuario = $dadosUser['userNome'] ?? 'Usuário';

                echo json_encode([
                    'sucesso' => true,
                    'comentario' => [
                        'nome' => $nomeUsuario,
                        'cmtTexto' => htmlspecialchars($cmtTexto),
                        'cmtData' => date('d/m/Y H:i')
                    ]
                ]);
            } else {
                echo json_encode(['sucesso' => false, 'erro' => 'Erro ao salvar no banco.']);
            }
        } catch (Exception $e) {
            echo json_encode(['sucesso' => false, 'erro' => 'Erro interno no servidor.']);
        }
    } else {
        echo json_encode(['sucesso' => false, 'erro' => 'Preencha o comentário.']);
    }
}