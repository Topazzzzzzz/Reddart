<?php
session_start();
require_once '../setup/conexao.php';

if (!isset($_SESSION['usuario_cargo']) || $_SESSION['usuario_cargo'] !== 2) {
    header("Location: home.php");
    exit;
}

$sql = "SELECT p.idPublicacao, p.idUsuario, p.pubHora, p.pubLink, p.pubLegenda, p.idGenero, p.pubCurtidas, u.userNome, u.userNick 
        FROM tblPublicacoes p 
        LEFT JOIN tblUsuario u ON p.idUsuario = u.idUsuario 
        ORDER BY p.pubHora DESC";
$resultado = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>REDDART - Gerenciar Publicações</title>
    <link rel="stylesheet" href="../css/gerenciar.css">
    <link rel="icon" type="image/jpeg" href="../img/logo.jpg">
    <link rel="shortcut icon" type="image/jpeg" href="../img/logo.jpg">
</head>
<body>

    <h1 class="logo">REDDART</h1>

    <div class="admin-card-wide">
        <span class="badge-admin">PAINEL ADMINISTRATIVO</span>
        <h2>Gerenciar Publicações</h2>

        <?php if (isset($_GET['msg'])): ?>
            <p class="msg-sucesso"><?php echo htmlspecialchars($_GET['msg']); ?></p>
        <?php endif; ?>

        <table class="tabela-usuarios">
            <thead>
                <tr>
                    <th>Mídia</th>
                    <th>Autor</th>
                    <th>Legenda</th>
                    <th>Curtidas</th>
                    <th>Data / Hora</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($pub = $resultado->fetch_assoc()): ?>
                    <?php 
                        $midia = $pub['pubLink'];
                        if (!empty($midia)) {
                            if (!str_starts_with($midia, 'http') && !str_starts_with($midia, '../')) {
                                $midia = '../' . $midia;
                            }
                        } else {
                            $midia = '../uploads/BannerPrin2.jpg';
                        }
                    ?>
                    <tr>
                        <td>
                            <img src="<?php echo htmlspecialchars($midia); ?>" 
                                 alt="Mídia" 
                                 class="avatar-img" 
                                 style="object-fit: cover; border-radius: 4px;"
                                 onerror="this.onerror=null;this.src='../uploads/BannerPrin2.jpg';">
                        </td>
                        <td>
                            <strong><?php echo htmlspecialchars($pub['userNome'] ?? 'Desconhecido'); ?></strong><br>
                            <span class="user-nick">@<?php echo htmlspecialchars($pub['userNick'] ?? 'desconhecido'); ?></span>
                        </td>
                        <td><?php echo htmlspecialchars($pub['pubLegenda']); ?></td>
                        <td><?php echo (int)$pub['pubCurtidas']; ?></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($pub['pubHora'])); ?></td>
                        <td>
                            <div class="acoes-cell">
                                <button class="btn-editar" onclick="abrirModal(<?php echo htmlspecialchars(json_encode($pub)); ?>)">Editar</button>
                                
                                <a href="acoes_publicacoes.php?acao=excluir&id=<?php echo $pub['idPublicacao']; ?>" 
                                   class="btn-excluir" 
                                   onclick="return confirm('Tem certeza que deseja excluir esta publicação?');">Excluir</a>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <a href="home.php" class="btn-voltar">Voltar ao Painel</a>
    </div>

    <div id="modalEditar" class="modal">
        <div class="modal-content">
            <h3>Editar Publicação</h3>
            <form action="acoes_publicacoes.php" method="POST">
                <input type="hidden" name="acao" value="atualizar">
                <input type="hidden" name="idPublicacao" id="edit-id">

                <label>Legenda:</label>
                <input type="text" name="pubLegenda" id="edit-legenda" required>

                <label>Link da Mídia:</label>
                <input type="text" name="pubLink" id="edit-link" required>

                <label>Gênero (ID):</label>
                <input type="number" name="idGenero" id="edit-genero" required>

                <label>Curtidas:</label>
                <input type="number" name="pubCurtidas" id="edit-curtidas" required>

                <div class="modal-acoes">
                    <button type="submit" class="btn-editar" style="flex: 1; padding: 12px;">Salvar</button>
                    <button type="button" class="btn-excluir" onclick="fecharModal()" style="flex: 1; padding: 12px; background: #222230;">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function abrirModal(publicacao) {
            document.getElementById('edit-id').value = publicacao.idPublicacao;
            document.getElementById('edit-legenda').value = publicacao.pubLegenda;
            document.getElementById('edit-link').value = publicacao.pubLink;
            document.getElementById('edit-genero').value = publicacao.idGenero;
            document.getElementById('edit-curtidas').value = publicacao.pubCurtidas;
            document.getElementById('modalEditar').style.display = 'flex';
        }

        function fecharModal() {
            document.getElementById('modalEditar').style.display = 'none';
        }
    </script>

</body>
</html>