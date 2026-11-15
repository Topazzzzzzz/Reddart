<?php
session_start();
require_once '../setup/conexao.php';

if (!isset($_SESSION['usuario_cargo']) || $_SESSION['usuario_cargo'] !== 2) {
    header("Location: home.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['limpar_todas_noticias'])) {
    $stmtLimpar = $conn->prepare("DELETE FROM tblNoticias");
    $stmtLimpar->execute();
    $stmtLimpar->close();

    header("Location: gerenciarNoticias.php?msg=" . urlencode("Todas as notícias foram removidas com sucesso!"));
    exit;
}

$sql = "SELECT idNoticia, titulo, dataPublicacao, conteudo, imagem, categoria 
        FROM tblNoticias 
        ORDER BY dataPublicacao DESC";
$resultado = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>REDDART - Gerenciar Notícias</title>
    <link rel="stylesheet" href="../css/gerenciar.css">
    <link rel="icon" type="image/jpeg" href="../img/logo.jpg">
    <link rel="shortcut icon" type="image/jpeg" href="../img/logo.jpg">
</head>
<body>

    <h1 class="logo">REDDART</h1>

    <div class="admin-card-wide">
        <span class="badge-admin">PAINEL ADMINISTRATIVO</span>
        <h2>Gerenciar Notícias</h2>

        <?php if (isset($_GET['msg'])): ?>
            <p class="msg-sucesso"><?php echo htmlspecialchars($_GET['msg']); ?></p>
        <?php endif; ?>

        <table class="tabela-usuarios">
            <thead>
                <tr>
                    <th>Imagem</th>
                    <th>Título</th>
                    <th>Conteúdo</th>
                    <th>Categoria</th>
                    <th>Data de Publicação</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($resultado && $resultado->num_rows > 0): ?>
                    <?php while ($noticia = $resultado->fetch_assoc()): ?>
                        <?php 
                            $midia = $noticia['imagem'];
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
                                     alt="Imagem da Notícia" 
                                     class="avatar-img" 
                                     style="object-fit: cover; border-radius: 4px;"
                                     onerror="this.onerror=null;this.src='../uploads/BannerPrin2.jpg';">
                            </td>
                            <td><strong><?php echo htmlspecialchars($noticia['titulo']); ?></strong></td>
                            <td><?php echo htmlspecialchars(mb_strimwidth($noticia['conteudo'], 0, 80, "...")); ?></td>
                            <td><?php echo htmlspecialchars($noticia['categoria']); ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($noticia['dataPublicacao'])); ?></td>
                            <td>
                                <div class="acoes-cell">
                                    <button class="btn-editar" onclick="abrirModal(<?php echo htmlspecialchars(json_encode($noticia)); ?>)">Editar</button>
                                    
                                    <a href="acoesNoticias.php?acao=excluir&id=<?php echo $noticia['idNoticia']; ?>" 
                                       class="btn-excluir" 
                                       onclick="return confirm('Tem certeza que deseja excluir esta notícia?');">Excluir</a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 20px;">Nenhuma notícia encontrada.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div style="display: flex; gap: 10px; margin-top: 20px;">
            <button type="button" onclick="abrirModalCriar()" class="btn-editar" style="padding: 12px 20px; border-radius: 6px; font-weight: bold; cursor: pointer;">
                + Nova Notícia
            </button>

            <a href="itensSite.php" class="btn-voltar">Voltar ao Painel</a>

            <form method="POST" action="" onsubmit="return confirm('Tem certeza absoluta que deseja EXCLUIR TODAS as notícias? Esta ação não pode ser desfeita.');">
                <button type="submit" name="limpar_todas_noticias" class="btn-excluir" style="background-color: #ff4d4d; color: white; border: none; padding: 12px 20px; border-radius: 6px; cursor: pointer; font-weight: bold;">
                    Excluir Todas as Notícias
                </button>
            </form>
        </div>
    </div>

    <div id="modalCriar" class="modal">
        <div class="modal-content">
            <h3>Nova Notícia</h3>
            <form action="acoesNoticias.php" method="POST">
                <input type="hidden" name="acao" value="criar">

                <label>Título:</label>
                <input type="text" name="titulo" required>

                <label>Conteúdo:</label>
                <input type="text" name="conteudo" required>

                <label>Link da Imagem:</label>
                <input type="text" name="imagem" placeholder="Ex: uploads/noticia.jpg">

                <label>Categoria:</label>
                <input type="text" name="categoria">

                <div class="modal-acoes">
                    <button type="submit" class="btn-editar" style="flex: 1; padding: 12px;">Publicar</button>
                    <button type="button" class="btn-excluir" onclick="fecharModalCriar()" style="flex: 1; padding: 12px; background: #222230;">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <div id="modalEditar" class="modal">
        <div class="modal-content">
            <h3>Editar Notícia</h3>
            <form action="acoesNoticias.php" method="POST">
                <input type="hidden" name="acao" value="atualizar">
                <input type="hidden" name="idNoticia" id="edit-id">

                <label>Título:</label>
                <input type="text" name="titulo" id="edit-titulo" required>

                <label>Conteúdo:</label>
                <input type="text" name="conteudo" id="edit-conteudo" required>

                <label>Link da Imagem:</label>
                <input type="text" name="imagem" id="edit-imagem">

                <label>Categoria:</label>
                <input type="text" name="categoria" id="edit-categoria">

                <div class="modal-acoes">
                    <button type="submit" class="btn-editar" style="flex: 1; padding: 12px;">Salvar</button>
                    <button type="button" class="btn-excluir" onclick="fecharModal()" style="flex: 1; padding: 12px; background: #222230;">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function abrirModalCriar() {
            document.getElementById('modalCriar').style.display = 'flex';
        }

        function fecharModalCriar() {
            document.getElementById('modalCriar').style.display = 'none';
        }

        function abrirModal(noticia) {
            document.getElementById('edit-id').value = noticia.idNoticia;
            document.getElementById('edit-titulo').value = noticia.titulo;
            document.getElementById('edit-conteudo').value = noticia.conteudo;
            document.getElementById('edit-imagem').value = noticia.imagem;
            document.getElementById('edit-categoria').value = noticia.categoria;
            document.getElementById('modalEditar').style.display = 'flex';
        }

        function fecharModal() {
            document.getElementById('modalEditar').style.display = 'none';
        }
    </script>

</body>
</html>