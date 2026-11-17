<?php
include_once 'include/conexao.php';

$idPublicacao = intval($_GET['idPublicacao'] ?? 0);

if ($idPublicacao > 0) {
    // Faz o JOIN com tblUsuario para resgatar o nome de quem comentou
    $sql = "SELECT c.*, u.userNome 
            FROM comentarios c 
            LEFT JOIN tblUsuario u ON c.idUsuario = u.idUsuario 
            WHERE c.idPublicacao = ? 
            ORDER BY c.idComentario DESC";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $idPublicacao);
    $stmt->execute();
    $resultado = $stmt->get_result();

    $comentarios = [];
    while ($row = $resultado->fetch_assoc()) {
        $comentarios[] = [
            'nome' => $row['userNome'] ?? 'Usuário',
            'cmtTexto' => htmlspecialchars($row['cmtTexto']),
            'cmtData' => !empty($row['cmtData']) ? date('d/m/Y H:i', strtotime($row['cmtData'])) : ''
        ];
    }

    echo json_encode(['sucesso' => true, 'comentarios' => $comentarios]);
} else {
    echo json_encode(['sucesso' => false, 'comentarios' => []]);
}