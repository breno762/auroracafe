<?php
// Exemplo educacional de processamento de formulário com PHP.
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: contato.html");
    exit;
}

$nome = trim($_POST["nome"] ?? "");
$email = trim($_POST["email"] ?? "");
$produto = trim($_POST["produto"] ?? "");
$mensagem = trim($_POST["mensagem"] ?? "");

$assuntosPermitidos = [
    "Latte da Aurora",
    "Cold brew cítrico",
    "Folhado amanteigado",
    "Dúvida ou sugestão"
];

$valido = mb_strlen($nome) >= 2
    && filter_var($email, FILTER_VALIDATE_EMAIL)
    && in_array($produto, $assuntosPermitidos, true)
    && mb_strlen($mensagem) >= 1;

if (!$valido) {
    http_response_code(400);
    $titulo = "Confira seus dados";
    $retorno = "Algum campo está inválido. Volte e tente novamente.";
} else {
    $titulo = "Mensagem recebida!";
    $retorno = "Obrigado, " . htmlspecialchars($nome, ENT_QUOTES, "UTF-8")
        . ". Sua mensagem foi processada nesta demonstração.";
}
$mensagemSegura = htmlspecialchars($mensagem, ENT_QUOTES, "UTF-8");
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title><?= htmlspecialchars($titulo, ENT_QUOTES, "UTF-8") ?> | Aurora Café</title><link rel="stylesheet" href="style.css"></head>
<body>
<header class="topo"><a class="marca" href="index.html"><span class="estrela">✦</span> aurora<span class="cafe">café</span></a><nav><a href="index.html">Início</a><a href="cardapio.html">Cardápio</a><a href="sobre.html">Nossa história</a><a href="contato.html">Contato</a></nav></header>
<main class="secao" style="max-width:900px;margin:6vh auto;background:#fffdf8"><p class="etiqueta">AURORA CAFÉ</p><h1 style="font-size:48px"><?= htmlspecialchars($titulo, ENT_QUOTES, "UTF-8") ?></h1><p><?= $retorno ?></p><p><strong>Mensagem:</strong> <?= $mensagemSegura ?></p><a class="botao" href="index.html">Voltar ao início ↗</a></main>
</body></html>