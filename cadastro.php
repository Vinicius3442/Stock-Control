<?php
require './conn/conexao.php';
$mensagem = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT); // Criptografia segura

    try {
        $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)");
        $stmt->execute([$nome, $email, $senha]);
        $mensagem = "<div style='color: #10B981; margin-bottom: 20px;'>Cadastro realizado com sucesso! Faça login.</div>";
    } catch (PDOException $e) {
        $mensagem = "<div class='error-message'>Erro: Este e-mail já está cadastrado.</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>StockControl - Cadastro</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="left-panel">
            <div>
                <span class="tag">Junte-se a nós</span>
                <h1>Comece a<br><span>organizar.</span></h1>
                <p>Crie sua conta e tenha o controle total em suas mãos.</p>
            </div>
            <img src="caixa_roxa.png" alt="Caixa 3D" class="illustration">
        </div>

        <div class="right-panel">
            <div class="auth-form">
                <h2>Crie sua conta</h2>
                <p>Preencha os dados abaixo para iniciar.</p>

                <?= $mensagem ?>

                <form method="POST" action="">
                    <div class="form-group">
                        <label for="nome">Nome Completo</label>
                        <input type="text" name="nome" id="nome" required>
                    </div>
                    <div class="form-group">
                        <label for="email">E-mail</label>
                        <input type="email" name="email" id="email" required>
                    </div>
                    <div class="form-group">
                        <label for="senha">Senha</label>
                        <input type="password" name="senha" id="senha" required>
                    </div>

                    <button type="submit" class="btn-primary">Cadastrar &rarr;</button>
                </form>

                <a href="index.php" class="footer-link">Já tem uma conta? Entre aqui.</a>
            </div>
        </div>
    </div>
</body>
</html>