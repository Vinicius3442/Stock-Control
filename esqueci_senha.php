<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>StockControl - Recuperar Senha</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="left-panel">
            <div>
                <span class="tag">Recuperação</span>
                <h1>Esqueceu a<br><span>senha?</span></h1>
                <p>Não se preocupe, vamos te ajudar a recuperar o acesso.</p>
            </div>
        </div>

        <div class="right-panel">
            <div class="auth-form">
                <h2>Recuperar acesso</h2>
                <p>Digite seu e-mail para receber as instruções.</p>

                <form method="POST" action="">
                    <div class="form-group">
                        <label for="email">E-mail cadastrado</label>
                        <input type="email" name="email" id="email" required>
                    </div>

                    <button type="submit" class="btn-primary">Enviar instruções &rarr;</button>
                </form>

                <a href="index.php" class="footer-link">Voltar para o login</a>
            </div>
        </div>
    </div>
</body>
</html>