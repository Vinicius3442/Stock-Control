<?php
session_start();
require './conn/conexao.php';

// Proteção de rota
if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit;
}

$id_usuario = $_SESSION['usuario_id'];
$isAdmin = ($_SESSION['nivel_acesso'] === 'admin');
$mensagem = '';

// Processar envios de formulário
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Atualizar Foto de Perfil
    if (isset($_POST['atualizar_foto']) && isset($_FILES['foto_perfil'])) {
        $arquivo = $_FILES['foto_perfil'];
        if ($arquivo['error'] === UPLOAD_ERR_OK) {
            $extensao = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
            if (in_array($extensao, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                $novoNome = uniqid('user_') . '.' . $extensao;
                if (move_uploaded_file($arquivo['tmp_name'], 'uploads/' . $novoNome)) {
                    $pdo->prepare("UPDATE usuarios SET foto_perfil = ? WHERE id = ?")->execute([$novoNome, $id_usuario]);
                    $mensagem = "<div class='success-banner'><i class='ph ph-check-circle' style='font-size: 1.25rem;'></i> Foto de perfil atualizada!</div>";
                }
            } else {
                $mensagem = "<div class='error-message' style='margin-bottom: 24px;'><i class='ph ph-warning-circle'></i> Formato inválido. Use JPG, PNG ou GIF.</div>";
            }
        }
    }

    // Atualizar Dados Gerais
    elseif (isset($_POST['atualizar_perfil'])) {
        $nome = trim($_POST['nome']);
        $email = trim($_POST['email']);
        try {
            $pdo->prepare("UPDATE usuarios SET nome = ?, email = ? WHERE id = ?")->execute([$nome, $email, $id_usuario]);
            $_SESSION['nome'] = $nome;
            $mensagem = "<div class='success-banner'><i class='ph ph-check-circle' style='font-size: 1.25rem;'></i> Perfil atualizado com sucesso!</div>";
        } catch (PDOException $e) {
            $mensagem = "<div class='error-message' style='margin-bottom: 24px;'><i class='ph ph-warning-circle'></i> Erro: E-mail já em uso.</div>";
        }
    }

    // Atualizar Senha
    elseif (isset($_POST['atualizar_senha'])) {
        $senha_atual = $_POST['senha_atual'];
        $nova_senha = $_POST['nova_senha'];

        $stmt_check = $pdo->prepare("SELECT senha FROM usuarios WHERE id = ?");
        $stmt_check->execute([$id_usuario]);
        $user_db = $stmt_check->fetch();

        if (password_verify($senha_atual, $user_db['senha'])) {
            $senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
            $pdo->prepare("UPDATE usuarios SET senha = ? WHERE id = ?")->execute([$senha_hash, $id_usuario]);
            $mensagem = "<div class='success-banner'><i class='ph ph-shield-check' style='font-size: 1.25rem;'></i> Senha alterada com segurança!</div>";
        } else {
            $mensagem = "<div class='error-message' style='margin-bottom: 24px;'><i class='ph ph-lock-key'></i> A senha atual informada está incorreta.</div>";
        }
    }
}

// Buscar dados do usuário
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->execute([$id_usuario]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

$iniciais = strtoupper(substr($usuario['nome'], 0, 2));
$foto_perfil = $usuario['foto_perfil'];
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>StockControl - Meu Perfil</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
</head>

<body>
    <div class="dashboard-container">
        <!-- Header -->
        <header class="topbar">
            <div class="topbar-left">
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
                    <a href="painel.php">Produtos</a>
                    <a href="movimentacoes.php">Movimentações</a>
                </nav>
            </div>

            <div class="topbar-right">
                <div class="user-profile">
                    <div class="avatar">
                        <?php if (!empty($foto_perfil)): ?>
                            <img src="uploads/<?= htmlspecialchars($foto_perfil) ?>"
                                style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">
                        <?php else: ?>
                            <?= $iniciais ?>
                        <?php endif; ?>
                    </div>
                    <div class="user-info">
                        <span><?= htmlspecialchars($usuario['nome']) ?></span>
                        <span class="user-role"><?= ucfirst($usuario['nivel_acesso']) ?></span>
                    </div>
                </div>
                <a href="logout.php" class="logout-link"><i class="ph ph-sign-out"></i> Sair</a>
            </div>
        </header>

        <main>
            <a href="painel.php" class="action-link" style="color: #A8A8B3; font-size: 0.875rem;">
                <i class="ph ph-arrow-left"></i> Voltar ao painel
            </a>

            <section class="page-header" style="margin-top: 24px;">
                <span class="tag" style="background: none; padding: 0;">CONFIGURAÇÕES DA CONTA</span>
                <h1>Meu Perfil.</h1>
                <p>Gerencie as suas informações pessoais e credenciais de segurança.</p>
            </section>

            <?= $mensagem ?>

            <div class="product-form-container">
                <div class="form-panel">

                    <!-- Sistema Automático de Foto -->
                    <form action="" method="POST" enctype="multipart/form-data"
                        style="display: flex; gap: 16px; align-items: center; margin-bottom: 24px; padding-bottom: 24px; border-bottom: 1px solid #29292E;">
                        <input type="hidden" name="atualizar_foto" value="1">
                        <div class="avatar" style="width: 64px; height: 64px; font-size: 1.5rem;">
                            <?php if (!empty($foto_perfil)): ?>
                                <img src="uploads/<?= htmlspecialchars($foto_perfil) ?>"
                                    style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">
                            <?php else: ?>
                                <?= $iniciais ?>
                            <?php endif; ?>
                        </div>
                        <div>
                            <input type="file" name="foto_perfil" accept="image/*" id="foto_upload"
                                style="display: none;" onchange="this.form.submit()">
                            <button type="button" class="btn-secondary"
                                onclick="document.getElementById('foto_upload').click()"
                                style="padding: 8px 16px; font-size: 0.875rem;">
                                <i class="ph ph-camera"></i> Alterar foto
                            </button>
                        </div>
                    </form>

                    <!-- Resto do Formulário de Dados Gerais -->
                    <form action="" method="POST">
                        <input type="hidden" name="atualizar_perfil" value="1">
                        <div class="form-group">
                            <label><i class="ph ph-user"></i> Nome Completo</label>
                            <input type="text" name="nome" value="<?= htmlspecialchars($usuario['nome']) ?>" required
                                class="form-input"
                                style="width: 100%; padding: 16px; background-color: #121214; border: 1px solid #29292E; border-radius: 8px; color: #FFF;">
                        </div>

                        <div class="form-group">
                            <label><i class="ph ph-envelope-simple"></i> E-mail</label>
                            <input type="email" name="email" value="<?= htmlspecialchars($usuario['email']) ?>" required
                                class="form-input"
                                style="width: 100%; padding: 16px; background-color: #121214; border: 1px solid #29292E; border-radius: 8px; color: #FFF;">
                        </div>

                        <p style="color: #52525B; font-size: 0.75rem; margin-top: 16px;">Conta criada em:
                            <?= date('d/m/Y', strtotime($usuario['criado_em'])) ?></p>

                        <div class="form-actions" style="margin-top: 32px;">
                            <button type="submit" class="btn-primary"
                                style="width: 100%; justify-content: center; display: flex; gap: 8px; align-items: center;">
                                <i class="ph ph-floppy-disk"></i> Salvar alterações
                            </button>
                        </div>
                    </form>
                </div>

                <div class="form-panel">
                    <h3
                        style="display: flex; align-items: center; gap: 8px; border-bottom: 1px solid #29292E; padding-bottom: 16px;">
                        <i class="ph ph-shield-check" style="color: #9D7AFF;"></i> Segurança
                    </h3>
                    <p style="color: #A8A8B3; font-size: 0.875rem; margin: 24px 0;">Atualize a sua palavra-passe
                        regularmente.</p>
                    <form action="" method="POST">
                        <input type="hidden" name="atualizar_senha" value="1">
                        <div class="form-group">
                            <label>Senha Atual</label>
                            <input type="password" name="senha_atual" placeholder="Digite sua senha atual" required
                                class="form-input"
                                style="width: 100%; padding: 16px; background-color: #121214; border: 1px solid #29292E; border-radius: 8px; color: #FFF;">
                        </div>
                        <div class="form-group">
                            <label>Nova Senha</label>
                            <input type="password" name="nova_senha" placeholder="No mínimo 6 caracteres" required
                                minlength="6" class="form-input"
                                style="width: 100%; padding: 16px; background-color: #121214; border: 1px solid #29292E; border-radius: 8px; color: #FFF;">
                        </div>
                        <div class="form-actions" style="margin-top: 32px;">
                            <button type="submit" class="btn-secondary"
                                style="width: 100%; justify-content: center; display: flex; gap: 8px; align-items: center; background-color: #29292E;">
                                <i class="ph ph-lock-key"></i> Alterar Senha
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>

</html>