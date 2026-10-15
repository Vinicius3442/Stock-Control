<?php
session_start();
require './conn/conexao.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit;
}

$isAdmin = ($_SESSION['nivel_acesso'] === 'admin');
$iniciais = strtoupper(substr($_SESSION['nome'], 0, 2));
$stmt_user = $pdo->prepare("SELECT foto_perfil FROM usuarios WHERE id = ?");
$stmt_user->execute([$_SESSION['usuario_id']]);
$foto_perfil = $stmt_user->fetchColumn();

// Buscar produtos para o select do filtro
$stmt_produtos = $pdo->query("SELECT id, nome FROM produtos ORDER BY nome ASC");
$produtos_lista = $stmt_produtos->fetchAll(PDO::FETCH_ASSOC);

// Lógica de filtragem
$filtro_produto_id = isset($_GET['produto_id']) ? (int) $_GET['produto_id'] : '';
$where = "";
$params = [];

if ($filtro_produto_id > 0) {
    $where = "WHERE m.produto_id = ?";
    $params[] = $filtro_produto_id;
}

// Buscar o histórico de movimentações com JOIN para nomes de utilizadores e produtos
$query = "
    SELECT m.*, p.nome AS produto_nome, u.nome AS usuario_nome 
    FROM movimentacoes m 
    JOIN produtos p ON m.produto_id = p.id 
    JOIN usuarios u ON m.usuario_id = u.id 
    $where 
    ORDER BY m.criado_em DESC
";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$movimentacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total_movimentacoes = count($movimentacoes);
?>
<!DOCTYPE html>
<html lang="pt-PT">

<head>
    <meta charset="UTF-8">
    <title>StockControl - Histórico de Movimentações</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
</head>

<body>
    <div class="dashboard-container">
        <!-- Header -->
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
            <section class="page-header" style="margin-top: 24px;">
                <span class="tag" style="background: none; padding: 0;">SEU ESTOQUE, EM ORDEM.</span>
                <h1>O caminho de cada item.</h1>
                <p>Entradas, saídas e responsáveis. Tudo registrado.</p>
            </section>

            <div class="history-actions">
                <form action="" method="GET" class="filter-group">
                    <label for="produto_id">Produto</label>
                    <select name="produto_id" id="produto_id" class="select-filter" onchange="this.form.submit()">
                        <option value="">Todos os produtos</option>
                        <?php foreach ($produtos_lista as $prod): ?>
                            <option value="<?= $prod['id'] ?>" <?= $filtro_produto_id === $prod['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($prod['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>

                <a href="painel.php" class="btn-primary"
                    style="text-decoration: none; padding: 12px 24px; font-weight: 500;">+ Nova movimentação</a>
            </div>

            <div class="history-section">
                <table class="history-table">
                    <thead>
                        <tr>
                            <th>DATA E HORA</th>
                            <th>TIPO</th>
                            <th>QUANTIDADE</th>
                            <th>RESPONSÁVEL</th>
                            <th>OBSERVAÇÃO</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($total_movimentacoes > 0): ?>
                            <?php foreach ($movimentacoes as $mov):
                                $data_formatada = date('d M. Y • H:i', strtotime($mov['criado_em']));
                                // Traduzir meses para português
                                $meses_en = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                                $meses_pt = ['jan', 'fev', 'mar', 'abr', 'mai', 'jun', 'jul', 'ago', 'set', 'out', 'nov', 'dez'];
                                $data_formatada = str_replace($meses_en, $meses_pt, $data_formatada);

                                $sinal = $mov['tipo'] === 'entrada' ? '+ ' : '- ';
                                $tipo_capitalizado = ucfirst($mov['tipo']);
                                ?>
                                <tr>
                                    <td><?= $data_formatada ?></td>
                                    <td><?= $tipo_capitalizado ?></td>
                                    <td><?= $sinal . $mov['quantidade'] ?></td>
                                    <td><?= htmlspecialchars($mov['usuario_nome']) ?></td>
                                    <td><?= htmlspecialchars($mov['observacao']) ?: '-' ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="text-align: center; color: #A8A8B3;">Nenhuma movimentação registada
                                    para este filtro.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <div style="margin-top: 32px; font-size: 0.75rem; color: #52525B;">
                    <?= $total_movimentacoes ?> movimentações • Histórico completo disponível.
                </div>
            </div>
        </main>
    </div>
</body>

</html>