<?php
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
error_reporting(E_ALL);

session_start();

// 1. Conexão com o banco de dados
if (file_exists(__DIR__ . '/setup/conexao.php')) {
    require_once __DIR__ . '/setup/conexao.php';
} else {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Arquivo setup/conexao.php não foi encontrado.']);
    exit;
}

if (!isset($conn) || !$conn) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Erro na conexão com o banco ($conn).']);
    exit;
}

// 2. Validação da Sessão do Usuário
$usuarioId = $_SESSION['idUsuario'] ?? $_SESSION['usuario_id'] ?? $_SESSION['id'] ?? null;
$idPublicacao = $_POST['idPublicacao'] ?? null;

if (!$usuarioId) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Você precisa estar logado para curtir.']);
    exit;
}

if (!$idPublicacao) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'ID da publicação inválido.']);
    exit;
}

$usuarioId = intval($usuarioId);
$idPublicacao = intval($idPublicacao);

try {
    // 3. Verifica se o usuário já curtiu esta publicação (usando usuario_id e post_id)
    $sqlCheck = "SELECT * FROM tblCurtidas WHERE usuario_id = ? AND post_id = ?";
    $stmtCheck = $conn->prepare($sqlCheck);
    $stmtCheck->bind_param("ii", $usuarioId, $idPublicacao);
    $stmtCheck->execute();
    $resCheck = $stmtCheck->get_result();

    if ($resCheck && $resCheck->num_rows > 0) {
        // --- DESCURTIR ---
        $sqlDelete = "DELETE FROM tblCurtidas WHERE usuario_id = ? AND post_id = ?";
        $stmtDel = $conn->prepare($sqlDelete);
        $stmtDel->bind_param("ii", $usuarioId, $idPublicacao);
        $stmtDel->execute();
        
        // Decrementa na tblPublicacoes
        $sqlUpdate = "UPDATE tblPublicacoes SET pubCurtidas = GREATEST(0, pubCurtidas - 1) WHERE idPublicacao = ?";
        $stmtUp = $conn->prepare($sqlUpdate);
        $stmtUp->bind_param("i", $idPublicacao);
        $stmtUp->execute();
        
        $curtiu = false;
    } else {
        // --- CURTIR ---
        $sqlInsert = "INSERT INTO tblCurtidas (usuario_id, post_id) VALUES (?, ?)";
        $stmtIns = $conn->prepare($sqlInsert);
        $stmtIns->bind_param("ii", $usuarioId, $idPublicacao);
        $stmtIns->execute();
        
        // Incrementa na tblPublicacoes
        $sqlUpdate = "UPDATE tblPublicacoes SET pubCurtidas = COALESCE(pubCurtidas, 0) + 1 WHERE idPublicacao = ?";
        $stmtUp = $conn->prepare($sqlUpdate);
        $stmtUp->bind_param("i", $idPublicacao);
        $stmtUp->execute();
        
        $curtiu = true;
    }

    // 4. Busca o total atualizado de curtidas
    $sqlTotal = "SELECT pubCurtidas FROM tblPublicacoes WHERE idPublicacao = ?";
    $stmtTot = $conn->prepare($sqlTotal);
    $stmtTot->bind_param("i", $idPublicacao);
    $stmtTot->execute();
    $resTotal = $stmtTot->get_result()->fetch_assoc();
    $totalCurtidas = intval($resTotal['pubCurtidas'] ?? 0);

    // 5. Retorna o resultado em JSON
    echo json_encode([
        'sucesso' => true,
        'curtiu' => $curtiu,
        'totalCurtidas' => $totalCurtidas
    ]);

} catch (Exception $e) {
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Erro interno no banco de dados: ' . $e->getMessage()
    ]);
}
exit;