<?php
session_start();
require './conn/conexao.php';

// Proteção de rota: apenas administradores podem acessar
if (!isset($_SESSION['usuario_id']) || $_SESSION['nivel_acesso'] !== 'admin') {
    header("Location: painel.php");
    exit;
}

$iniciais = strtoupper(substr($_SESSION['nome'], 0, 2));
$mensagem = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = $_POST['nome'];
    $descricao = $_POST['descricao'];
    $quantidade = (int)$_POST['quantidade'];
    $preco = str_replace(',', '.', $_POST['preco']); // Converte 189,90 para 189.90
    $status = $_POST['status'];
    $comentario = trim($_POST['comentario']);
    
    // Lógica de Upload de Imagem
    $nome_imagem = null;
    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] == 0) {
        $extensao = strtolower(pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION));
        $tipos_permitidos = ['png', 'jpg', 'jpeg'];
        
        if (in_array($extensao, $tipos_permitidos)) {
            $nome_imagem = uniqid() . '.' . $extensao;
            $diretorio = 'uploads/';
            
            if (!is_dir($diretorio)) {
                mkdir($diretorio, 0777, true);
            }
            move_uploaded_file($_FILES['imagem']['tmp_name'], $diretorio . $nome_imagem);
        } else {
            $mensagem = "<div class='error-message'>Formato de imagem inválido. Use PNG ou JPG.</div>";
        }
    }

    // Se não houve erro com a imagem, prossegue com o insert
    if (empty($mensagem)) {
        try {
            $pdo->beginTransaction(); 

            // Inserir Produto
            $stmt = $pdo->prepare("INSERT INTO produtos (nome, descricao, quantidade, preco, status, imagem) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$nome, $descricao, $quantidade, $preco, $status, $nome_imagem]);
            
            $produto_id = $pdo->lastInsertId();

            // Inserir Comentário Inicial (se preenchido)
            if (!empty($comentario)) {
                $stmt_com = $pdo->prepare("INSERT INTO comentarios (produto_id, usuario_id, comentario) VALUES (?, ?, ?)");
                $stmt_com->execute([$produto_id, $_SESSION['usuario_id'], $comentario]);
            }

            $pdo->commit();
            $mensagem = "<div style='color: #10B981; margin-bottom: 16px; padding: 12px; background: rgba(16, 185, 129, 0.1); border-radius: 8px; border: 1px solid rgba(16, 185, 129, 0.3);'>Produto cadastrado com sucesso!</div>";
        } catch (PDOException $e) {
            $pdo->rollBack();
            $mensagem = "<div class='error-message'>Erro ao cadastrar: " . $e->getMessage() . "</div>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>StockControl - Novo Produto</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Header -->
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
            <a href="painel.php" style="color: #A8A8B3; text-decoration: none; font-size: 0.875rem;">&larr; Voltar aos produtos</a>
            
            <section class="page-header" style="margin-top: 24px;">
                <span class="tag" style="background: none; padding: 0;">SEU ESTOQUE, EM ORDEM.</span>
                <h1>Um novo item por aqui.</h1>
                <p>Preencha os detalhes. A organização começa por aqui.</p>
            </section>

            <?= $mensagem ?>

            <form action="" method="POST" enctype="multipart/form-data" class="product-form-container">
                <!-- Coluna Esquerda -->
                <div class="form-panel">
                    <h3>Informações do produto</h3>
                    
                    <div class="form-group">
                        <label>Nome *</label>
                        <input type="text" name="nome" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Descrição *</label>
                        <input type="text" name="descricao" required>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Quantidade *</label>
                            <input type="number" name="quantidade" required min="0">
                        </div>
                        <div class="form-group">
                            <label>Preço (R$) *</label>
                            <input type="text" name="preco" required placeholder="0,00">
                        </div>
                        <div class="form-group">
                            <label>Status *</label>
                            <select name="status" style="width: 100%; padding: 16px; background: #121214; border: 1px solid #29292E; color: #FFF; border-radius: 8px;">
                                <option value="ativo">Ativo</option>
                                <option value="inativo">Inativo</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Comentário inicial (opcional)</label>
                        <input type="text" name="comentario" placeholder="Ex: Lote inicial recebido...">
                    </div>
                    
                    <p style="color: #52525B; font-size: 0.75rem; margin-top: 16px;">Data de inclusão registrada automaticamente.</p>
                    
                    <div class="form-actions">
                        <a href="painel.php" class="btn-secondary">Cancelar</a>
                        <button type="submit" class="btn-primary" style="width: auto; margin: 0; padding: 12px 32px;">Cadastrar</button>
                    </div>
                </div>

                <!-- Coluna Direita -->
                <div class="form-panel">
                    <h3>Imagem do produto (Opcional)</h3>
                    <div class="image-upload-box" style="cursor: pointer;" onclick="document.getElementById('upload-img').click();">
                        <span style="font-size: 24px; margin-bottom: 8px;">&uarr;</span>
                        <p>Clique para selecionar sua imagem</p>
                    </div>
                    <!-- Input oculto, acionado pelo clique na caixa acima -->
                    <input type="file" id="upload-img" name="imagem" accept="image/png, image/jpeg" style="display: none;">
                    
                    <p style="color: #A8A8B3; font-size: 0.75rem; margin-top: 16px;">PNG ou JPG + até 5 MB.</p>
                    <p style="color: #A8A8B3; font-size: 0.75rem; margin-top: 8px;">Uma boa imagem ajuda a encontrar<br>o produto num piscar de olhos.</p>
                </div>
            </form>
        </main>
    </div>
</body>
</html>