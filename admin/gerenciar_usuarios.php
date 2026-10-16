<?php
session_start();
require_once '../setup/conexao.php';

if (!isset($_SESSION['usuario_cargo']) || $_SESSION['usuario_cargo'] !== 2) {
    header("Location: home.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['limpar_todos_usuarios'])) {
    $idAdmin = (int)$_SESSION['usuario_id'];
    $stmtLimpar = $conn->prepare("DELETE FROM tblUsuario WHERE idUsuario != ?");
    $stmtLimpar->bind_param("i", $idAdmin);
    $stmtLimpar->execute();
    $stmtLimpar->close();

    header("Location: gerenciar_usuarios.php?msg=" . urlencode("Todos os usuários (exceto seu perfil) foram removidos!"));
    exit;
}

$sql = "SELECT idUsuario, userFoto, userNome, userNick, userEmail, idCargo FROM tblUsuario";
$resultado = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>REDDART - Gerenciar Usuários</title>
    <link rel="stylesheet" href="../css/gerenciar.css">
    <link rel="icon" type="image/jpeg" href="../img/logo.jpg">
    <link rel="shortcut icon" type="image/jpeg" href="../img/logo.jpg">
</head>
<body>

    <h1 class="logo">REDDART</h1>

    <div class="admin-card-wide">
        <span class="badge-admin">PAINEL ADMINISTRATIVO</span>
        <h2>Gerenciar Usuários</h2>

        <?php if (isset($_GET['msg'])): ?>
            <p class="msg-sucesso"><?php echo htmlspecialchars($_GET['msg']); ?></p>
        <?php endif; ?>

        <table class="tabela-usuarios">
            <thead>
                <tr>
                    <th>Perfil</th>
                    <th>Nome / Nick</th>
                    <th>E-mail</th>
                    <th>Cargo</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($user = $resultado->fetch_assoc()): ?>
                    <?php 
                        $foto = $user['userFoto'];
                        if (!empty($foto)) {
                            if (!str_starts_with($foto, 'http') && !str_starts_with($foto, '../')) {
                                $foto = '../' . $foto;
                            }
                        } else {
                            $foto = '../uploads/usuNovoPerfil.jpg';
                        }
                    ?>
                    <tr>
                        <td>
                            <img src="<?php echo htmlspecialchars($foto); ?>" 
                                 alt="Avatar" 
                                 class="avatar-img" 
                                 onerror="this.onerror=null;this.src='../uploads/usuNovoPerfil.jpg';">
                        </td>
                        <td>
                            <strong><?php echo htmlspecialchars($user['userNome']); ?></strong><br>
                            <span class="user-nick">@<?php echo htmlspecialchars($user['userNick']); ?></span>
                        </td>
                        <td><?php echo htmlspecialchars($user['userEmail']); ?></td>
                        <td>
                            <?php echo $user['idCargo'] == 2 ? '<span style="color: #5542f6; font-weight: bold;">Admin</span>' : 'Usuário'; ?>
                        </td>
                        <td>
                            <div class="acoes-cell">
                                <button class="btn-editar" onclick="abrirModal(<?php echo htmlspecialchars(json_encode($user)); ?>)">Editar</button>
                                
                                <?php if ($user['idUsuario'] != $_SESSION['usuario_id']): ?>
                                    <a href="acoes_usuario.php?acao=excluir&id=<?php echo $user['idUsuario']; ?>" 
                                       class="btn-excluir" 
                                       onclick="return confirm('Tem certeza que deseja excluir o usuário @<?php echo $user['userNick']; ?>?');">Excluir</a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <div style="display: flex; gap: 10px; margin-top: 20px;">
            <a href="home.php" class="btn-voltar">Voltar ao Painel</a>

            <form method="POST" action="" onsubmit="return confirm('Tem certeza absoluta que deseja EXCLUIR TODOS os usuários (exceto a sua própria conta)?');">
                <button type="submit" name="limpar_todos_usuarios" class="btn-excluir" style="background-color: #ff4d4d; color: white; border: none; padding: 12px 20px; border-radius: 6px; cursor: pointer; font-weight: bold;">
                    Excluir Todos os Usuários
                </button>
            </form>
        </div>
    </div>

    <div id="modalEditar" class="modal">
        <div class="modal-content">
            <h3>Editar Usuário</h3>
            <form action="acoes_usuario.php" method="POST">
                <input type="hidden" name="acao" value="atualizar">
                <input type="hidden" name="idUsuario" id="edit-id">

                <label>Nome:</label>
                <input type="text" name="userNome" id="edit-nome" required>

                <label>Nick:</label>
                <input type="text" name="userNick" id="edit-nick" required>

                <label>E-mail:</label>
                <input type="email" name="userEmail" id="edit-email" required>

                <label>Cargo:</label>
                <select name="idCargo" id="edit-cargo">
                    <option value="1">Usuário</option>
                    <option value="2">Admin</option>
                </select>

                <div class="modal-acoes">
                    <button type="submit" class="btn-editar" style="flex: 1; padding: 12px;">Salvar</button>
                    <button type="button" class="btn-excluir" onclick="fecharModal()" style="flex: 1; padding: 12px; background: #222230;">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function abrirModal(usuario) {
            document.getElementById('edit-id').value = usuario.idUsuario;
            document.getElementById('edit-nome').value = usuario.userNome;
            document.getElementById('edit-nick').value = usuario.userNick;
            document.getElementById('edit-email').value = usuario.userEmail;
            document.getElementById('edit-cargo').value = usuario.idCargo;
            document.getElementById('modalEditar').style.display = 'flex';
        }

        function fecharModal() {
            document.getElementById('modalEditar').style.display = 'none';
        }
    </script>

</body>
</html>