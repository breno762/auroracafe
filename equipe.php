<?php
session_start();
$diretorio = __DIR__ . '/dados';
if (!is_dir($diretorio)) mkdir($diretorio, 0750, true);
$usuariosPadrao = [
    'chefe' => ['hash' => '$2y$12$BzAQ.3cHK1o1JXGEbV9TB.g7pJOw4xRiBgvbQ2loCufqstyrDrgGu', 'papel' => 'Chefe'],
    'funcionario' => ['hash' => '$2y$12$kI5Q1A7iXlmb66IuHUG7/eUWL9YqglfTIvNzpXtutqbCmTeLQPeQO', 'papel' => 'Funcionário'],
];
$arquivoUsuarios = $diretorio . '/usuarios.json';
$usuariosSalvos = is_file($arquivoUsuarios) ? json_decode((string)file_get_contents($arquivoUsuarios), true) : null;
$usuarios = is_array($usuariosSalvos) && $usuariosSalvos ? $usuariosSalvos : $usuariosPadrao;
$erro = '';
function lerDados(string $caminho): array {
    if (!is_file($caminho)) return [];
    $dados = json_decode((string)file_get_contents($caminho), true);
    return is_array($dados) ? $dados : [];
}
function salvarDados(string $caminho, array $dados): bool {
    $json = json_encode($dados, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    return file_put_contents($caminho, $json, LOCK_EX) !== false;
}
if (isset($_GET['sair'])) { $_SESSION = []; session_destroy(); header('Location: equipe.php'); exit; }
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['novo_usuario'])) {
    $logadoAgora = isset($_SESSION['equipe_usuario']) && isset($usuarios[$_SESSION['equipe_usuario']]);
    $usuarioAtual = $_SESSION['equipe_usuario'] ?? '';
    $novoUsuario = trim($_POST['novo_usuario'] ?? '');
    $novaSenha = $_POST['nova_senha'] ?? '';
    if (!$logadoAgora) $erro = 'Entre na área da equipe antes de alterar suas credenciais.';
    elseif (!preg_match('/^[a-zA-Z0-9_.-]{3,30}$/', $novoUsuario)) $erro = 'O usuário deve ter de 3 a 30 caracteres (letras, números, ponto, hífen ou sublinhado).';
    elseif (strlen($novaSenha) < 8) $erro = 'A nova senha deve ter pelo menos 8 caracteres.';
    elseif ($novoUsuario !== $usuarioAtual && isset($usuarios[$novoUsuario])) $erro = 'Esse nome de usuário já está em uso.';
    else {
        $papelAtual = $usuarios[$usuarioAtual]['papel'];
        unset($usuarios[$usuarioAtual]);
        $usuarios[$novoUsuario] = ['hash' => password_hash($novaSenha, PASSWORD_DEFAULT), 'papel' => $papelAtual];
        if (file_put_contents($arquivoUsuarios, json_encode($usuarios, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX) !== false) {
            session_regenerate_id(true);
            $_SESSION['equipe_usuario'] = $novoUsuario;
            $_SESSION['equipe_papel'] = $papelAtual;
            header('Location: equipe.php?credenciais=ok'); exit;
        }
        $erro = 'Não foi possível salvar as novas credenciais.';
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['usuario'])) {
    $usuario = trim($_POST['usuario'] ?? ''); $senha = $_POST['senha'] ?? '';
    if (isset($usuarios[$usuario]) && password_verify($senha, $usuarios[$usuario]['hash'])) {
        session_regenerate_id(true); $_SESSION['equipe_usuario'] = $usuario; $_SESSION['equipe_papel'] = $usuarios[$usuario]['papel'];
        header('Location: equipe.php'); exit;
    }
    $erro = 'Usuário ou senha incorretos.';
}
$logado = isset($_SESSION['equipe_usuario'], $_SESSION['equipe_papel']) && isset($usuarios[$_SESSION['equipe_usuario']]);
if ($logado && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['descricaoDespesa'])) {
    $descricao = trim($_POST['descricaoDespesa'] ?? '');
    $valor = filter_var($_POST['valorDespesa'] ?? '', FILTER_VALIDATE_FLOAT);
    if ($descricao === '' || strlen($descricao) > 120 || $valor === false || $valor <= 0 || $valor > 10000000) $erro = 'Informe uma descrição e um valor de despesa válido.';
    else {
        $despesas = lerDados($diretorio . '/despesas.json');
        $despesas[] = ['data' => date('c'), 'descricao' => $descricao, 'valor' => round((float)$valor, 2), 'registrado_por' => $_SESSION['equipe_usuario']];
        if (salvarDados($diretorio . '/despesas.json', $despesas)) { header('Location: equipe.php?despesa=ok'); exit; }
        $erro = 'Não foi possível salvar a despesa.';
    }
}
$pedidos = $logado ? lerDados($diretorio . '/pedidos.json') : [];
$despesas = $logado ? lerDados($diretorio . '/despesas.json') : [];
$faturamento = 0; $custoProdutos = 0;
foreach ($pedidos as $pedido) { $faturamento += (float)($pedido['faturamento'] ?? 0); $custoProdutos += (float)($pedido['custo_produtos'] ?? 0); }
$totalDespesas = array_sum(array_map(fn($d) => (float)($d['valor'] ?? 0), $despesas));
$lucroPrejuizo = $faturamento - $custoProdutos - $totalDespesas;
function dinheiro(float $valor): string { return 'R$ ' . number_format($valor, 2, ',', '.'); }
?>
<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><meta name="description" content="Área administrativa da Aurora Café."><title>Área da equipe | Aurora Café</title><link rel="stylesheet" href="style.css"></head><body>
<header class="topo"><a class="marca" href="index.html"><span class="estrela">✦</span> aurora<span class="cafe">café</span></a><nav aria-label="Navegação principal"><a href="index.html">Início</a><a href="cardapio.html">Cardápio</a><a href="sobre.html">Nossa história</a><a href="contato.html">Contato</a><a href="carrinho.php">Carrinho</a><a href="equipe.php" class="ativo">Área da equipe</a></nav></header>
<main>
<section class="pagina-topo"><p class="etiqueta">ACESSO RESTRITO</p><h1>Área da <em>equipe.</em></h1><p>Gestão interna de pedidos, faturamento, custos, despesas e resultado financeiro.</p></section>
<section class="secao">
<?php if (!$logado): ?>
<div class="login-equipe"><p class="etiqueta">LOGIN DE FUNCIONÁRIOS</p><h2>Entre no <em>painel.</em></h2><p class="muted">Use seu acesso autorizado para consultar os dados da cafeteria.</p>
<?php if ($erro): ?><p class="feedback" style="color:#b34332" role="alert"><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
<form method="POST" action="equipe.php"><label for="usuario">Usuário</label><input id="usuario" name="usuario" autocomplete="username" required><label for="senha">Senha</label><input id="senha" name="senha" type="password" autocomplete="current-password" required><button class="botao" type="submit">Entrar na área restrita ↗</button></form>
<p class="aviso-sistema" style="margin-top:20px"><strong>Acessos iniciais de demonstração:</strong><br>Chefe: <code>chefe</code> / <code>AuroraChefe2026!</code><br>Funcionário: <code>funcionario</code> / <code>AuroraEquipe2026!</code><br>Após entrar, você pode criar seu próprio usuário e senha no painel.</p></div>
<?php else: ?>
<div class="painel-topo"><div><p class="etiqueta">PAINEL FINANCEIRO</p><h2>Olá, <?= htmlspecialchars($_SESSION['equipe_papel'], ENT_QUOTES, 'UTF-8') ?>.</h2><p class="muted">Os números abaixo consideram os pedidos registrados e as despesas lançadas neste site.</p></div><a class="botao botao-secundario" href="equipe.php?sair=1">Sair da área restrita</a></div>
<?php if (isset($_GET['despesa'])): ?><p class="aviso-sistema">Despesa registrada com sucesso.</p><?php endif; ?>
<?php if ($erro): ?><p class="feedback" style="color:#b34332" role="alert"><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
<div class="metricas"><div class="metrica"><span>Faturamento registrado</span><strong><?= dinheiro($faturamento) ?></strong></div><div class="metrica"><span>Custo dos produtos vendidos</span><strong><?= dinheiro($custoProdutos) ?></strong></div><div class="metrica"><span>Despesas lançadas</span><strong><?= dinheiro($totalDespesas) ?></strong></div><div class="metrica"><span><?= $lucroPrejuizo >= 0 ? 'Lucro estimado' : 'Prejuízo estimado' ?></span><strong class="<?= $lucroPrejuizo >= 0 ? 'resultado-positivo' : 'resultado-negativo' ?>"><?= dinheiro($lucroPrejuizo) ?></strong></div></div>
<p class="aviso-sistema">Os custos unitários dos produtos são estimativas configuradas no código para fins acadêmicos. Registre aluguel, energia, salários e outras despesas no formulário abaixo para aproximar o resultado da loja. O faturamento é calculado pelos pedidos registrados, não por pagamentos confirmados.</p>
<?php if (isset($_GET['credenciais'])): ?><p class="aviso-sistema">Nome de usuário e senha atualizados. Use os novos dados no próximo acesso.</p><?php endif; ?>
<section class="painel-card" style="margin-bottom:20px"><p class="etiqueta">SEGURANÇA DA CONTA</p><h2>Alterar usuário e senha</h2><form method="POST" action="equipe.php"><label for="novo_usuario">Novo nome de usuário</label><input id="novo_usuario" name="novo_usuario" value="<?= htmlspecialchars($_SESSION['equipe_usuario'], ENT_QUOTES, 'UTF-8') ?>" minlength="3" maxlength="30" pattern="[a-zA-Z0-9_.-]{3,30}" autocomplete="username" required><label for="nova_senha">Nova senha (mínimo 8 caracteres)</label><input id="nova_senha" name="nova_senha" type="password" minlength="8" autocomplete="new-password" required><button class="botao" type="submit">Salvar novas credenciais</button></form></section>
<div class="painel-grid"><section class="painel-card"><p class="etiqueta">CONTROLE DE DESPESAS</p><h2>Lançar despesa</h2><form method="POST" action="equipe.php"><label for="descricaoDespesa">Descrição</label><input id="descricaoDespesa" name="descricaoDespesa" maxlength="120" placeholder="Ex.: conta de energia" required><label for="valorDespesa">Valor (R$)</label><input id="valorDespesa" name="valorDespesa" type="number" min="0.01" max="10000000" step="0.01" placeholder="0,00" required><button class="botao" type="submit">Salvar despesa</button></form></section>
<section class="painel-card"><p class="etiqueta">DESPESAS LANÇADAS</p><h2>Últimas despesas</h2><?php if (!$despesas): ?><p class="muted">Nenhuma despesa registrada.</p><?php else: ?><table class="tabela-painel"><thead><tr><th>Data</th><th>Descrição</th><th>Valor</th></tr></thead><tbody><?php foreach (array_slice(array_reverse($despesas), 0, 8) as $d): ?><tr><td><?= htmlspecialchars(date('d/m/Y', strtotime($d['data'] ?? 'now')), ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($d['descricao'] ?? '', ENT_QUOTES, 'UTF-8') ?></td><td><?= dinheiro((float)($d['valor'] ?? 0)) ?></td></tr><?php endforeach; ?></tbody></table><?php endif; ?></section></div>
<section class="painel-card" style="margin-top:20px"><p class="etiqueta">VENDAS REGISTRADAS</p><h2>Pedidos recentes</h2><?php if (!$pedidos): ?><p class="muted">Ainda não há pedidos. Os pedidos feitos pelo carrinho aparecerão aqui.</p><?php else: ?><table class="tabela-painel"><thead><tr><th>Data</th><th>Pedido</th><th>Cliente</th><th>Produtos</th><th>Pagamento</th><th>Faturamento</th></tr></thead><tbody><?php foreach (array_slice(array_reverse($pedidos), 0, 20) as $p): ?><tr><td><?= htmlspecialchars(date('d/m/Y H:i', strtotime($p['data'] ?? 'now')), ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars(substr($p['id'] ?? '', 0, 15), ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($p['cliente'] ?? '', ENT_QUOTES, 'UTF-8') ?></td><td><?php foreach (($p['itens'] ?? []) as $item): ?><?= htmlspecialchars($item['nome'] ?? '', ENT_QUOTES, 'UTF-8') ?> × <?= (int)($item['quantidade'] ?? 0) ?><br><?php endforeach; ?></td><td><?= htmlspecialchars($p['forma_pagamento'] ?? 'Não informado', ENT_QUOTES, 'UTF-8') ?></td><td><?= dinheiro((float)($p['faturamento'] ?? 0)) ?></td></tr><?php endforeach; ?></tbody></table><?php endif; ?></section>
<?php endif; ?>
</section></main>
<footer><a class="marca" href="index.html"><span class="estrela">✦</span> aurora<span class="cafe">café</span></a><p>Um bom café. Um novo começo.</p><small>© 2026 Aurora Café — projeto acadêmico demonstrativo.</small></footer></body></html>
