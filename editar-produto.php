<?php
session_start();
require './conn/conexao.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['nivel_acesso'] !== 'admin') {
    header("Location: painel.php");
    exit;
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: painel.php");
    exit;
}

$id_produto = $_GET['id'];
$mensagem = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = $_POST['nome'];
    $descricao = $_POST['descricao'];
    $quantidade = (int)$_POST['quantidade'];
    
    // Converter formato de preço do Brasil (vírgula) para formato da base de dados (ponto)
    $preco = str_replace(',', '.', $_POST['preco']); 
    $status = $_POST['status'];
    $novo_comentario = $_POST['comentario']; // Comentário opcional

    try {
        // Atualizar os dados do produto
        $stmt = $pdo->prepare("UPDATE produtos SET nome = ?, descricao = ?, quantidade = ?, preco = ?, status = ? WHERE id = ?");
        $stmt->execute([$nome, $descricao, $quantidade, $preco, $status, $id_produto]);

        // Se o administrador adicionou um novo comentário durante a edição, inserimos na tabela de comentários
        if (!empty(trim($novo_comentario))) {
            $stmt_comentario = $pdo->prepare("INSERT INTO comentarios (produto_id, usuario_id, comentario) VALUES (?, ?, ?)");
            $stmt_comentario->execute([$id_produto, $_SESSION['usuario_id'], $novo_comentario]);
        }

        $mensagem = "<div style='color: #10B981; margin-bottom: 16px; padding: 12px; background: rgba(16, 185, 129, 0.1); border-radius: 8px;'>Produto atualizado com sucesso!</div>";
    } catch (PDOException $e) {
        $mensagem = "<div class='error-message'>Erro ao atualizar o produto: " . $e->getMessage() . "</div>";
    }
}

// Lógica para Buscar os Dados do Produto (GET)
$stmt = $pdo->prepare("SELECT * FROM produtos WHERE id = ?");
$stmt->execute([$id_produto]);
$produto = $stmt->fetch(PDO::FETCH_ASSOC);

// Se o produto não existir, redirecionar
if (!$produto) {
    header("Location: painel.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>StockControl - Editar Produto</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
</head>
<body>
    <div class="dashboard-container">
        <!-- O Topbar do painel.php deve ser incluído aqui (pode usar include 'topbar.php' se separar em ficheiros) -->
        
        <main>
            <a href="painel.php" style="color: #A8A8B3; text-decoration: none; font-size: 0.875rem;">&larr; Voltar aos produtos</a>
            
            <section class="page-header" style="margin-top: 24px;">
                <span class="tag" style="background: none; padding: 0;">SEU ESTOQUE, EM ORDEM.</span>
                <h1>Editar produto</h1>
                <p>Altere os detalhes do produto selecionado.</p>
            </section>

            <?= $mensagem ?>

            <!-- O action vazio submete para a própria página -->
            <form action="" method="POST" enctype="multipart/form-data" class="product-form-container">
                <!-- Coluna Esquerda -->
                <div class="form-panel">
                    <h3>Informações do produto</h3>
                    
                    <div class="form-group">
                        <label>Nome *</label>
                        <!-- Injetando o valor da base de dados -->
                        <input type="text" name="nome" value="<?= htmlspecialchars($produto['nome']) ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Descrição *</label>
                        <input type="text" name="descricao" value="<?= htmlspecialchars($produto['descricao']) ?>" required>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Quantidade *</label>
                            <input type="number" name="quantidade" value="<?= $produto['quantidade'] ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Preço (R$) *</label>
                            <!-- Formatando o preço para exibir com vírgula no formulário -->
                            <input type="text" name="preco" value="<?= number_format($produto['preco'], 2, ',', '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Status *</label>
                            <select name="status" style="width: 100%; padding: 16px; background: #121214; border: 1px solid #29292E; color: #FFF; border-radius: 8px;">
                                <option value="ativo" <?= $produto['status'] == 'ativo' ? 'selected' : '' ?>>Ativo</option>
                                <option value="inativo" <?= $produto['status'] == 'inativo' ? 'selected' : '' ?>>Inativo</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Adicionar novo comentário (opcional)</label>
                        <!-- Deixamos vazio, se o admin escrever algo, insere na tabela de comentários -->
                        <input type="text" name="comentario" placeholder="Observações sobre esta edição...">
                    </div>
                    
                    <p style="color: #52525B; font-size: 0.75rem; margin-top: 16px;">Criado em: <?= date('d/m/Y H:i', strtotime($produto['criado_em'])) ?></p>
                    
                    <div class="form-actions">
                        <a href="painel.php" class="btn-secondary">Cancelar</a>
                        <button type="submit" class="btn-primary" style="width: auto; margin: 0; padding: 12px 32px;">Salvar alterações</button>
                    </div>
                </div>

                <!-- Coluna Direita -->
                <div class="form-panel">
                    <h3>Imagem do produto *</h3>
                    <div class="image-preview">
                        <?php if (!empty($produto['imagem'])): ?>
                            <img src="uploads/<?= htmlspecialchars($produto['imagem']) ?>" alt="Imagem do produto">
                        <?php else: ?>
                            <div style="font-size: 64px; color: #9D7AFF;">&#128230;</div> 
                        <?php endif; ?>
                    </div>
                    <button type="button" class="btn-secondary" style="width: 100%;">Trocar imagem</button>
                    <p style="color: #A8A8B3; font-size: 0.75rem; margin-top: 16px;">PNG ou JPG + até 5 MB.</p>
                    <p style="color: #A8A8B3; font-size: 0.75rem; margin-top: 8px;">Uma boa imagem ajuda a encontrar<br>o produto num piscar de olhos.</p>
                </div>
            </form>
        </main>
    </div>
</body>
</html>