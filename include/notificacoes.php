<?php
include_once __DIR__ . '/notificacoesFuncoes.php';

$idUsuarioNotificacoes = (int) ($_SESSION['idUsuario'] ?? 0);
$totalNotificacoesNaoLidas = notificacoesContarNaoLidas($conn ?? null, $idUsuarioNotificacoes);
?>
<link rel="stylesheet" href="css/notificacoes.css?v=1">
<div class="rd-notificacoes" data-notificacoes data-endpoint="api/notificacoes.php" data-csrf="<?php echo htmlspecialchars(notificacoesToken(), ENT_QUOTES, 'UTF-8'); ?>">
    <button type="button" class="rd-notificacoes__botao" data-notificacoes-abrir aria-label="Notificações" aria-expanded="false" aria-controls="rdPainelNotificacoes" title="Notificações">
        <i class="fa-regular fa-bell" aria-hidden="true"></i>
        <span class="rd-notificacoes__contador" data-notificacoes-contador<?php echo $totalNotificacoesNaoLidas > 0 ? '' : ' hidden'; ?>><?php echo $totalNotificacoesNaoLidas > 99 ? '99+' : (int) $totalNotificacoesNaoLidas; ?></span>
    </button>
    <section class="rd-notificacoes__painel" id="rdPainelNotificacoes" data-notificacoes-painel hidden aria-label="Suas notificações">
        <header class="rd-notificacoes__cabecalho">
            <strong>Notificações</strong>
            <button type="button" data-notificacoes-ler-todas>Marcar todas como lidas</button>
        </header>
        <div class="rd-notificacoes__lista" data-notificacoes-lista aria-live="polite">
            <p class="rd-notificacoes__vazio">Carregando notificações...</p>
        </div>
        <a class="rd-notificacoes__ver-todas" href="notificacoes.php">Ver todas</a>
    </section>
</div>
<script src="js/notificacoes.js?v=1" defer></script>
