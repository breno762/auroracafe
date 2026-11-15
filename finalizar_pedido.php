<?php
session_start();
function responderErro(string $texto, int $codigo = 400): never {
    http_response_code($codigo);
    $mensagem = htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');
    echo '<!DOCTYPE html><html lang="pt-BR"><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Pedido | Aurora Café</title><link rel="stylesheet" href="style.css"><body><main class="secao" style="max-width:850px;margin:auto"><p class="etiqueta">AURORA CAFÉ</p><h1>Não foi possível registrar o pedido.</h1><p>' . $mensagem . '</p><a class="botao" href="carrinho.php">Voltar ao carrinho</a></main></body></html>';
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: carrinho.php'); exit; }
$nome = trim($_POST['nome'] ?? '');
$email = trim($_POST['email'] ?? '');
$formaPagamento = trim($_POST['forma_pagamento'] ?? '');
$formasPermitidas = ['Pix na entrega/retirada', 'Cartão na entrega/retirada', 'Dinheiro na entrega/retirada'];
$dadosItens = json_decode($_POST['itens'] ?? '', true);
$catalogo = [
    'Latte da Aurora' => ['preco' => 12.00, 'custo' => 4.50],
    'Cold brew cítrico' => ['preco' => 15.00, 'custo' => 5.50],
    'Chá de jardim' => ['preco' => 8.00, 'custo' => 2.50],
    'Folhado amanteigado' => ['preco' => 9.00, 'custo' => 3.50],
    'Bolo caseiro' => ['preco' => 10.00, 'custo' => 4.00],
    'Sanduíche da casa' => ['preco' => 18.00, 'custo' => 8.00],
];
if (strlen($nome) < 2 || strlen($nome) > 100 || !filter_var($email, FILTER_VALIDATE_EMAIL) || !in_array($formaPagamento, $formasPermitidas, true) || !is_array($dadosItens) || count($dadosItens) < 1 || count($dadosItens) > 30) {
    responderErro('Confira o nome, o e-mail e os produtos do pedido.');
}
$itens = []; $total = 0; $custoTotal = 0;
foreach ($dadosItens as $item) {
    $nomeProduto = $item['nome'] ?? '';
    $quantidade = filter_var($item['quantidade'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 50]]);
    if (!isset($catalogo[$nomeProduto]) || !$quantidade) { responderErro('Um dos produtos ou uma quantidade está inválida.'); }
    $preco = $catalogo[$nomeProduto]['preco']; $custo = $catalogo[$nomeProduto]['custo'];
    $itens[] = ['nome' => $nomeProduto, 'quantidade' => $quantidade, 'preco' => $preco, 'custo_unitario' => $custo];
    $total += $preco * $quantidade; $custoTotal += $custo * $quantidade;
}
$diretorio = __DIR__ . '/dados';
if (!is_dir($diretorio) && !mkdir($diretorio, 0750, true) && !is_dir($diretorio)) { responderErro('Não foi possível salvar o pedido agora.', 500); }
$arquivo = $diretorio . '/pedidos.json';
$fp = fopen($arquivo, 'c+');
if (!$fp) { responderErro('Não foi possível salvar o pedido agora.', 500); }
flock($fp, LOCK_EX); $conteudo = stream_get_contents($fp); $pedidos = json_decode($conteudo ?: '[]', true); if (!is_array($pedidos)) $pedidos = [];
$pedido = ['id' => uniqid('AC-', true), 'data' => date('c'), 'cliente' => $nome, 'email' => $email, 'itens' => $itens, 'faturamento' => round($total, 2), 'custo_produtos' => round($custoTotal, 2), 'forma_pagamento' => $formaPagamento, 'status' => 'Registrado — aguardando pagamento'];
$pedidos[] = $pedido; rewind($fp); ftruncate($fp, 0); fwrite($fp, json_encode($pedidos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)); fflush($fp); flock($fp, LOCK_UN); fclose($fp);
$_SESSION['ultimo_pedido'] = $pedido['id'];
?>
<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Pedido registrado | Aurora Café</title><link rel="stylesheet" href="style.css"></head><body>
<header class="topo"><a class="marca" href="index.html"><span class="estrela">✦</span> aurora<span class="cafe">café</span></a><nav><a href="index.html">Início</a><a href="cardapio.html">Cardápio</a><a href="carrinho.php">Carrinho</a><a href="equipe.php">Área da equipe</a></nav></header>
<main class="secao" style="max-width:850px;margin:auto"><p class="etiqueta">AURORA CAFÉ</p><h1>Pedido <em>registrado!</em></h1><p>Obrigado, <?= htmlspecialchars($nome, ENT_QUOTES, 'UTF-8') ?>. Seu pedido foi salvo para esta demonstração.</p><div class="aviso-sistema"><strong>Número do pedido:</strong> <?= htmlspecialchars($pedido['id'], ENT_QUOTES, 'UTF-8') ?><br><strong>Total dos produtos:</strong> R$ <?= number_format($total, 2, ',', '.') ?><br><strong>Forma de pagamento:</strong> <?= htmlspecialchars($formaPagamento, ENT_QUOTES, 'UTF-8') ?><br>O pedido ainda não foi pago e não há integração de entrega.</div><p><a class="botao" href="cardapio.html">Voltar ao cardápio ↗</a></p></main>
<script>try{localStorage.removeItem('auroraCafeCarrinho')}catch(e){}</script></body></html>
