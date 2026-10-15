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
    $quantidade = (int) $_POST['quantidade'];

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
        <!-- Header Principal Padronizado -->
        <header class="topbar">
            <div class="topbar-left">
                <div class="logo"
                    style="display: flex; align-items: center; gap: 12px; transition: transform 0.3s ease; cursor: pointer;"
                    onclick="window.location.href='painel.php'" onmouseover="this.style.transform='scale(1.05)'"
                    onmouseout="this.style.transform='scale(1)'">
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
                    <a href="movimentacoes.php">Movimentações</a>
                </nav>
            </div>

            <div class="topbar-right">
                <a href="perfil.php" style="text-decoration: none; display: block;">
                    <div class="user-profile" style="transition: transform 0.2s ease; cursor: pointer;"
                        onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                        <div class="avatar"
                            style="background-color: rgba(157, 122, 255, 0.2); color: #9D7AFF; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                            <?php
                            // Busca rápida da foto para o header
                            $stmt_user = $pdo->prepare("SELECT foto_perfil FROM usuarios WHERE id = ?");
                            $stmt_user->execute([$_SESSION['usuario_id']]);
                            $foto_header = $stmt_user->fetchColumn();

                            if (!empty($foto_header)): ?>
                                <img src="uploads/<?= htmlspecialchars($foto_header) ?>"
                                    style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">
                            <?php else: ?>
                                <?= strtoupper(substr($_SESSION['nome'], 0, 2)) ?>
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
            <a href="painel.php" style="color: #A8A8B3; text-decoration: none; font-size: 0.875rem;">&larr; Voltar aos
                produtos</a>

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
                        <input type="text" name="descricao" value="<?= htmlspecialchars($produto['descricao']) ?>"
                            required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Quantidade *</label>
                            <input type="number" name="quantidade" value="<?= $produto['quantidade'] ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Preço (R$) *</label>
                            <!-- Formatando o preço para exibir com vírgula no formulário -->
                            <input type="text" name="preco" value="<?= number_format($produto['preco'], 2, ',', '') ?>"
                                required>
                        </div>
                        <div class="form-group">
                            <label>Status *</label>
                            <select name="status"
                                style="width: 100%; padding: 16px; background: #121214; border: 1px solid #29292E; color: #FFF; border-radius: 8px;">
                                <option value="ativo" <?= $produto['status'] == 'ativo' ? 'selected' : '' ?>>Ativo</option>
                                <option value="inativo" <?= $produto['status'] == 'inativo' ? 'selected' : '' ?>>Inativo
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Adicionar novo comentário (opcional)</label>
                        <!-- Deixamos vazio, se o admin escrever algo, insere na tabela de comentários -->
                        <input type="text" name="comentario" placeholder="Observações sobre esta edição...">
                    </div>

                    <p style="color: #52525B; font-size: 0.75rem; margin-top: 16px;">Criado em:
                        <?= date('d/m/Y H:i', strtotime($produto['criado_em'])) ?>
                    </p>

                    <div class="form-actions">
                        <a href="painel.php" class="btn-secondary">Cancelar</a>
                        <button type="submit" class="btn-primary"
                            style="width: auto; margin: 0; padding: 12px 32px;">Salvar alterações</button>
                    </div>
                </div>

                <!-- Coluna Direita -->
                <!-- Coluna Direita: Imagem do Produto -->
                <div class="form-panel">
                    <h3
                        style="display: flex; align-items: center; gap: 8px; border-bottom: 1px solid #29292E; padding-bottom: 16px;">
                        <i class="ph ph-image" style="color: #9D7AFF;"></i> Imagem do produto
                    </h3>

                    <!-- Caixa de Upload Dinâmica -->
                    <div class="image-upload-box" id="upload-box"
                        onclick="document.getElementById('imagem_produto').click()"
                        style="margin-top: 24px; <?= !empty($produto['imagem']) ? 'border: none; background-color: transparent;' : '' ?>">

                        <?php if (!empty($produto['imagem'])): ?>
                            <img src="uploads/<?= htmlspecialchars($produto['imagem']) ?>"
                                style="width:100%; height:100%; object-fit:cover; border-radius: 8px;">
                        <?php else: ?>
                            <i class="ph ph-upload-simple" style="font-size: 2rem; margin-bottom: 8px;"></i>
                            <span id="upload-text" style="font-size: 0.875rem;">Clique para selecionar a imagem</span>
                        <?php endif; ?>

                    </div>

                    <!-- Input Oculto -->
                    <input type="file" name="imagem" id="imagem_produto" accept="image/*" style="display: none;"
                        onchange="previewImage(this)">

                    <p style="color: #A8A8B3; font-size: 0.75rem; margin-top: 16px;">PNG ou JPG + até 5 MB.</p>
                    <p style="color: #A8A8B3; font-size: 0.75rem; margin-top: 8px;">Uma boa imagem ajuda a
                        encontrar<br>o produto num piscar de olhos.</p>
                </div>
            </form>
        </main>
    </div>

    <!-- Lógica do Preview da Imagem -->
    <script>
        function previewImage(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    const box = document.getElementById('upload-box');
                    box.style.border = 'none';
                    box.style.backgroundColor = 'transparent';
                    box.innerHTML = `<img src="${e.target.result}" style="width:100%; height:100%; object-fit:cover; border-radius: 8px; animation: scaleIn 0.3s ease-out;">`;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
    </form>
    </main>
    </div>
</body>

</html>