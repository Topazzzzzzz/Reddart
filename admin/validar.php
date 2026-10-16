<?php
session_start();
require_once '../setup/conexao.php'; 

$email = $_POST['email'] ?? '';
$senha = $_POST['senha'] ?? '';

if (!empty($email) && !empty($senha)) {

    $stmt = $conn->prepare("SELECT idUsuario, userNome, userSenha, idCargo FROM tblUsuario WHERE userEmail = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 1) {
        $usuario = $resultado->fetch_assoc();

        if (password_verify($senha, $usuario['userSenha'])) {

            $_SESSION['usuario_id']    = $usuario['idUsuario'];
            $_SESSION['usuario_nome']  = $usuario['userNome'];
            $_SESSION['usuario_cargo'] = (int)$usuario['idCargo'];

            header("Location: home.php");
            exit;

        } else {
            header("Location: index.php?erro=" . urlencode("Senha incorreta!"));
            exit;
        }
    } else {
        header("Location: index.php?erro=" . urlencode("Usuário não encontrado!"));
        exit;
    }

    $stmt->close();

} else {
    header("Location: index.php?erro=" . urlencode("Preencha todos os campos!"));
    exit;
}
?>