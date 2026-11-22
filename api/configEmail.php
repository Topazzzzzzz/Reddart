<?php

return [
    'host'            => 'smtp.gmail.com',
    'porta'           => 587,
    'seguranca'       => 'tls',            // 'tls' (porta 587) ou 'ssl' (porta 465)
    'usuario'         => 'reddart267@gmail.com',  
    'senha'           => 'kxxoiidxcluqqiki',  
    'remetente_email' => 'reddart267@gmail.com',   
    'remetente_nome'  => 'ReddArt',

    // Deixe true SÓ para descobrir o erro; depois volte para false.
    // Com true, a mensagem de erro real do SMTP aparece no alerta do site.
    'debug'           => true,
];