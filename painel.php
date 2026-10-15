<?php
session_start();
require './conn/conexao.php';

// Proteção de rota: verifica se o utilizador está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit;
}

$isAdmin = ($_SESSION['nivel_acesso'] === 'admin');
$iniciais = strtoupper(substr($_SESSION['nome'], 0, 2));
$stmt_user = $pdo->prepare("SELECT foto_perfil FROM usuarios WHERE id = ?");
$stmt_user->execute([$_SESSION['usuario_id']]);
$foto_perfil = $stmt_user->fetchColumn();

// Buscar todos os produtos
$stmt = $pdo->query("SELECT * FROM produtos WHERE status = 'ativo' ORDER BY id DESC");
$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Cálculos das métricas
$totalUnidades = array_sum(array_column($produtos, 'quantidade'));
$produtosAtivos = count(array_filter($produtos, fn($p) => $p['status'] === 'ativo'));
$estoqueBaixo = count(array_filter($produtos, fn($p) => $p['quantidade'] < 10));
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StockControl - Painel de Produtos</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
</head>

<body>
    <div class="dashboard-container">
        <!-- Header Principal -->
        <header class="topbar">
            <div class="topbar-left">
                <div class="logo" style="display: flex; align-items: center; gap: 8px;">
                    <div style="width: 24px; height: 24px; background: #9D7AFF; border-radius: 4px;"></div>
                    <strong style="font-size: 1.25rem;">StockControl</strong>
                </div>
                <nav class="nav-links">
                    <a href="painel.php" class="active">Produtos</a>
                    <a href="./movimentacoes.php">Movimentações</a>
                </nav>
            </div>

            <div class="topbar-right">
                <?php if (isset($isAdmin) && $isAdmin && basename($_SERVER['PHP_SELF']) == 'painel.php'): ?>
                    <a href="./novo-produto.php" class="btn-new"><i class="ph ph-plus"></i> Novo produto</a>
                <?php endif; ?>

                <a href="perfil.php" style="text-decoration: none;">
                    <div class="user-profile" style="transition: transform 0.2s ease; cursor: pointer;"
                        onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">

                        <div class="avatar">
                            <?php if (!empty($foto_perfil)): ?>
                                <img src="uploads/<?= htmlspecialchars($foto_perfil) ?>"
                                    style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">
                            <?php else: ?>
                                <?= $iniciais ?>
                            <?php endif; ?>
                        </div>

                        <div class="user-info">
                            <span><?= htmlspecialchars($_SESSION['nome']) ?></span>
                            <span class="user-role"><?= ucfirst($_SESSION['nivel_acesso']) ?></span>
                        </div>
                    </div>
                </a>

                <a href="logout.php" class="logout-link"><i class="ph ph-sign-out"></i> Sair</a>
            </div>
    </div>
    </header>

    <main>
        <!-- Banner de Sucesso (Exibido após adicionar comentário/movimentação) -->
        <?php if (isset($_SESSION['mensagem_sucesso'])): ?>
            <div class="success-banner">
                <?= htmlspecialchars($_SESSION['mensagem_sucesso']) ?>
            </div>
            <?php unset($_SESSION['mensagem_sucesso']); // Limpa a mensagem após exibir ?>
        <?php endif; ?>

        <!-- Cabeçalho com Métricas (ESTAVA FALTANDO) -->
        <section class="page-header">
            <span class="tag" style="background: none; padding: 0;">SEU ESTOQUE, EM ORDEM.</span>
            <h1>Tudo no seu lugar.</h1>
            <p>Acompanhe seus produtos e deixe o estoque fluir.</p>

            <div class="metrics-grid">
                <div class="metric-card">
                    <h3><?= $totalUnidades ?></h3>
                    <p>unidades em estoque</p>
                </div>
                <div class="metric-card">
                    <h3><?= $produtosAtivos ?></h3>
                    <p>produtos ativos</p>
                </div>
                <div class="metric-card">
                    <h3><?= $estoqueBaixo ?></h3>
                    <p>produto com estoque baixo</p>
                </div>
            </div>
        </section>

        <!-- Barra de Busca e Filtro (ESTAVA FALTANDO) -->
        <section class="filters-section">
            <div>
                <span style="font-size: 0.75rem; color: #A8A8B3; display: block; margin-bottom: 8px;">BUSCAR
                    PRODUTO</span>
                <input type="text" class="search-input" placeholder="Buscar por nome ou descrição">
            </div>
            <button class="toggle-active" style="cursor: pointer;">Somente ativos</button>
        </section>

        <!-- Grid de Produtos Dinâmico -->
        <section class="products-grid">
            <?php foreach ($produtos as $produto): ?>
                <div class="product-card">

                    <!-- Informações do Produto (ESTAVA FALTANDO) -->
                    <div class="product-info">
                        <div class="product-image">
                            <?php if (!empty($produto['imagem'])): ?>
                                <img src="uploads/<?= htmlspecialchars($produto['imagem']) ?>" alt="Img"
                                    style="width: 100%; height: 100%; object-fit: cover; border-radius: 8px;">
                            <?php else: ?>
                                <div
                                    style="display: flex; align-items: center; justify-content: center; height: 100%; color: #9D7AFF;">
                                    <i class="ph ph-package" style="font-size: 40px;"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="product-details">
                            <h4><?= htmlspecialchars($produto['nome']) ?></h4>
                            <p><?= htmlspecialchars($produto['descricao']) ?></p>
                            <div class="product-price">R$ <?= number_format($produto['preco'], 2, ',', '.') ?></div>
                        </div>
                    </div>

                    <!-- Footer com os botões (O SEU JÁ ESTAVA CERTO) -->
                    <div class="product-footer">
                        <div class="product-stock">
                            <span><?= sprintf("%02d", $produto['quantidade']) ?></span> unidades
                        </div>
                        <div class="product-actions">
                            <a href="#"
                                onclick="abrirModalMovimentar(<?= $produto['id'] ?>, '<?= htmlspecialchars($produto['nome']) ?>', <?= $produto['quantidade'] ?>)"
                                class="action-link" style="text-decoration:none;">
                                <i class="ph ph-arrows-down-up"></i> Movimentar
                            </a>

                            <?php if ($isAdmin): ?>
                                <a href="editar-produto.php?id=<?= $produto['id'] ?>" class="action-link">
                                    <i class="ph ph-pencil-simple"></i> Editar
                                </a>
                                <button
                                    onclick="abrirModalExcluir(<?= $produto['id'] ?>, '<?= htmlspecialchars($produto['nome']) ?>')"
                                    class="action-link"
                                    style="background:none; border:none; cursor:pointer; padding: 0; font-family: inherit;">
                                    <i class="ph ph-trash"></i> Excluir
                                </button>
                            <?php endif; ?>

                            <button
                                onclick="abrirModalComentarios(<?= $produto['id'] ?>, '<?= htmlspecialchars($produto['nome']) ?>')"
                                class="action-link"
                                style="background:none; border:none; cursor:pointer; padding: 0; font-family: inherit;">
                                <i class="ph ph-chat-text"></i> Comentários
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </section>
    </main>
    </div>

    <!-- MODAL 1: Lista de Comentários -->
    <div class="modal-overlay" id="modal-lista-comentarios" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h2>Conversas que organizam.</h2>
                    <p id="modal-produto-nome">Produto / Observações do produto</p>
                </div>
                <button class="btn-close" onclick="fecharModal('modal-lista-comentarios')">&times;</button>
            </div>

            <p style="color: #52525B; font-size: 0.75rem; margin-bottom: 16px;">Comentários • Mais recentes primeiro</p>

            <div class="comment-list" id="lista-comentarios-container"></div>

            <div
                style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #29292E; padding-top: 24px;">
                <button class="btn-secondary" onclick="fecharModal('modal-lista-comentarios')">Fechar</button>
                <button class="btn-primary" onclick="abrirModalAddComentario()" style="width: auto; margin: 0;">+
                    Adicionar comentário</button>
            </div>
        </div>
    </div>

    <!-- MODAL 2: Adicionar Comentário -->
    <div class="modal-overlay" id="modal-add-comentario" style="display: none;">
        <div class="modal-content" style="max-width: 500px;">
            <div class="modal-header">
                <div>
                    <h2>Adicionar comentário</h2>
                    <p id="modal-add-produto-nome">Produto / Observações do produto</p>
                </div>
                <button class="btn-close" onclick="fecharModal('modal-add-comentario')">&times;</button>
            </div>

            <form action="adicionar-comentario.php" method="POST">
                <input type="hidden" name="produto_id" id="input-add-produto-id">
                <div style="margin-bottom: 16px;">
                    <label style="display: block; margin-bottom: 8px; font-size: 0.875rem; color: #E1E1E6;">Comentário
                        *</label>
                    <textarea name="comentario" rows="4" class="form-input"
                        placeholder="Lote conferido. Todos os itens estão em bom estado." required></textarea>
                </div>

                <p style="color: #52525B; font-size: 0.75rem; margin-bottom: 24px;">Esta ação ficará registrada no
                    histórico do produto.</p>

                <div
                    style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #29292E; padding-top: 24px;">
                    <button type="button" class="btn-secondary" onclick="fecharModal('modal-add-comentario')">Fechar e
                        voltar</button>
                    <button type="submit" class="btn-primary" style="width: auto; margin: 0;">Cadastrar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 3: Excluir Produto -->
    <div class="modal-overlay" id="modal-excluir-produto" style="display: none;">
        <div class="modal-content" style="max-width: 400px;">
            <div class="modal-header">
                <div>
                    <h2>Excluir produto?</h2>
                    <p id="modal-excluir-produto-nome">Nome do Produto</p>
                </div>
                <button class="btn-close" onclick="fecharModal('modal-excluir-produto')">&times;</button>
            </div>

            <p style="color: #E1E1E6; font-size: 0.875rem; margin-bottom: 24px; line-height: 1.5;">Atenção! Tem certeza
                que deseja excluir o produto?<br>Esta ação não poderá ser desfeita.</p>

            <div
                style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #29292E; padding-top: 24px;">
                <button type="button" class="btn-secondary"
                    onclick="fecharModal('modal-excluir-produto')">Cancelar</button>
                <form action="excluir-produto.php" method="POST" style="margin: 0;">
                    <input type="hidden" name="id" id="input-excluir-produto-id">
                    <button type="submit" class="btn-danger">Sim, excluir produto</button>
                </form>
            </div>
        </div>
    </div>
    <!-- MODAL 4: Movimentar Estoque -->
    <div class="modal-overlay" id="modal-movimentar" style="display: none;">
        <div class="modal-content" style="max-width: 450px;">
            <div class="modal-header">
                <div>
                    <h2>Cada movimento conta.</h2>
                    <p id="modal-mov-subtitle">Produto / 0 unidades disponíveis</p>
                </div>
                <button class="btn-close" onclick="fecharModal('modal-movimentar')">&times;</button>
            </div>

            <form action="registrar-movimentacao.php" method="POST">
                <input type="hidden" name="produto_id" id="input-mov-produto-id">
                <input type="hidden" name="tipo_movimento" id="input-mov-tipo" value="entrada">

                <!-- Abas -->
                <div class="tabs-container">
                    <button type="button" class="tab-btn active" id="btn-tab-entrada"
                        onclick="setTipoMovimentacao('entrada')">&plus; Entrada</button>
                    <button type="button" class="tab-btn" id="btn-tab-saida"
                        onclick="setTipoMovimentacao('saida')">&uarr; Saída</button>
                </div>

                <div style="margin-bottom: 16px;">
                    <label style="display: block; margin-bottom: 8px; font-size: 0.875rem; color: #E1E1E6;">Quantidade
                        *</label>
                    <input type="number" name="quantidade" id="input-mov-quantidade" class="form-input"
                        style="width: 100%; padding: 16px; background-color: #121214; border: 1px solid #29292E; border-radius: 8px; color: #FFF;"
                        required min="1" oninput="calcularEstoque()">
                </div>

                <!-- Preview Dinâmico -->
                <div class="stock-preview">
                    <span>ESTOQUE APÓS A MOVIMENTAÇÃO</span>
                    <strong id="preview-estoque-final">0 unidades</strong>
                </div>

                <div style="margin-bottom: 24px;">
                    <label style="display: block; margin-bottom: 8px; font-size: 0.875rem; color: #E1E1E6;">Observação
                        (opcional)</label>
                    <input type="text" name="observacao" class="form-input"
                        style="width: 100%; padding: 16px; background-color: #121214; border: 1px solid #29292E; border-radius: 8px; color: #FFF;"
                        placeholder="Ex: Recebimento de novo lote.">
                </div>

                <p style="color: #52525B; font-size: 0.75rem; margin-bottom: 24px;">Responsável:
                    <?= htmlspecialchars($_SESSION['nome']) ?> | Data e hora automáticas.
                </p>

                <div
                    style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #29292E; padding-top: 24px;">
                    <button type="button" class="btn-secondary"
                        onclick="fecharModal('modal-movimentar')">Cancelar</button>
                    <button type="submit" class="btn-primary" id="btn-submit-movimentacao"
                        style="width: auto; margin: 0;">Registrar entrada</button>
                </div>
            </form>
        </div>
    </div>

    <!-- LÓGICA JAVASCRIPT DOS MODAIS -->
    <script>
        let produtoAtualId = null;
        let produtoAtualNome = '';

        function fecharModal(id) {
            document.getElementById(id).style.display = 'none';
        }

        // --- Lógica de Excluir Produto ---
        function abrirModalExcluir(id, nome) {
            document.getElementById('modal-excluir-produto-nome').textContent = nome;
            document.getElementById('input-excluir-produto-id').value = id;
            document.getElementById('modal-excluir-produto').style.display = 'flex';
        }

        // --- Lógica de Comentários ---
        function abrirModalComentarios(id, nome) {
            produtoAtualId = id;
            produtoAtualNome = nome;

            document.getElementById('modal-produto-nome').textContent = nome + ' / Observações do produto';
            const commentList = document.getElementById('lista-comentarios-container');

            commentList.innerHTML = '<p style="color: #A8A8B3; text-align: center; padding: 20px;">Carregando...</p>';
            document.getElementById('modal-lista-comentarios').style.display = 'flex';

            fetch(`buscar-comentarios.php?produto_id=${id}`)
                .then(response => response.json())
                .then(data => {
                    commentList.innerHTML = '';

                    if (data.length === 0) {
                        commentList.innerHTML = '<p style="color: #A8A8B3; text-align: center; padding: 20px;">Nenhum comentário registrado ainda.</p>';
                        return;
                    }

                    data.forEach(com => {
                        const iniciais = com.nome.substring(0, 2).toUpperCase();
                        const dataComentario = new Date(com.criado_em);
                        const formatada = dataComentario.toLocaleDateString('pt-BR', { day: '2-digit', month: 'short', year: 'numeric' }) + ' • ' + dataComentario.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });

                        // Botão de excluir comentário (aparece só se tiver permissão)
                        let btnExcluir = '';
                        if (com.pode_excluir) {
                            btnExcluir = `<button onclick="excluirComentario(${com.id})" style="background:none; border:none; color: #EF4444; font-size: 0.75rem; cursor: pointer; margin-top: 8px;">Deletar comentário</button>`;
                        }

                        commentList.innerHTML += `
                            <div class="comment-card">
                                <div class="avatar" style="color: ${com.nivel_acesso === 'admin' ? '#9D7AFF' : '#FFF'};">${iniciais}</div>
                                <div class="comment-body">
                                    <h4>${com.nome} <span style="color: #A8A8B3; font-weight: normal;">- ${com.nivel_acesso.charAt(0).toUpperCase() + com.nivel_acesso.slice(1)}</span></h4>
                                    <span style="font-size: 0.75rem; color: #52525B;">${formatada}</span>
                                    <p style="margin-top: 8px; font-size: 0.875rem; line-height: 1.4; color: #E1E1E6;">${com.comentario}</p>
                                    ${btnExcluir}
                                </div>
                            </div>
                        `;
                    });
                });
        }

        function abrirModalAddComentario() {
            fecharModal('modal-lista-comentarios'); // Fecha a lista

            document.getElementById('modal-add-produto-nome').textContent = produtoAtualNome + ' / Observações do produto';
            document.getElementById('input-add-produto-id').value = produtoAtualId;
            document.getElementById('modal-add-comentario').style.display = 'flex';
        }

        function excluirComentario(idComentario) {
            if (confirm('Tem certeza que deseja excluir este comentário?')) {
                fetch(`excluir-comentario.php`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `id=${idComentario}`
                }).then(() => {
                    // Recarrega a lista de comentários para o produto atual
                    abrirModalComentarios(produtoAtualId, produtoAtualNome);
                });
            }
        }
        // --- Lógica de Movimentação (Entrada/Saída) ---
        let estoqueAtualMovimentacao = 0;

        function abrirModalMovimentar(id, nome, estoqueAtual) {
            estoqueAtualMovimentacao = parseInt(estoqueAtual);

            document.getElementById('input-mov-produto-id').value = id;
            document.getElementById('modal-mov-subtitle').textContent = `${nome} / ${estoqueAtual} unidades disponíveis`;
            document.getElementById('input-mov-quantidade').value = '';
            document.getElementById('preview-estoque-final').textContent = `${estoqueAtual} unidades`;

            // Reseta para entrada por padrão
            setTipoMovimentacao('entrada');

            document.getElementById('modal-movimentar').style.display = 'flex';
        }

        function setTipoMovimentacao(tipo) {
            document.getElementById('input-mov-tipo').value = tipo;

            const btnEntrada = document.getElementById('btn-tab-entrada');
            const btnSaida = document.getElementById('btn-tab-saida');
            const btnSubmit = document.getElementById('btn-submit-movimentacao');

            if (tipo === 'entrada') {
                btnEntrada.classList.add('active');
                btnSaida.classList.remove('active');
                btnSubmit.textContent = 'Registrar entrada';
            } else {
                btnSaida.classList.add('active');
                btnEntrada.classList.remove('active');
                btnSubmit.textContent = 'Registrar saída';
            }

            calcularEstoque(); // Recalcula caso o usuário troque a aba com um número já digitado
        }

        function calcularEstoque() {
            const inputQtd = document.getElementById('input-mov-quantidade');
            const tipo = document.getElementById('input-mov-tipo').value;
            const preview = document.getElementById('preview-estoque-final');

            let qtd = parseInt(inputQtd.value);
            if (isNaN(qtd)) qtd = 0;

            let estoqueFinal = estoqueAtualMovimentacao;

            if (tipo === 'entrada') {
                estoqueFinal += qtd;
            } else {
                estoqueFinal -= qtd;
                if (estoqueFinal < 0) estoqueFinal = 0; // Evita mostrar negativo no preview
            }

            preview.textContent = `${estoqueFinal} unidades`;
        }
    </script>

</body>

</html>