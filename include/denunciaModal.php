<?php
include_once __DIR__ . '/denunciasFuncoes.php';
$tiposDenuncia = denunciaTipos();
?>
<link rel="stylesheet" href="css/denuncias.css?v=1">
<div class="denuncia-overlay" data-denuncia-modal hidden>
    <section class="denuncia-modal" role="dialog" aria-modal="true" aria-labelledby="denunciaTitulo">
        <button type="button" class="denuncia-fechar" data-denuncia-fechar aria-label="Fechar">&times;</button>
        <h2 id="denunciaTitulo">Denunciar publicação</h2>
        <p>Selecione o motivo. A denúncia será analisada pela equipe administrativa.</p>
        <form data-denuncia-form>
            <input type="hidden" name="csrf" value="<?php echo htmlspecialchars(denunciaToken(), ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="idPublicacao" value="">
            <label for="denunciaTipo">Motivo da denúncia</label>
            <select name="idTipo" id="denunciaTipo" required>
                <option value="">Selecione um motivo</option>
                <?php foreach ($tiposDenuncia as $idTipo => $rotulo): ?>
                    <option value="<?php echo (int) $idTipo; ?>"><?php echo htmlspecialchars($rotulo, ENT_QUOTES, 'UTF-8'); ?></option>
                <?php endforeach; ?>
            </select>
            <div class="denuncia-retorno" data-denuncia-retorno role="status" hidden></div>
            <button type="submit" class="denuncia-enviar">Enviar denúncia</button>
        </form>
    </section>
</div>
<script src="js/denuncias.js?v=1" defer></script>
