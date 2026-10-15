<?php
session_start();
require './conn/conexao.php';

// Proteção de rota
if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit;
}

$isAdmin = ($_SESSION['nivel_acesso'] === 'admin');
$iniciais = strtoupper(substr($_SESSION['nome'], 0, 2));

// Buscar produtos
$stmt = $pdo->query("SELECT * FROM produtos ORDER BY id DESC");
$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Cálculos de métricas rápidos
$totalUnidades = array_sum(array_column($produtos, 'quantidade'));
$produtosAtivos = count(array_filter($produtos, fn($p) => $p['status'] === 'ativo'));
$estoqueBaixo = count(array_filter($produtos, fn($p) => $p['quantidade'] < 10));
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>StockControl - Produtos</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Header -->
        <header class="topbar">
            <div class="topbar-left">
                <div class="logo">
                    <!-- Insira sua logo aqui -->
                    <strong>StockControl</strong>
                </div>
                <nav class="nav-links">
                    <a href="painel.php" class="active">Produtos</a>
                    <a href="#">Movimentações</a>
                </nav>
            </div>
            
            <div class="topbar-right">
                <?php if ($isAdmin): ?>
                    <a href="#" class="btn-new">+ Novo produto</a>
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
            <!-- Título e Métricas -->
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

            <!-- Buscas e Filtros -->
            <section class="filters-section">
                <div>
                    <span style="font-size: 0.75rem; color: #A8A8B3; display: block; margin-bottom: 8px;">BUSCAR PRODUTO</span>
                    <input type="text" class="search-input" placeholder="Buscar por nome ou descrição">
                </div>
                <button class="toggle-active">Somente ativos</button>
            </section>

            <!-- Grid -->
            <section class="products-grid">
                <?php foreach ($produtos as $produto): ?>
                <div class="product-card">
                    <div class="product-info">
                        <div class="product-image"></div>
                        <div class="product-details">
                            <h4><?= htmlspecialchars($produto['nome']) ?></h4>
                            <p><?= htmlspecialchars($produto['descricao']) ?></p>
                            <div class="product-price">R$ <?= number_format($produto['preco'], 2, ',', '.') ?></div>
                        </div>
                    </div>
                    
                    <div class="product-footer">
                        <div class="product-stock">
                            <span><?= sprintf("%02d", $produto['quantidade']) ?></span> unidades
                        </div>
                        <div class="product-actions">
                            <a href="#" class="action-link">&darr;&uarr; Movimentar</a>
                            
                            <?php if ($isAdmin): ?>
                                <a href="#" class="action-link">Editar</a>
                                <a href="#" class="action-link">Excluir</a>
                            <?php endif; ?>
                            
                            <a href="#" class="action-link">Comentários &nearr;</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </section>
        </main>
    </div>
</body>
</html>