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

// Buscar todos os produtos
$stmt =$pdo->query("SELECT * FROM produtos ORDER BY id DESC");
$produtos =$stmt->fetchAll(PDO::FETCH_ASSOC);

// Cálculos das métricas
$totalUnidades = array_sum(array_column($produtos, 'quantidade'));
$produtosAtivos = count(array_filter($produtos, fn($p) =>$p['status'] === 'ativo'));
$estoqueBaixo = count(array_filter($produtos, fn($p) =>$p['quantidade'] < 10));
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StockControl - Painel de Produtos</title>
    <link rel="stylesheet" href="style.css">
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
                    <a href="#">Movimentações</a>
                </nav>
            </div>
            
            <div class="topbar-right">
                <?php if ($isAdmin): ?>
                    <a href="novo-produto.php" class="btn-new">+ Novo produto</a>
                <?php endif; ?>
                
                <div class="user-profile">
                    <div class="avatar"><?= $iniciais ?></div>
                    <div class="user-info">
                        <span><?= htmlspecialchars($_SESSION['nome']) ?></span>
                        <span class="user-role"><?= ucfirst($_SESSION['nivel_acesso']) ?></span>
                    </div>
                </div>
                <a href="logout.php" class="logout-link">Sair &nearr;</a>
            </div>
        </header>

        <main>
            <!-- Título da Página e Métricas -->
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

            <!-- Barra de Busca e Filtro -->
            <section class="filters-section">
                <div>
                    <span style="font-size: 0.75rem; color: #A8A8B3; display: block; margin-bottom: 8px;">BUSCAR PRODUTO</span>
                    <input type="text" class="search-input" placeholder="Buscar por nome ou descrição">
                </div>
                <button class="toggle-active" style="cursor: pointer;">Somente ativos</button>
            </section>

            <!-- Grid de Produtos Dinâmico -->
            <section class="products-grid">
                <?php foreach ($produtos as$produto): ?>
                <div class="product-card">
                    <div class="product-info">
                        <div class="product-image">
                            <?php if (!empty($produto['imagem'])): ?>
                                <img src="uploads/<?= htmlspecialchars($produto['imagem']) ?>" alt="Img" style="width: 100%; height: 100%; object-fit: cover; border-radius: 8px;">
                            <?php else: ?>
                                <div style="display: flex; align-items: center; justify-content: center; height: 100%; font-size: 24px; color: #9D7AFF;">&#128230;</div>
                            <?php endif; ?>
                        </div>
                        <div class="product-details">
                            <h4><?= htmlspecialchars($produto['nome']) ?></h4>
                            <p><?= htmlspecialchars($produto['descricao']) ?></p>
                            <div class="product-price">R$<?= number_format($produto['preco'], 2, ',', '.') ?></div>
                        </div>
                    </div>
                    
                    <div class="product-footer">
                        <div class="product-stock">
                            <span><?= sprintf("%02d", $produto['quantidade']) ?></span> unidades
                        </div>
                        <div class="product-actions">
                            <a href="#" class="action-link">&darr;&uarr; Movimentar</a>
                            
                            <?php if ($isAdmin): ?>
                                <a href="editar-produto.php?id=<?= $produto['id'] ?>" class="action-link">Editar</a>
                                
                                <form action="excluir-produto.php" method="POST" style="display:inline;" onsubmit="return confirm('Tem certeza que deseja excluir este produto?');">
                                    <input type="hidden" name="id" value="<?= $produto['id'] ?>">
                                    <button type="submit" class="action-link" style="background:none; border:none; cursor:pointer; padding: 0; font-family: inherit;">Excluir</button>
                                </form>
                            <?php endif; ?>
                            
                            <button onclick="abrirModalComentarios(<?= $produto['id'] ?>, '<?= htmlspecialchars($produto['nome']) ?>')" class="action-link" style="background:none; border:none; cursor:pointer; padding: 0; font-family: inherit;">Comentários &nearr;</button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </section>
        </main>
    </div>

    <!-- Estrutura do Modal de Comentários (Oculto por Padrão) -->
    <div class="modal-overlay" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h2>Conversas que organizam.</h2>
                    <p id="modal-produto-nome">Produto / Observações do produto</p>
                </div>
                <button class="btn-close">&times;</button>
            </div>

            <p style="color: #52525B; font-size: 0.75rem; margin-bottom: 16px;">Comentários • Mais recentes primeiro</p>

            <div class="comment-list">
                <!-- Os comentários serão injetados aqui via JavaScript -->
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #29292E; padding-top: 24px;">
                <button class="btn-secondary" id="btn-fechar-modal">Fechar</button>
                <button class="btn-primary" style="width: auto; margin: 0;">+ Adicionar comentário</button>
            </div>
        </div>
    </div>

    <!-- Lógica JavaScript do Modal -->
    <script>
        const modalOverlay = document.querySelector('.modal-overlay');
        const commentList = document.querySelector('.comment-list');
        const btnClose = document.querySelector('.btn-close');
        const btnFechar = document.getElementById('btn-fechar-modal');
        const modalProdutoNome = document.getElementById('modal-produto-nome');

        // Função para abrir o modal e carregar comentários
        function abrirModalComentarios(produtoId, produtoNome) {
            modalProdutoNome.textContent = produtoNome + ' / Observações do produto';
            commentList.innerHTML = '<p style="color: #A8A8B3; text-align: center; padding: 20px;">Carregando...</p>';
            modalOverlay.style.display = 'flex';
            
            fetch(`buscar-comentarios.php?produto_id=${produtoId}`)
                .then(response => response.json())
                .then(data => {
                    commentList.innerHTML = '';
                    
                    if (data.length === 0) {
                        commentList.innerHTML = '<p style="color: #A8A8B3; text-align: center; padding: 20px;">Nenhum comentário registrado ainda.</p>';
                        return;
                    }
                    
                    data.forEach(com => {
                        const iniciais = com.nome.substring(0, 2).toUpperCase();
                        
                        // Tratamento de data/hora
                        const dataComentario = new Date(com.criado_em);
                        const formatada = dataComentario.toLocaleDateString('pt-BR', { day: '2-digit', month: 'short', year: 'numeric' }) + ' • ' + dataComentario.toLocaleTimeString('pt-BR', { hour: '2-digit', minute:'2-digit' });
                        
                        commentList.innerHTML += `
                            <div class="comment-card">
                                <div class="avatar" style="color: ${com.nivel_acesso === 'admin' ? '#9D7AFF' : '#FFF'};">${iniciais}</div>
                                <div class="comment-body">
                                    <h4>${com.nome} <span style="color: #A8A8B3; font-weight: normal;">- ${com.nivel_acesso.charAt(0).toUpperCase() + com.nivel_acesso.slice(1)}</span></h4>
                                    <span style="font-size: 0.75rem; color: #52525B;">${formatada}</span>
                                    <p style="margin-top: 8px; font-size: 0.875rem; line-height: 1.4; color: #E1E1E6;">${com.comentario}</p>
                                </div>
                            </div>
                        `;
                    });
                })
                .catch(err => {
                    commentList.innerHTML = '<p style="color: #EF4444; text-align: center; padding: 20px;">Erro ao carregar comentários.</p>';
                });
        }

        // Funções para fechar o modal
        function fecharModal() {
            modalOverlay.style.display = 'none';
        }

        btnClose.addEventListener('click', fecharModal);
        btnFechar.addEventListener('click', fecharModal);

        // Fechar ao clicar fora do conteúdo do modal
        modalOverlay.addEventListener('click', function(e) {
            if (e.target === modalOverlay) {
                fecharModal();
            }
        });
    </script>
</body>
</html>