<?php
session_start();
require_once '../setup/conexao.php';
require_once '../include/denunciasFuncoes.php';

if (!isset($_SESSION['usuario_cargo']) || $_SESSION['usuario_cargo'] !== 2) {
    header("Location: home.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['limpar_todas_publicacoes'])) {
    $conn->begin_transaction();
    $stmtDenuncias = $conn->prepare("DELETE FROM tblDenuncias");
    $stmtDenuncias->execute();
    $stmtDenuncias->close();
    $stmtLimpar = $conn->prepare("DELETE FROM tblPublicacoes");
    $stmtLimpar->execute();
    $stmtLimpar->close();
    $conn->commit();

    header("Location: gerenciarPublicacoes.php?msg=" . urlencode("Todas as publicações foram removidas com sucesso!"));
    exit;
}

$aba = ($_GET['aba'] ?? 'publicacoes') === 'denuncias' ? 'denuncias' : 'publicacoes';
$resultadoTotalDenuncias = $conn->query('SELECT COUNT(*) AS total FROM tblDenuncias');
$totalDenuncias = $resultadoTotalDenuncias ? (int) $resultadoTotalDenuncias->fetch_assoc()['total'] : 0;

if ($aba === 'denuncias') {
    $sql = "SELECT p.idPublicacao, p.idUsuario, p.pubLink, p.pubLegenda, u.userNome, u.userNick,
                   COUNT(d.idDenuncias) AS totalDenuncias,
                   GROUP_CONCAT(DISTINCT d.idTipo ORDER BY d.idTipo SEPARATOR ',') AS tiposDenuncia
            FROM tblDenuncias d
            INNER JOIN tblPublicacoes p ON p.idPublicacao = d.idPublicacao
            LEFT JOIN tblUsuario u ON p.idUsuario = u.idUsuario
            GROUP BY p.idPublicacao, p.idUsuario, p.pubLink, p.pubLegenda, u.userNome, u.userNick
            ORDER BY COUNT(d.idDenuncias) DESC, MAX(d.idDenuncias) DESC";
} else {
    $sql = "SELECT p.idPublicacao, p.idUsuario, p.pubHora, p.pubLink, p.pubLegenda, p.idGenero, p.pubCurtidas, u.userNome, u.userNick
            FROM tblPublicacoes p
            LEFT JOIN tblUsuario u ON p.idUsuario = u.idUsuario
            ORDER BY p.pubHora DESC";
}
$resultado = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>REDDART - Gerenciar Publicações</title>
    <link rel="stylesheet" href="../css/gerenciar.css">
    <link rel="stylesheet" href="../css/denuncias.css?v=1">
    <link rel="stylesheet" href="../css/responsivo.css?v=1">
    <link rel="icon" type="image/jpeg" href="../img/logo.jpg">
    <link rel="shortcut icon" type="image/jpeg" href="../img/logo.jpg">
</head>
<body>

    <h1 class="logo">REDDART</h1>

    <div class="admin-card-wide">
        <span class="badge-admin">PAINEL ADMINISTRATIVO</span>
        <h2>Gerenciar Publicações</h2>

        <nav class="admin-abas-denuncias" aria-label="Gerenciamento de publicações">
            <a href="gerenciarPublicacoes.php" class="<?php echo $aba === 'publicacoes' ? 'ativa' : ''; ?>">Publicações</a>
            <a href="gerenciarPublicacoes.php?aba=denuncias" class="<?php echo $aba === 'denuncias' ? 'ativa' : ''; ?>">Denúncias <span><?php echo $totalDenuncias; ?></span></a>
        </nav>

        <?php if (isset($_GET['msg'])): ?>
            <p class="msg-sucesso"><?php echo htmlspecialchars($_GET['msg']); ?></p>
        <?php endif; ?>

        <?php if ($aba === 'denuncias'): ?>
        <table class="tabela-usuarios">
            <thead>
                <tr>
                    <th>Mídia</th>
                    <th>Autor</th>
                    <th>Denúncias</th>
                    <th>Motivos informados</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($resultado && $resultado->num_rows > 0): ?>
                    <?php while ($pub = $resultado->fetch_assoc()): ?>
                        <?php
                            $midia = (string) ($pub['pubLink'] ?? '');
                            if ($midia === '') {
                                $midia = '../uploads/BannerPrin2.jpg';
                            } elseif (!str_starts_with($midia, 'http') && !str_starts_with($midia, '../')) {
                                $midia = '../' . $midia;
                            }
                            $motivos = array_filter(array_map('intval', explode(',', (string) ($pub['tiposDenuncia'] ?? ''))));
                        ?>
                        <tr>
                            <td>
                                <a href="<?php echo htmlspecialchars($midia, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener">
                                    <img src="<?php echo htmlspecialchars($midia, ENT_QUOTES, 'UTF-8'); ?>" alt="Mídia denunciada" class="avatar-img" style="object-fit:cover;border-radius:4px" onerror="this.onerror=null;this.src='../uploads/BannerPrin2.jpg';">
                                </a>
                                <br><strong><?php echo htmlspecialchars($pub['pubLegenda'] ?? 'Sem título', ENT_QUOTES, 'UTF-8'); ?></strong>
                            </td>
                            <td>
                                <strong><?php echo htmlspecialchars($pub['userNome'] ?? 'Desconhecido', ENT_QUOTES, 'UTF-8'); ?></strong><br>
                                <span class="user-nick">@<?php echo htmlspecialchars($pub['userNick'] ?? 'desconhecido', ENT_QUOTES, 'UTF-8'); ?></span>
                            </td>
                            <td><span class="denuncia-contagem"><?php echo (int) $pub['totalDenuncias']; ?></span></td>
                            <td class="denuncia-motivos">
                                <?php foreach ($motivos as $idTipo): ?>
                                    <?php if (isset($tiposDenuncia[$idTipo])): ?>
                                        <div><?php echo htmlspecialchars($tiposDenuncia[$idTipo], ENT_QUOTES, 'UTF-8'); ?></div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </td>
                            <td>
                                <div class="acoes-cell denuncia-acoes">
                                    <a class="btn-editar" href="../perfil.php?id=<?php echo (int) $pub['idUsuario']; ?>" target="_blank" rel="noopener">Perfil</a>
                                    <a href="acoesPublicacoes.php?acao=excluir&amp;id=<?php echo (int) $pub['idPublicacao']; ?>&amp;aba=denuncias" class="btn-excluir" onclick="return confirm('Excluir esta publicação? As denúncias relacionadas também serão removidas.');">Excluir publicação</a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5" style="text-align:center;padding:20px">Nenhuma denúncia recebida.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        <?php else: ?>
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
                <?php if ($resultado && $resultado->num_rows > 0): ?>
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
                                
                                <a href="acoesPublicacoes.php?acao=excluir&id=<?php echo $pub['idPublicacao']; ?>" 
                                   class="btn-excluir" 
                                   onclick="return confirm('Tem certeza que deseja excluir esta publicação?');">Excluir</a>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="6" style="text-align:center;padding:20px">Nenhuma publicação encontrada.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        <?php endif; ?>

        <?php if ($aba === 'publicacoes'): ?>
        <div style="display: flex; gap: 10px; margin-top: 20px;">
            <a href="itensSite.php" class="btn-voltar">Voltar ao Painel</a>

            <form method="POST" action="" onsubmit="return confirm('Tem certeza absoluta que deseja EXCLUIR TODAS as publicações? Esta ação não pode ser desfeita.');">
                <button type="submit" name="limpar_todas_publicacoes" class="btn-excluir" style="background-color: #ff4d4d; color: white; border: none; padding: 12px 20px; border-radius: 6px; cursor: pointer; font-weight: bold;">
                    Excluir Todas as Publicações
                </button>
            </form>
        </div>
        <?php endif; ?>
    </div>

    <div id="modalEditar" class="modal">
        <div class="modal-content">
            <h3>Editar Publicação</h3>
            <form action="acoesPublicacoes.php" method="POST">
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
