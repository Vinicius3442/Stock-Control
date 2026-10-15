<?php
session_start();
require './conn/conexao.php';

$erro = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $usuario = $stmt->fetch();

    if ($usuario && password_verify($senha, $usuario['senha'])) {
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['nome'] = $usuario['nome'];
        $_SESSION['nivel_acesso'] = $usuario['nivel_acesso'];
        header("Location: painel.php"); // Redireciona para a tela de produtos depois
        exit;
    } else {
        $erro = 'Senha inválida. Confira e tente novamente.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>StockControl - Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <!-- Painel Esquerdo (Visual do Figma) -->
        <div class="left-panel">
            <div>
                <span class="tag">Organização sem complicação</span>
                <h1>Seu estoque.<br><span>Sob controle.</span></h1>
                <p>Mais espaço para boas ideias.<br>Menos tempo procurando o que falta.</p>
            </div>
            <!-- Substitua pelo caminho da sua imagem da caixa 3D -->
            <img src="caixa_roxa.png" alt="Caixa 3D" class="illustration">
            <span style="color: #52525B; font-size: 0.75rem;">Pequenos detalhes. Grandes diferenças.</span>
        </div>

        <!-- Painel Direito (Formulário) -->
        <div class="right-panel">
            <div class="auth-form">
                <span style="color: #9D7AFF; font-size: 0.75rem; text-transform: uppercase;">Bom ter você por aqui</span>
                <h2>Entre e organize.</h2>
                <p>Acesse sua conta para continuar.</p>

                <form method="POST" action="">
                    <div class="form-group">
                        <label for="email">E-mail</label>
                        <input type="email" name="email" id="email" placeholder="ana@stockcontrol.com" required>
                    </div>

                    <div class="form-group">
                        <label for="senha">Senha</label>
                        <input type="password" name="senha" id="senha" placeholder="********" required>
                    </div>

                    <?php if ($erro): ?>
                        <div class="error-message"><?= $erro ?></div>
                    <?php endif; ?>

                    <button type="submit" class="btn-primary">Entrar &rarr;</button>
                </form>

                <a href="esqueci_senha.php" class="footer-link">Esqueceu a senha?</a>
                <a href="cadastro.php" class="footer-link">Precisa de acesso? Cadastre-se.</a>
            </div>
        </div>
    </div>
</body>
</html>