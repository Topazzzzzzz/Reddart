<?php
// Se a sessão não estiver ativa, inicia
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Se o usuário não estiver logado, redireciona para o login
if (!isset($_SESSION['idUsuario'])) {
    header("Location: login.php");
    exit;
}

// Define a URL base para links dinâmicos (XAMPP ou online)
$baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');

// E-mails da equipe (edite aqui)
$emails = [
    'rodriguessantosyago2@gmail.com',
    'luizmiguellaraferreira@gmail.com',
    'miguelasfigueiredo@gmail.com',
];

// Categorias: chave => [título, descrição, ícone]
$categorias = [
    'artes'  => ['Artes',  'Enviar e publicar suas criações',        'fa-image'],
    'salvos' => ['Salvos', 'Favoritar e encontrar o que você salvou', 'fa-heart'],
    'pastas' => ['Pastas', 'Organizar seus salvos',                   'fa-folder'],
    'conta'  => ['Conta',  'Perfil, nome, bio e foto',                'fa-user'],
];

// Perguntas frequentes (para adicionar uma nova, inclua mais uma linha)
$faq = [
    [
        'cat' => 'artes',
        'q'   => 'Como faço para enviar uma nova arte?',
        'a'   => 'Clique em <strong>Adicionar</strong> no menu superior, preencha o título, escolha a categoria e selecione o arquivo de imagem.',
    ],
    [
        'cat' => 'salvos',
        'q'   => 'Como salvo uma arte nos favoritos?',
        'a'   => 'Clique no ícone de coração da arte. Ela passa a aparecer na aba <strong>Salvos</strong>.',
    ],
    [
        'cat' => 'salvos',
        'q'   => 'Como gerencio meus salvos?',
        'a'   => 'Na aba <strong>Salvos</strong> você vê tudo que favoritou. Use a busca para achar um item e o coração para desfavoritar.',
    ],
    [
        'cat' => 'salvos',
        'q'   => 'Meus salvos sumiram em outro navegador ou aparelho. O que houve?',
        'a'   => 'Os favoritos ficam guardados no navegador em que você salvou. Em outro navegador ou aparelho eles não aparecem, e limpar os dados do navegador também apaga a lista.',
    ],
    [
        'cat' => 'pastas',
        'q'   => 'Como crio uma pasta para organizar meus salvos?',
        'a'   => 'Na aba <strong>Salvos</strong>, use o botão <strong>Criar pasta</strong> no painel da direita e dê um nome para ela.',
    ],
    [
        'cat' => 'pastas',
        'q'   => 'O que acontece com os itens se eu excluir uma pasta?',
        'a'   => 'Nada se perde. Os itens continuam salvos em <strong>Tudo</strong>, apenas sem pasta.',
    ],
    [
        'cat' => 'conta',
        'q'   => 'Como altero minhas informações de perfil?',
        'a'   => 'Clique na sua foto no menu superior ou vá em <strong>Configurações &gt; Perfil</strong> para editar nome, biografia e foto.',
    ],
];

// Quantas perguntas tem cada categoria
$contagem = array_fill_keys(array_keys($categorias), 0);
foreach ($faq as $f) {
    $contagem[$f['cat']]++;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reddart - Contato e Suporte</title>
    <link rel="icon" type="image/png" href="img/logo.png">
    <link rel="stylesheet" href="css/headerTopo.css">
    <link rel="stylesheet" href="css/suporte.css">
    <link rel="stylesheet" href="css/footer.css">
    <link rel="stylesheet" href="css/responsivo.css?v=1">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo+Black&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="sup-page">

    <?php include_once 'include/headerTopo.php'; ?>

    <main class="sup-main" id="conteudo">
        <div class="sup-wrap">

            <!-- TÍTULO -->
            <section class="sup-intro">
                <span class="sup-intro-ic"><i class="fa-solid fa-headset"></i></span>
                <div>
                    <h1>Contato e suporte</h1>
                    <p>Veja as dúvidas mais comuns ou fale direto com a equipe do Reddart.</p>
                </div>
            </section>

            <!-- CATEGORIAS -->
            <section aria-labelledby="supCatTitulo">
                <h2 class="sup-h2" id="supCatTitulo">Navegue por assunto</h2>
                <div class="sup-cats">
                    <?php foreach ($categorias as $chave => [$titulo, $desc, $icone]): ?>
                        <button type="button" class="sup-cat" data-cat="<?php echo $chave; ?>" aria-pressed="false">
                            <span class="sup-cat-ic"><i class="fa-solid <?php echo $icone; ?>"></i></span>
                            <span class="sup-cat-tx">
                                <strong><?php echo $titulo; ?></strong>
                                <small><?php echo $desc; ?></small>
                            </span>
                            <span class="sup-cat-n"><?php echo $contagem[$chave]; ?></span>
                        </button>
                    <?php endforeach; ?>
                </div>
            </section>

            <!-- FAQ -->
            <section class="sup-faq" id="faq" aria-labelledby="supFaqTitulo">
                <div class="sup-faq-head">
                    <h2 class="sup-h2" id="supFaqTitulo">Perguntas frequentes</h2>
                    <span class="sup-hint">
                        <span id="supCountHint"></span>
                        <button type="button" id="supReset" class="sup-reset" hidden>Ver todas</button>
                    </span>
                </div>

                <div class="sup-list">
                    <?php foreach ($faq as $i => $item): ?>
                        <article class="sup-item" data-cat="<?php echo htmlspecialchars($item['cat']); ?>">
                            <button class="sup-q" type="button" aria-expanded="false" aria-controls="sup-a-<?php echo $i; ?>">
                                <span class="sup-q-tx"><?php echo htmlspecialchars($item['q']); ?></span>
                                <i class="fa-solid fa-chevron-down sup-chevron"></i>
                            </button>
                            <div class="sup-a" id="sup-a-<?php echo $i; ?>">
                                <div class="sup-a-inner">
                                    <p><?php echo $item['a']; ?></p>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>

            <!-- CONTATO -->
            <section class="sup-contact" aria-labelledby="supContatoTitulo">
                <div class="sup-contact-head">
                    <h2 id="supContatoTitulo">Ainda precisa de ajuda?</h2>
                    <p>Escreva para a equipe do Reddart. Conte o que aconteceu e, se puder, envie um print.</p>
                </div>
                <div class="sup-contact-grid">
                    <?php foreach ($emails as $email): ?>
                        <a class="sup-mail" href="mailto:<?php echo htmlspecialchars($email); ?>">
                            <span class="sup-mail-ic"><i class="fa-solid fa-envelope"></i></span>
                            <span class="sup-mail-tx">
                                <small>Enviar e-mail</small>
                                <strong><?php echo htmlspecialchars($email); ?></strong>
                            </span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>

        </div>
    </main>

    <?php include_once 'include/footer.php'; ?>

<script>
(function () {
    const items = Array.from(document.querySelectorAll('.sup-item'));
    const cats  = document.querySelectorAll('.sup-cat');
    const hint  = document.getElementById('supCountHint');
    const reset = document.getElementById('supReset');
    let cat = '';

    function aplicar() {
        let total = 0;

        items.forEach(item => {
            const mostrar = !cat || item.dataset.cat === cat;
            item.hidden = !mostrar;
            if (mostrar) total++;
        });

        cats.forEach(c => {
            const on = c.dataset.cat === cat;
            c.classList.toggle('active', on);
            c.setAttribute('aria-pressed', on ? 'true' : 'false');
        });

        hint.textContent = total === 1 ? '1 pergunta' : total + ' perguntas';
        reset.hidden = !cat;
    }

    // sanfona
    items.forEach(item => {
        const btn = item.querySelector('.sup-q');
        btn.addEventListener('click', () => {
            const aberto = item.classList.toggle('open');
            btn.setAttribute('aria-expanded', aberto ? 'true' : 'false');
        });
    });

    // categorias (clicar de novo desmarca)
    cats.forEach(c => {
        c.addEventListener('click', () => {
            cat = (cat === c.dataset.cat) ? '' : c.dataset.cat;
            aplicar();
            if (cat) document.getElementById('faq').scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });

    reset.addEventListener('click', () => {
        cat = '';
        aplicar();
    });

    // primeira pergunta já aberta
    if (items[0]) {
        items[0].classList.add('open');
        items[0].querySelector('.sup-q').setAttribute('aria-expanded', 'true');
    }
    aplicar();
})();
</script>

</body>
</html>
