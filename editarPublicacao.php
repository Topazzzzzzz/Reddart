<?php
session_start();
include "setup/conexao.php";

// Verifica se o usuário está logado
if (!isset($_SESSION['idUsuario'])) {
    header("Location: login.php");
    exit;
}

$idUsuario = intval($_SESSION['idUsuario']);
$idPub = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Busca a publicação garantindo que pertence ao usuário logado
$sqlPub = "SELECT * FROM tblPublicacoes WHERE idPublicacao = ? AND idUsuario = ?";
$stmt = mysqli_prepare($conn, $sqlPub);
mysqli_stmt_bind_param($stmt, "ii", $idPub, $idUsuario);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$publicacao = mysqli_fetch_assoc($res);

if (!$publicacao) {
    header("Location: perfil.php");
    exit;
}

$erro = "";

// Processa o formulário de atualização
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $novoTitulo = trim($_POST['pubLegenda'] ?? '');
    $novoGenero = intval($_POST['idGenero'] ?? 0);

    if (!empty($novoTitulo)) {
        // Atualiza usando idGenero conforme a sua estrutura de banco
        $sqlUpdate = "UPDATE tblPublicacoes SET pubLegenda = ?, idGenero = ? WHERE idPublicacao = ? AND idUsuario = ?";
        $stmtUpdate = mysqli_prepare($conn, $sqlUpdate);
        mysqli_stmt_bind_param($stmtUpdate, "siii", $novoTitulo, $novoGenero, $idPub, $idUsuario);
        
        if (mysqli_stmt_execute($stmtUpdate)) {
            header("Location: perfil.php");
            exit;
        } else {
            $erro = "Erro ao atualizar a publicação. Tente novamente.";
        }
    } else {
        $erro = "O título da imagem não pode ficar vazio.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reddart — Editar Publicação</title>
    <link rel="icon" type="image/png" href="img/logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Archivo+Black&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/editarPublicacao.css">
</head>
<body>



    <main class="main-container">
        <div class="form-card">
            <div class="form-header">
                <i class="fa-solid fa-wand-magic-sparkles"></i>
                <h1>Editar Publicação</h1>
            </div>

            <?php if (!empty($erro)): ?>
                <div class="alert-error"><?php echo $erro; ?></div>
            <?php endif; ?>

            <!-- Preview da Imagem Atual -->
            <div class="preview-img-box">
                <img src="<?php echo htmlspecialchars($publicacao['pubLink']); ?>" alt="Pré-visualização da arte">
            </div>

            <form action="editarPublicacao.php?id=<?php echo $idPub; ?>" method="POST">
                
                <div class="form-group">
                    <label class="form-label">Título da Imagem</label>
                    <input type="text" name="pubLegenda" class="form-input" value="<?php echo htmlspecialchars($publicacao['pubLegenda'] ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Categoria / Gênero</label>
                    <select name="idGenero" class="form-select" required>
                        <option value="" disabled>Selecione uma categoria...</option>
                        <option value="1" <?php echo ($publicacao['idGenero'] == 1) ? 'selected' : ''; ?>>Animes</option>
                        <option value="2" <?php echo ($publicacao['idGenero'] == 2) ? 'selected' : ''; ?>>Jogos</option>
                        <option value="3" <?php echo ($publicacao['idGenero'] == 3) ? 'selected' : ''; ?>>Fan-Art</option>
                        <option value="4" <?php echo ($publicacao['idGenero'] == 4) ? 'selected' : ''; ?>>Filme</option>
                        <option value="5" <?php echo ($publicacao['idGenero'] == 5) ? 'selected' : ''; ?>>Shitpost</option>
                        <option value="6" <?php echo ($publicacao['idGenero'] == 6) ? 'selected' : ''; ?>>Esportes</option>
                        <option value="7" <?php echo ($publicacao['idGenero'] == 7) ? 'selected' : ''; ?>>Livros</option>
                        <option value="8" <?php echo ($publicacao['idGenero'] == 8) ? 'selected' : ''; ?>>Mangá</option>
                        <option value="9" <?php echo ($publicacao['idGenero'] == 9) ? 'selected' : ''; ?>>Cartoons</option>
                        <option value="10" <?php echo ($publicacao['idGenero'] == 10) ? 'selected' : ''; ?>>Moda</option>
                        <option value="11" <?php echo ($publicacao['idGenero'] == 11) ? 'selected' : ''; ?>>Paisagem</option>
                        <option value="12" <?php echo ($publicacao['idGenero'] == 12) ? 'selected' : ''; ?>>Viagens</option>
                    </select>
                </div>

                <button type="submit" class="btn-submit">Salvar Alterações</button>
                <a href="perfil.php" class="btn-cancel">Cancelar</a>

            </form>
        </div>
    </main>


</body>
</html>