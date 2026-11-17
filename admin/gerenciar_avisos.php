<?php
session_start();
include_once "../setup/conexao.php";

// Mesma verificação de segurança do seu home.php
if (!isset($_SESSION['usuario_cargo'])) {
    header("Location: index.php");
    exit;
}

// Busca avisos cadastrados
$sql = "SELECT * FROM tblNoticias ORDER BY dataPublicacao DESC";
$resAvisos = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Admin - Gerenciar Avisos | ReddArt</title>
    
    <link rel="stylesheet" href="../css/gerenciar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body {
            background-color: #0C0C11;
            color: #fff;
            font-family: 'Inter', sans-serif;
            padding: 30px;
        }

        .admin-box {
            max-width: 900px;
            margin: 0 auto;
            background-color: #161A1F;
            border-radius: 8px;
            padding: 30px;
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }

        .btn-voltar {
            color: #6366F1;
            text-decoration: none;
            font-weight: 600;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-bottom: 18px;
        }

        .form-group label {
            font-weight: 600;
            font-size: 14px;
        }

        input[type="text"], select, textarea {
            background-color: #0C0C11;
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 6px;
            padding: 12px;
            color: #fff;
            outline: none;
        }

        .btn-submit {
            background-color: #6366F1;
            color: #fff;
            border: none;
            padding: 12px 24px;
            font-weight: 700;
            border-radius: 6px;
            cursor: pointer;
            transition: 0.2s;
        }

        .btn-submit:hover {
            background-color: #4f46e5;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }

        th {
            color: #a1a1aa;
            font-size: 13px;
            text-transform: uppercase;
        }

        .btn-deletar {
            color: #ef4444;
            text-decoration: none;
        }
    </style>
</head>

<body>

    <div class="admin-box">
        <div class="admin-header">
            <h2><i class="fa-solid fa-bullhorn" style="color: #6366F1;"></i> GERENCIAR AVISOS</h2>
            <a href="home.php" class="btn-voltar"><i class="fa-solid fa-arrow-left"></i> Voltar à Home Admin</a>
        </div>

        <!-- FORMULÁRIO DE NOVO AVISO -->
        <form action="processar_aviso.php" method="POST" enctype="multipart/form-data">
            
            <div class="form-group">
                <label>Título do Aviso:</label>
                <input type="text" name="titulo" required placeholder="Digite o título do comunicado">
            </div>

            <div class="form-group">
                <label>Categoria:</label>
                <select name="categoria">
                    <option value="Comunicado Oficial">Comunicado Oficial</option>
                    <option value="Atualização">Atualização</option>
                    <option value="Manutenção">Manutenção</option>
                    <option value="Evento">Evento</option>
                </select>
            </div>

            <div class="form-group">
                <label>Banner / Imagem (Opcional):</label>
                <input type="file" name="imagem" accept="image/*">
            </div>

            <div class="form-group">
                <label>Conteúdo:</label>
                <textarea name="conteudo" rows="5" required placeholder="Mensagem completa..."></textarea>
            </div>

            <button type="submit" class="btn-submit"><i class="fa-solid fa-plus"></i> Publicar Comunicação</button>
        </form>

        <!-- AVISOS PUBLICADOS -->
        <h3 style="margin-top: 40px; margin-bottom: 15px;">Avisos Ativos</h3>
        <table>
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Título</th>
                    <th>Categoria</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($resAvisos && mysqli_num_rows($resAvisos) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($resAvisos)): ?>
                        <tr>
                            <td><?php echo date('d/m/Y', strtotime($row['dataPublicacao'])); ?></td>
                            <td><?php echo htmlspecialchars($row['titulo']); ?></td>
                            <td><?php echo htmlspecialchars($row['categoria']); ?></td>
                            <td>
                                <a href="processar_aviso.php?deletar=<?php echo $row['idNoticia']; ?>" class="btn-deletar" onclick="return confirm('Excluir este aviso?')">
                                    <i class="fa-solid fa-trash"></i> Excluir
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" style="color: #a1a1aa;">Nenhum aviso cadastrado ainda.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

    </div>

</body>
</html>