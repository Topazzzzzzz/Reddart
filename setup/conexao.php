<?php

$base_url = "http://reddart.hubsapiens.com.br";

$servidor = "br612.hostgator.com.br";
$usuario  = "hubsap45_usr_reddart";    
$senha    = "7. Raijin...!_7. QRM — Est0# s0fr&nd0 !nt&rf&rênc!@?";        
$banco    = "hubsap45_bdreddart_2026"; 

$conn = new mysqli($servidor, $usuario, $senha, $banco);

if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>