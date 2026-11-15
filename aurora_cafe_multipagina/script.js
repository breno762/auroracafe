const formulario = document.querySelector("#pedidoForm");
const feedback = document.querySelector("#formFeedback");

if (formulario && feedback) {
  formulario.addEventListener("submit", (evento) => {
    const nome = document.querySelector("#nome").value.trim();
    const email = document.querySelector("#email").value.trim();
    const produto = document.querySelector("#produto").value;
    const mensagem = document.querySelector("#mensagem").value.trim();

    if (nome.length < 2 || !email || !produto || !mensagem) {
      evento.preventDefault();
      feedback.textContent = "Confira os campos obrigatórios antes de enviar.";
      feedback.style.color = "#b34332";
      return;
    }
    feedback.textContent = "Dados conferidos! Enviando sua mensagem...";
  });
}

// Carrinho de compras: os itens ficam salvos neste navegador até o pedido ser enviado.
const chaveCarrinho = "auroraCafeCarrinho";
const produtosCatalogo = {
  "Latte da Aurora": { preco: 12.00, custo: 4.50 },
  "Cold brew cítrico": { preco: 15.00, custo: 5.50 },
  "Chá de jardim": { preco: 8.00, custo: 2.50 },
  "Folhado amanteigado": { preco: 9.00, custo: 3.50 },
  "Bolo caseiro": { preco: 10.00, custo: 4.00 },
  "Sanduíche da casa": { preco: 18.00, custo: 8.00 }
};
function lerCarrinho() {
  try { return JSON.parse(localStorage.getItem(chaveCarrinho) || "[]"); }
  catch { return []; }
}
function salvarCarrinho(itens) {
  localStorage.setItem(chaveCarrinho, JSON.stringify(itens));
  atualizarContadorCarrinho();
}
function atualizarContadorCarrinho() {
  const contador = document.querySelector("#contadorCarrinho");
  if (contador) contador.textContent = `(${lerCarrinho().reduce((soma, item) => soma + item.quantidade, 0)})`;
}
document.querySelectorAll(".botao-carrinho").forEach((botao) => {
  botao.addEventListener("click", () => {
    const campoQuantidade = botao.closest(".produto")?.querySelector(".quantidade-produto");
    const mensagem = botao.closest(".produto")?.querySelector(".mensagem-carrinho");
    const quantidade = Math.floor(Number(campoQuantidade?.value || 1));

    if (!Number.isFinite(quantidade) || quantidade < 1) {
      if (mensagem) {
        mensagem.textContent = "Informe uma quantidade válida (mínimo 1).";
        mensagem.classList.add("mensagem-erro");
      }
      if (campoQuantidade) campoQuantidade.value = "1";
      return;
    }

    const itens = lerCarrinho();
    const nome = botao.dataset.nome;
    const existente = itens.find((item) => item.nome === nome);
    if (existente) existente.quantidade += quantidade;
    else itens.push({ nome, preco: Number(botao.dataset.preco), quantidade });
    salvarCarrinho(itens);

    if (mensagem) {
      mensagem.textContent = "Produto adicionado com sucesso!";
      mensagem.classList.remove("mensagem-erro");
    }
    if (campoQuantidade) campoQuantidade.value = "1";
  });
});
function renderizarCarrinho() {
  const lista = document.querySelector("#listaCarrinho");
  if (!lista) return;
  const itens = lerCarrinho();
  const totalEl = document.querySelector("#totalCarrinho");
  const subtotalEl = document.querySelector("#subtotalCarrinho");
  const campoItens = document.querySelector("#itensPedido");
  const botaoFinalizar = document.querySelector("#finalizarPedido");
  if (!itens.length) {
    lista.innerHTML = '<p class="muted">Seu carrinho está vazio. Acesse o cardápio para adicionar seus favoritos.</p><a class="botao" href="cardapio.html">Ir para o cardápio ↗</a>';
  } else {
    lista.innerHTML = itens.map((item, indice) => `
      <article class="item-carrinho"><div><h3>${escaparHTML(item.nome)}</h3><p>R$ ${item.preco.toFixed(2).replace(".", ",")} cada</p>
      <div class="controles-quantidade"><button type="button" data-acao="menos" data-indice="${indice}" aria-label="Diminuir quantidade">−</button><strong>${item.quantidade}</strong><button type="button" data-acao="mais" data-indice="${indice}" aria-label="Aumentar quantidade">+</button></div>
      <button class="remover-item" type="button" data-acao="remover" data-indice="${indice}">Remover</button></div><strong>R$ ${(item.preco * item.quantidade).toFixed(2).replace(".", ",")}</strong></article>`).join("");
    lista.querySelectorAll("[data-acao]").forEach((botao) => botao.addEventListener("click", () => {
      const atualizados = lerCarrinho(); const i = Number(botao.dataset.indice);
      if (botao.dataset.acao === "mais") atualizados[i].quantidade += 1;
      if (botao.dataset.acao === "menos") atualizados[i].quantidade -= 1;
      if (botao.dataset.acao === "remover" || atualizados[i].quantidade <= 0) atualizados.splice(i, 1);
      salvarCarrinho(atualizados); renderizarCarrinho();
    }));
  }
  const subtotal = itens.reduce((soma, item) => soma + item.preco * item.quantidade, 0);
  if (subtotalEl) subtotalEl.textContent = `R$ ${subtotal.toFixed(2).replace(".", ",")}`;
  if (totalEl) totalEl.textContent = `R$ ${subtotal.toFixed(2).replace(".", ",")}`;
  if (campoItens) campoItens.value = JSON.stringify(itens);
  if (botaoFinalizar) botaoFinalizar.disabled = !itens.length;
}
function escaparHTML(valor) {
  return String(valor).replace(/[&<>"']/g, (caractere) => ({"&":"&amp;", "<":"&lt;", ">":"&gt;", "\"":"&quot;", "'":"&#39;"}[caractere]));
}
const formularioCarrinho = document.querySelector("#formFinalizarPedido");
if (formularioCarrinho) formularioCarrinho.addEventListener("submit", (evento) => {
  const itens = lerCarrinho();
  if (!itens.length) { evento.preventDefault(); alert("Adicione pelo menos um produto ao carrinho."); return; }
  document.querySelector("#itensPedido").value = JSON.stringify(itens);
});
if (document.querySelector("#listaCarrinho")) renderizarCarrinho();
// O contador também é atualizado ao abrir qualquer página do site.
atualizarContadorCarrinho();
