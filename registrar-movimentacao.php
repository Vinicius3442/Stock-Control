<?php
session_start();
require './conn/conexao.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['usuario_id'])) {
    $produto_id = (int)$_POST['produto_id'];
    $tipo = $_POST['tipo_movimento']; // 'entrada' ou 'saida'
    $quantidade = (int)$_POST['quantidade'];
    $observacao = trim($_POST['observacao']);

    if ($quantidade > 0) {
        try {
            $pdo->beginTransaction();

            // 1. Atualizar o estoque do produto
            if ($tipo === 'entrada') {
                $stmt = $pdo->prepare("UPDATE produtos SET quantidade = quantidade + ? WHERE id = ?");
            } else {
                // Para saída, garante que não fica negativo (opcional, dependendo da sua regra de negócio)
                $stmt = $pdo->prepare("UPDATE produtos SET quantidade = GREATEST(0, quantidade - ?) WHERE id = ?");
            }
            $stmt->execute([$quantidade, $produto_id]);

            // 2. Registrar no histórico de movimentações
            $stmt_mov = $pdo->prepare("INSERT INTO movimentacoes (produto_id, usuario_id, tipo, quantidade, observacao) VALUES (?, ?, ?, ?, ?)");
            $stmt_mov->execute([$produto_id, $_SESSION['usuario_id'], $tipo, $quantidade, $observacao]);

            $pdo->commit();
            $_SESSION['mensagem_sucesso'] = "✓ Movimentação de {$tipo} registrada com sucesso!";
        } catch (Exception $e) {
            $pdo->rollBack();
            // Lidar com erro se necessário
        }
    }
}
header("Location: painel.php");
exit;