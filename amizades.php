<?php
session_start();
include "setup/conexao.php";
include_once 'include/amizadesFuncoes.php';

if (!isset($_SESSION['idUsuario'])) {
    header("Location: login.php");
    exit;
}

$me  = amizadeSessaoId();
$aba = $_GET['aba'] ?? 'amigos';

if (!in_array($aba, ['amigos', 'recebidas', 'enviadas'], true)) {
    $aba = 'amigos';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $abaPost = $_POST['aba'] ?? 'amigos';
    $acao    = $_POST['acao'] ?? '';
    $outro   = (int) ($_POST['outro'] ?? 0);
    $csrf    = (string) ($_POST['csrf'] ?? '');

    if (!in_array($abaPost, ['amigos', 'recebidas', 'enviadas'], true)) {
        $abaPost = 'amigos';
    }

    if (!amizadeVerificarCsrf($csrf)) {
        $_SESSION['amizadeFlash'] = ['tipo' => 'erro', 'texto' => 'Token de segurança inválido. Recarregue a página.'];
    } else {
        $resultado = amizadeExecutarAcao($conn, $me, $outro, $acao);
        $_SESSION['amizadeFlash'] = [
            'tipo'  => $resultado['ok'] ? 'ok' : 'erro',
            'texto' => $resultado['mensagem'],
        ];
    }

    header("Location: amizades.php?aba={$abaPost}", true, 303);
    exit;
}

$flash = $_SESSION['amizadeFlash'] ?? null;
unset($_SESSION['amizadeFlash']);

$amigos    = amizadeListarAmigos($conn, $me);
$recebidas = amizadeListarRecebidas($conn, $me);
$enviadas  = amizadeListarEnviadas($conn, $me);

$totalAmigos    = count($amigos);
$totalRecebidas = count($recebidas);
$totalEnviadas  = count($enviadas);

function amizadeEscapar($valor)
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Amizades | ReddArt</title>
    <link rel="icon" type="image/png" href="img/logo.png">
    <meta name="csrf-amizade" content="<?php echo amizadeEscapar(amizadeTokenCsrf()); ?>">
    <link rel="stylesheet" href="css/headerTopo.css">
    <link rel="stylesheet" href="css/amizades.css?v=1">
    <link rel="stylesheet" href="css/footer.css">
    <link rel="stylesheet" href="css/responsivo.css?v=1">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo+Black&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <?php include_once 'include/headerTopo.php'; ?>

    <main class="amizades-main">
        <h1 class="amizades-titulo"><i class="fa-solid fa-user-group"></i> Amizades</h1>

        <nav class="amizade-tabs" aria-label="Seções de amizade">
            <a href="amizades.php?aba=amigos" class="<?php echo $aba === 'amigos' ? 'active' : ''; ?>">
                <i class="fa-solid fa-user-group"></i> Amigos
                <span class="amizade-tab-count"><?php echo $totalAmigos; ?></span>
            </a>
            <a href="amizades.php?aba=recebidas" class="<?php echo $aba === 'recebidas' ? 'active' : ''; ?>">
                <i class="fa-solid fa-inbox"></i> Recebidas
                <span class="amizade-tab-count"><?php echo $totalRecebidas; ?></span>
            </a>
            <a href="amizades.php?aba=enviadas" class="<?php echo $aba === 'enviadas' ? 'active' : ''; ?>">
                <i class="fa-solid fa-paper-plane"></i> Enviadas
                <span class="amizade-tab-count"><?php echo $totalEnviadas; ?></span>
            </a>
        </nav>

        <?php if ($flash): ?>
            <div class="amizade-flash amizade-flash--<?php echo $flash['tipo'] === 'ok' ? 'ok' : 'erro'; ?>" role="status">
                <?php echo amizadeEscapar($flash['texto']); ?>
            </div>
        <?php endif; ?>

        <?php if ($aba === 'amigos'): ?>
            <?php if ($totalAmigos > 0): ?>
                <div class="amigos-grid">
                    <?php foreach ($amigos as $amigo): ?>
                        <article class="amigo-card">
                            <div class="amigo-card-top">
                                <img class="amigo-avatar" src="<?php echo amizadeEscapar($amigo['foto']); ?>" alt="Foto de <?php echo amizadeEscapar($amigo['nome']); ?>" loading="lazy">
                                <div class="amigo-dados">
                                    <a class="amigo-nome" href="perfil.php?id=<?php echo (int) $amigo['id']; ?>"><?php echo amizadeEscapar($amigo['nome']); ?></a>
                                    <?php if ($amigo['nick'] !== ''): ?>
                                        <span class="amigo-nick">@<?php echo amizadeEscapar($amigo['nick']); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="amigo-acoes">
                                <a class="btn-friend" href="perfil.php?id=<?php echo (int) $amigo['id']; ?>"><i class="fa-solid fa-eye"></i> Ver perfil</a>
                                <form method="post" action="amizades.php" data-amizade-form data-confirm="Tem certeza que deseja desfazer esta amizade?">
                                    <input type="hidden" name="aba" value="amigos">
                                    <input type="hidden" name="acao" value="desfazer">
                                    <input type="hidden" name="outro" value="<?php echo (int) $amigo['id']; ?>">
                                    <input type="hidden" name="csrf" value="<?php echo amizadeEscapar(amizadeTokenCsrf()); ?>">
                                    <button type="submit" class="btn-friend btn-friend--desfazer"><i class="fa-solid fa-user-minus"></i> Desfazer amizade</button>
                                </form>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="amizades-vazio">
                    <i class="fa-solid fa-user-group"></i>
                    <p>Você ainda não tem amigos.</p>
                    <small>Use a pesquisa de usuários para encontrar pessoas e enviar uma solicitação.</small>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <?php if ($aba === 'recebidas'): ?>
            <?php if ($totalRecebidas > 0): ?>
                <div class="amigos-grid">
                    <?php foreach ($recebidas as $solicitante): ?>
                        <article class="amigo-card">
                            <div class="amigo-card-top">
                                <img class="amigo-avatar" src="<?php echo amizadeEscapar($solicitante['foto']); ?>" alt="Foto de <?php echo amizadeEscapar($solicitante['nome']); ?>" loading="lazy">
                                <div class="amigo-dados">
                                    <a class="amigo-nome" href="perfil.php?id=<?php echo (int) $solicitante['id']; ?>"><?php echo amizadeEscapar($solicitante['nome']); ?></a>
                                    <?php if ($solicitante['nick'] !== ''): ?>
                                        <span class="amigo-nick">@<?php echo amizadeEscapar($solicitante['nick']); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="amigo-acoes">
                                <form method="post" action="amizades.php" data-amizade-form>
                                    <input type="hidden" name="aba" value="recebidas">
                                    <input type="hidden" name="acao" value="aceitar">
                                    <input type="hidden" name="outro" value="<?php echo (int) $solicitante['id']; ?>">
                                    <input type="hidden" name="csrf" value="<?php echo amizadeEscapar(amizadeTokenCsrf()); ?>">
                                    <button type="submit" class="btn-friend btn-friend--aceitar"><i class="fa-solid fa-check"></i> Aceitar</button>
                                </form>
                                <form method="post" action="amizades.php" data-amizade-form>
                                    <input type="hidden" name="aba" value="recebidas">
                                    <input type="hidden" name="acao" value="recusar">
                                    <input type="hidden" name="outro" value="<?php echo (int) $solicitante['id']; ?>">
                                    <input type="hidden" name="csrf" value="<?php echo amizadeEscapar(amizadeTokenCsrf()); ?>">
                                    <button type="submit" class="btn-friend btn-friend--recusar"><i class="fa-solid fa-xmark"></i> Recusar</button>
                                </form>
                                <a class="btn-friend" href="perfil.php?id=<?php echo (int) $solicitante['id']; ?>"><i class="fa-solid fa-eye"></i> Ver perfil</a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="amizades-vazio">
                    <i class="fa-solid fa-inbox"></i>
                    <p>Nenhuma solicitação recebida.</p>
                    <small>Quando alguém enviar uma solicitação, ela aparecerá aqui.</small>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <?php if ($aba === 'enviadas'): ?>
            <?php if ($totalEnviadas > 0): ?>
                <div class="amigos-grid">
                    <?php foreach ($enviadas as $destinatario): ?>
                        <article class="amigo-card">
                            <div class="amigo-card-top">
                                <img class="amigo-avatar" src="<?php echo amizadeEscapar($destinatario['foto']); ?>" alt="Foto de <?php echo amizadeEscapar($destinatario['nome']); ?>" loading="lazy">
                                <div class="amigo-dados">
                                    <a class="amigo-nome" href="perfil.php?id=<?php echo (int) $destinatario['id']; ?>"><?php echo amizadeEscapar($destinatario['nome']); ?></a>
                                    <?php if ($destinatario['nick'] !== ''): ?>
                                        <span class="amigo-nick">@<?php echo amizadeEscapar($destinatario['nick']); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="amigo-acoes">
                                <form method="post" action="amizades.php" data-amizade-form data-confirm="Cancelar esta solicitação de amizade?">
                                    <input type="hidden" name="aba" value="enviadas">
                                    <input type="hidden" name="acao" value="cancelar">
                                    <input type="hidden" name="outro" value="<?php echo (int) $destinatario['id']; ?>">
                                    <input type="hidden" name="csrf" value="<?php echo amizadeEscapar(amizadeTokenCsrf()); ?>">
                                    <button type="submit" class="btn-friend btn-friend--cancelar"><i class="fa-solid fa-xmark"></i> Cancelar solicitação</button>
                                </form>
                                <a class="btn-friend" href="perfil.php?id=<?php echo (int) $destinatario['id']; ?>"><i class="fa-solid fa-eye"></i> Ver perfil</a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="amizades-vazio">
                    <i class="fa-solid fa-paper-plane"></i>
                    <p>Nenhuma solicitação enviada.</p>
                    <small>Visite o perfil de um usuário e clique em "Adicionar amigo".</small>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </main>

    <script src="js/amizades.js?v=1" defer></script>

    <?php include_once 'include/footer.php'; ?>
</body>

</html>
