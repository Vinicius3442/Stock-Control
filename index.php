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
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
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

                <!-- Olhinhos (Pontos alinhados na face esquerda) — seguem o mouse via JS -->
                <g class="eye-blink">
                    <circle class="box-eye" cx="65" cy="105" r="4.5" fill="#F5F3FF" />
                </g>
                <g class="eye-blink">
                    <circle class="box-eye" cx="85" cy="117" r="4.5" fill="#F5F3FF" />
                </g>
            </svg>
            <span style="color: #52525B; font-size: 0.75rem;">Pequenos detalhes. Grandes diferenças.</span>
        </div>

        <!-- Painel Direito (Formulário) -->
        <div class="right-panel">
            <div class="auth-form">
                <span style="color: #9D7AFF; font-size: 0.75rem; text-transform: uppercase;">Bom ter você por
                    aqui</span>
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

    <!-- Animação: olhinhos da caixa seguem o mouse -->
    <script>
        (function () {
            const svg = document.querySelector('.illustration');
            const olhos = svg ? svg.querySelectorAll('.box-eye') : [];
            if (!olhos.length) return;

            const MAX = 2.6;          // deslocamento máximo do olho (unidades do SVG)
            const INCLINACAO = 32 / 55; // inclinação da face esquerda da caixa (45,68 -> 100,100)
            let mouse = null;
            const atual = [...olhos].map(() => ({ x: 0, y: 0 }));

            window.addEventListener('pointermove', (e) => { mouse = { x: e.clientX, y: e.clientY }; });
            document.addEventListener('pointerleave', () => { mouse = null; });

            function animar() {
                const ctm = svg.getScreenCTM();

                olhos.forEach((olho, i) => {
                    let alvoX = 0, alvoY = 0;

                    if (mouse && ctm) {
                        // Centro do olho na tela
                        const centro = svg.createSVGPoint();
                        centro.x = +olho.getAttribute('cx');
                        centro.y = +olho.getAttribute('cy');
                        const tela = centro.matrixTransform(ctm);

                        const dx = mouse.x - tela.x;
                        const dy = mouse.y - tela.y;
                        const dist = Math.hypot(dx, dy) || 1;
                        const forca = Math.min(dist / 200, 1) * MAX; // olha menos quando o mouse está bem perto

                        // Projeta a direção na face inclinada para parecer "desenhado" na caixa
                        const ux = dx / dist, uy = dy / dist;
                        alvoX = ux * forca;
                        alvoY = (uy + ux * INCLINACAO) * forca * 0.85;
                    }

                    // Suaviza o movimento (interpolação)
                    atual[i].x += (alvoX - atual[i].x) * 0.15;
                    atual[i].y += (alvoY - atual[i].y) * 0.15;
                    olho.setAttribute('transform', `translate(${atual[i].x.toFixed(2)} ${atual[i].y.toFixed(2)})`);
                });

                requestAnimationFrame(animar);
            }

            requestAnimationFrame(animar);
        })();
    </script>
</body>

</html>