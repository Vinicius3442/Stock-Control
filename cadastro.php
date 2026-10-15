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
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="40 30 120 160" class="illustration">
                <!-- Sombra Projetada -->
                <ellipse cx="100" cy="175" rx="65" ry="14" fill="#000000" opacity="0.3" />

                <!-- Face Esquerda (Roxo Escuro) -->
                <polygon points="100,100 100,164 45,132 45,68" fill="#6D28D9" />

                <!-- Face Direita (Roxo Médio) -->
                <polygon points="100,100 155,68 155,132 100,164" fill="#8B5CF6" />

                <!-- Face Superior (Roxo Claro) -->
                <polygon points="100,36 155,68 100,100 45,68" fill="#A78BFA" />

                <!-- Fita (Linha branca/cinza que cruza o topo e desce a lateral) -->
                <polyline points="80,47 135,79.5 135,120" stroke="#F5F3FF" stroke-width="4" fill="none"
                    stroke-linejoin="round" stroke-linecap="round" />

                <!-- Olhinhos (Pontos alinhados na face esquerda) -->
                <circle cx="65" cy="105" r="4.5" fill="#F5F3FF" />
                <circle cx="85" cy="117" r="4.5" fill="#F5F3FF" />
            </svg>
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