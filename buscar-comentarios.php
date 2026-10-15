<?php
session_start();
require './conn/conexao.php';

if (isset($_GET['produto_id'])) {
    $stmt = $pdo->prepare("
        SELECT c.*, u.nome, u.nivel_acesso 
        FROM comentarios c 
        JOIN usuarios u ON c.usuario_id = u.id 
        WHERE c.produto_id = ? 
        ORDER BY c.criado_em DESC
    ");
    $stmt->execute([$_GET['produto_id']]);
    $comentarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Adiciona uma flag dizendo se o usuário logado pode deletar aquele comentário específico
    $current_user_id = $_SESSION['usuario_id'] ?? 0;
    $is_admin = ($_SESSION['nivel_acesso'] === 'admin');
    
    $response = [];
    foreach ($comentarios as $com) {
        $com['pode_excluir'] = ($is_admin || $com['usuario_id'] == $current_user_id);
        $response[] = $com;
    }
    
    echo json_encode($response);
}