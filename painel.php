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
        <!-- Header Principal Corrigido -->
        <header class="topbar">
            <div class="topbar-left">
                <!-- Novo Logo em SVG (Miniatura da Caixa) -->
                <div class="logo"
                    style="display: flex; align-items: center; gap: 12px; transition: transform 0.3s ease; cursor: pointer;"
                    onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="40 35 120 135" style="width: 28px;">
                        <polygon points="100,100 100,164 45,132 45,68" fill="#6D28D9" />
                        <polygon points="100,100 155,68 155,132 100,164" fill="#8B5CF6" />
                        <polygon points="100,36 155,68 100,100 45,68" fill="#A78BFA" />
                        <polyline points="80,47 135,79.5 135,120" stroke="#F5F3FF" stroke-width="4" fill="none"
                            stroke-linejoin="round" stroke-linecap="round" />
                    </svg>
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
            <!-- Cabeçalho com Métricas Animadas -->
            <section class="page-header">
                <span class="tag" style="background: none; padding: 0;">SEU ESTOQUE, EM ORDEM.</span>
                <h1>Tudo no seu lugar.</h1>
                <p>Acompanhe seus produtos e deixe o estoque fluir.</p>

                <div class="metrics-grid">
                    <div class="metric-card">
                        <i class="ph ph-boxes"
                            style="font-size: 2rem; color: #9D7AFF; margin-bottom: 12px; display: block;"></i>
                        <h3 class="counter" data-target="<?= $totalUnidades ?>">0</h3>
                        <p>unidades em estoque</p>
                    </div>
                    <div class="metric-card">
                        <i class="ph ph-check-circle"
                            style="font-size: 2rem; color: #10B981; margin-bottom: 12px; display: block;"></i>
                        <h3 class="counter" data-target="<?= $produtosAtivos ?>">0</h3>
                        <p>produtos ativos</p>
                    </div>
                    <div class="metric-card card-alert">
                        <i class="ph ph-warning"
                            style="font-size: 2rem; color: #EF4444; margin-bottom: 12px; display: block;"></i>
                        <h3 class="counter" data-target="<?= $estoqueBaixo ?>">0</h3>
                        <p>produto(s) com estoque baixo</p>
                    </div>
                </div>
            </section>

            <!-- Barra de Busca (esquerda) e Ações (direita) -->
            <section class="filters-section">
                <div class="filters-left">
                    <div class="search-container">
                        <i class="ph ph-magnifying-glass search-icon" id="icon-lupa"></i>
                        <i class="ph ph-spinner-gap search-icon loading-spinner" id="icon-spinner"
                            style="display: none;"></i>
                        <input type="text" id="input-pesquisa" class="search-input"
                            placeholder="Buscar por nome ou descrição...">
                    </div>
                </div>

                <div class="filters-right">
                    <button class="toggle-active"><i class="ph ph-funnel"></i> Somente ativos</button>

                    <?php if ($isAdmin): ?>
                        <a href="novo-produto.php" class="btn-add-product">
                            <i class="ph ph-plus"></i> Adicionar produto
                        </a>
                    <?php endif; ?>
                </div>
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

                        <!-- Novo Rodapé do Cartão (Hover Effect) -->
                        <div class="product-footer">
                            <div class="product-stock">
                                <span><?= sprintf("%02d", $produto['quantidade']) ?></span> unidades
                            </div>

                            <!-- Botões visíveis por padrão (As ações principais) -->
                            <div style="display: flex; gap: 8px;">
                                <a href="#"
                                    onclick="abrirModalMovimentar(<?= $produto['id'] ?>, '<?= htmlspecialchars($produto['nome']) ?>', <?= $produto['quantidade'] ?>)"
                                    class="btn-pill-action">
                                    <i class="ph ph-arrows-down-up" style="font-size: 1rem;"></i> Movimentar
                                </a>
                            </div>

                            <!-- Menu de opções extra (Desliza da direita ao fazer Hover) -->
                            <div class="product-actions-hover">
                                <button
                                    onclick="abrirModalComentarios(<?= $produto['id'] ?>, '<?= htmlspecialchars($produto['nome']) ?>')"
                                    class="btn-icon-action" title="Comentários">
                                    <i class="ph ph-chat-text"></i>
                                </button>

                                <?php if (isset($isAdmin) && $isAdmin): ?>
                                    <a href="editar-produto.php?id=<?= $produto['id'] ?>" class="btn-icon-action"
                                        title="Editar">
                                        <i class="ph ph-pencil-simple"></i>
                                    </a>
                                    <button
                                        onclick="abrirModalExcluir(<?= $produto['id'] ?>, '<?= htmlspecialchars($produto['nome']) ?>')"
                                        class="btn-icon-action delete" title="Excluir">
                                        <i class="ph ph-trash"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </section>
            <div id="empty-state"
                style="display: none; text-align: center; padding: 60px 20px; animation: fadeIn 0.3s ease-out; margin: 0 auto; grid-column: 1 / -1;">
                <svg width="100" height="100" viewBox="0 0 100 100" fill="none" stroke-width="6" stroke-linecap="round"
                    stroke-linejoin="round" style="margin-bottom: 16px;">
                    <!-- Rosto (Fundo transparente) -->
                    <circle cx="50" cy="50" r="40" stroke="#EF4444" opacity="0.2" />
                    <!-- Olhinhos -->
                    <circle cx="35" cy="40" r="5" fill="#EF4444" stroke="none" />
                    <circle cx="65" cy="40" r="5" fill="#EF4444" stroke="none" />
                    <!-- Boquinha animada (Vetor Morphing) -->
                    <path d="M 35 65 Q 50 65 65 65" stroke="#EF4444">
                        <animate attributeName="d"
                            values="M 35 65 Q 50 65 65 65; M 35 70 Q 50 45 65 70; M 35 65 Q 50 65 65 65" dur="1.5s"
                            repeatCount="indefinite" />
                    </path>
                </svg>
                <h3 style="color: #EF4444; font-size: 1.25rem; margin-bottom: 8px;">Nenhum produto encontrado</h3>
                <p style="color: #A8A8B3; font-size: 0.875rem;">Sua busca não retornou resultados. Tente procurar por
                    outro
                    termo.</p>
            </div>

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

    <script src="main.js"></script>

</body>

</html>