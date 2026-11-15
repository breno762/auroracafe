<?php
session_start();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><meta name="description" content="Revise seu pedido na Aurora Café."><title>Carrinho | Aurora Café</title><link rel="stylesheet" href="style.css"></head>
<body>
<header class="topo">
  <a class="marca" href="index.html"><span class="estrela">✦</span> aurora<span class="cafe">café</span></a>
  <nav aria-label="Navegação principal"><a href="index.html">Início</a><a href="cardapio.html">Cardápio</a><a href="sobre.html">Nossa história</a><a href="contato.html">Contato</a><a href="carrinho.php" class="ativo">Carrinho <span id="contadorCarrinho">(0)</span></a><a href="equipe.php">Área da equipe</a></nav>
  <a class="botao" href="cardapio.html">Continuar comprando</a>
</header>
<main>
<section class="pagina-topo"><p class="etiqueta">SEU PEDIDO</p><h1>Seu carrinho <em>Aurora.</em></h1><p>Confira os produtos, ajuste as quantidades e informe seus dados para registrar o pedido.</p></section>
<section class="secao"><div class="carrinho-layout"><div class="carrinho-lista"><h2>Produtos escolhidos</h2><div id="listaCarrinho"><p class="muted">Carregando carrinho...</p></div></div>
<aside class="resumo-carrinho"><h2>Resumo</h2><div class="total-linha"><span>Subtotal</span><strong id="subtotalCarrinho">R$ 0,00</strong></div><div class="total-linha"><span>Entrega</span><span>A combinar</span></div><div class="total-linha destaque-total"><span>Total dos produtos</span><strong id="totalCarrinho">R$ 0,00</strong></div>
<form id="formFinalizarPedido" action="finalizar_pedido.php" method="POST"><label for="nomeCliente">Seu nome</label><input id="nomeCliente" name="nome" required minlength="2" maxlength="100" placeholder="Como podemos chamar você?"><label for="emailCliente">E-mail</label><input id="emailCliente" name="email" type="email" required maxlength="150" placeholder="voce@email.com"><label for="formaPagamento">Forma de pagamento</label><select id="formaPagamento" name="forma_pagamento" required><option value="" selected disabled>Escolha como deseja pagar</option><option value="Pix na entrega/retirada">Pix na entrega/retirada</option><option value="Cartão na entrega/retirada">Cartão na entrega/retirada</option><option value="Dinheiro na entrega/retirada">Dinheiro na entrega/retirada</option></select><input type="hidden" id="itensPedido" name="itens" value="[]"><button class="botao" id="finalizarPedido" type="submit" disabled>Confirmar pedido ↗</button><p class="feedback">Projeto demonstrativo: escolha a forma de pagamento. Nenhuma cobrança online será realizada.</p></form>
</aside></div></section>
</main>
<footer><a class="marca" href="index.html"><span class="estrela">✦</span> aurora<span class="cafe">café</span></a><p>Um bom café. Um novo começo.</p><small>© 2026 Aurora Café — projeto acadêmico demonstrativo.</small></footer>
<script src="script.js"></script>
</body></html>
