<?php
session_start();
require './conn/conexao.php';

// Apenas administradores podem excluir produtos
if (!isset($_SESSION['usuario_id']) || $_SESSION['nivel_acesso'] !== 'admin') {
    header("Location: painel.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['id'])) {
    $id = (int) $_POST['id'];

    try {
        // Buscar o produto para obter o nome e a imagem antes de excluir
        $stmt = $pdo->prepare("SELECT nome, imagem FROM produtos WHERE id = ?");
        $stmt->execute([$id]);
        $produto = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($produto) {
            // Comentários e movimentações são removidos automaticamente (ON DELETE CASCADE)
            $del = $pdo->prepare("DELETE FROM produtos WHERE id = ?");
            $del->execute([$id]);

            // Remove a imagem do produto da pasta uploads, se existir
            if (!empty($produto['imagem'])) {
                $caminho = 'uploads/' . basename($produto['imagem']);
                if (is_file($caminho)) {
                    unlink($caminho);
                }
            }

            $_SESSION['mensagem_sucesso'] = "✓ Produto \"{$produto['nome']}\" excluído com sucesso!";
        }
    } catch (PDOException $e) {
        // Lidar com erro se necessário
    }
}

header("Location: painel.php");
exit;
