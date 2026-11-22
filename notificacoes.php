<?php
session_start();
$tituloPagina = 'Notificações';
include_once 'include/header.php';
include_once 'include/notificacoesFuncoes.php';

$idUsuarioNotificacoes = (int) $_SESSION['idUsuario'];
$tokenNotificacoes = notificacoesToken();
$stmt = $conn->prepare("SELECT n.idNotificacao, n.idAtor, n.notTipo, n.notTitulo, n.notMensagem, n.idPublicacao, n.notLida, n.notHora, u.userNome AS atorNome, p.pubLegenda
                        FROM tblNotificacoes n
                        LEFT JOIN tblUsuario u ON u.idUsuario = n.idAtor
                        LEFT JOIN tblPublicacoes p ON p.idPublicacao = n.idPublicacao
                        WHERE n.idUsuario = ?
                        ORDER BY n.notHora DESC, n.idNotificacao DESC
                        LIMIT 100");
$stmt->bind_param('i', $idUsuarioNotificacoes);
$stmt->execute();
$resultado = $stmt->get_result();
$notificacoes = [];

while ($resultado && ($linha = $resultado->fetch_assoc())) {
    $tipo = (string) $linha['notTipo'];
    $ator = trim((string) ($linha['atorNome'] ?? '')) ?: 'Alguém';
    $linha['tituloExibicao'] = trim((string) ($linha['notTitulo'] ?? '')) ?: ($tipo === 'seguidor' ? 'Novo seguidor' : 'Notificação');
    $linha['mensagemExibicao'] = trim((string) ($linha['notMensagem'] ?? ''));

    if ($linha['mensagemExibicao'] === '') {
        if ($tipo === 'seguidor') {
            $linha['mensagemExibicao'] = $ator . ' começou a seguir você.';
        } elseif ($tipo === 'visualizacao') {
            $linha['mensagemExibicao'] = $ator . ' abriu sua arte “' . (trim((string) ($linha['pubLegenda'] ?? '')) ?: 'Sem título') . '”.';
        } else {
            $linha['mensagemExibicao'] = $ator . ' enviou uma notificação.';
        }
    }

    if ($tipo === 'amizade_solicitacao') {
        $linha['urlExibicao'] = 'amizades.php?aba=recebidas';
    } elseif ($tipo === 'amizade_aceita') {
        $linha['urlExibicao'] = 'amizades.php?aba=amigos';
    } elseif ($tipo === 'visualizacao' || $tipo === 'seguidor') {
        $linha['urlExibicao'] = 'perfil.php?id=' . (int) $linha['idAtor'];
    } else {
        $linha['urlExibicao'] = '#';
    }

    $notificacoes[] = $linha;
}
$stmt->close();
?>
<main id="conteudo" class="rd-notificacoes-pagina" tabindex="-1">
    <header class="rd-notificacoes-pagina__cabecalho">
        <div>
            <h2>Notificações</h2>
            <p>Acompanhe avisos e atividades da sua conta.</p>
        </div>
        <?php if ($notificacoes): ?>
            <form method="post" action="api/notificacoes.php" data-notificacoes-marcar-todas>
                <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($tokenNotificacoes, ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="acao" value="marcar_todas">
                <button type="submit">Marcar todas como lidas</button>
            </form>
        <?php endif; ?>
    </header>
    <section class="rd-notificacoes-pagina__lista" aria-label="Lista de notificações">
        <?php if (!$notificacoes): ?>
            <p class="rd-notificacoes__vazio">Você não tem notificações.</p>
        <?php else: ?>
            <?php foreach ($notificacoes as $notificacao): ?>
                <article class="rd-notificacoes-pagina__item<?php echo (int) $notificacao['notLida'] === 0 ? ' is-nao-lida' : ''; ?>">
                    <div class="rd-notificacoes-pagina__texto">
                        <strong><?php echo htmlspecialchars($notificacao['tituloExibicao'], ENT_QUOTES, 'UTF-8'); ?></strong>
                        <p><?php echo nl2br(htmlspecialchars($notificacao['mensagemExibicao'], ENT_QUOTES, 'UTF-8')); ?></p>
                        <time><?php echo date('d/m/Y H:i', strtotime($notificacao['notHora'])); ?></time>
                    </div>
                    <div class="rd-notificacoes-pagina__acoes">
                        <a href="<?php echo htmlspecialchars($notificacao['urlExibicao'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo $notificacao['urlExibicao'] === '#' ? 'Aviso' : 'Abrir'; ?></a>
                        <?php if ((int) $notificacao['notLida'] === 0): ?>
                            <form method="post" action="api/notificacoes.php" data-notificacao-marcar>
                                <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($tokenNotificacoes, ENT_QUOTES, 'UTF-8'); ?>">
                                <input type="hidden" name="acao" value="marcar_lida">
                                <input type="hidden" name="id" value="<?php echo (int) $notificacao['idNotificacao']; ?>">
                                <button type="submit">Marcar como lida</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>
</main>
<style>
.rd-notificacoes-pagina { max-width: 900px; margin: 0 auto; }
.rd-notificacoes-pagina__cabecalho { display:flex; align-items:center; justify-content:space-between; gap:16px; margin-bottom:20px; }
.rd-notificacoes-pagina__cabecalho h2 { margin:0 0 5px; color:var(--rd-texto,#eef0ff); font-size:22px; }
.rd-notificacoes-pagina__cabecalho p { margin:0; color:var(--rd-texto-suave,#a9a9c4); font-size:13px; }
.rd-notificacoes-pagina__cabecalho button,.rd-notificacoes-pagina__acoes button,.rd-notificacoes-pagina__acoes a { border:0; border-radius:8px; padding:9px 12px; background:#25179e; color:#fff; font:600 12px Inter,sans-serif; text-decoration:none; cursor:pointer; }
.rd-notificacoes-pagina__lista { display:grid; gap:10px; }
.rd-notificacoes-pagina__item { display:flex; align-items:center; justify-content:space-between; gap:16px; padding:16px; border:1px solid var(--rd-borda,rgba(255,255,255,.08)); border-radius:13px; background:rgba(255,255,255,.025); }
.rd-notificacoes-pagina__item.is-nao-lida { border-left:3px solid #ef4444; background:rgba(79,70,229,.08); }
.rd-notificacoes-pagina__texto strong { color:var(--rd-texto,#eef0ff); font-size:14px; }
.rd-notificacoes-pagina__texto p { margin:6px 0; color:var(--rd-texto-suave,#a9a9c4); font-size:13px; line-height:1.5; }
.rd-notificacoes-pagina__texto time { color:#858597; font-size:11px; }
.rd-notificacoes-pagina__acoes { display:flex; flex-wrap:wrap; align-items:center; gap:8px; }
.rd-notificacoes-pagina__acoes form { margin:0; }
body.dark .rd-notificacoes-pagina__cabecalho h2,body.dark .rd-notificacoes-pagina__texto strong { color:#18181b; }
body.dark .rd-notificacoes-pagina__cabecalho p,body.dark .rd-notificacoes-pagina__texto p { color:#52525b; }
body.dark .rd-notificacoes-pagina__item { border-color:rgba(0,0,0,.1); background:#fff; }
@media(max-width:620px) { .rd-notificacoes-pagina__cabecalho,.rd-notificacoes-pagina__item { align-items:flex-start; flex-direction:column; } .rd-notificacoes-pagina__acoes { width:100%; } }
</style>
<script>
document.querySelectorAll('form[data-notificacao-marcar], form[data-notificacoes-marcar-todas]').forEach(function(form){
    form.addEventListener('submit', function(event){
        event.preventDefault();
        fetch(form.action, {method:'POST', credentials:'same-origin', body:new FormData(form)})
            .then(function(response){ return response.json(); })
            .then(function(data){ if(data && data.ok) window.location.reload(); });
    });
});
</script>
