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