<?php
session_start();
require './conn/conexao.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['usuario_id'])) {
    $produto_id = $_POST['produto_id'];
    $comentario = trim($_POST['comentario']);

    if (!empty($comentario)) {
        $stmt = $pdo->prepare("INSERT INTO comentarios (produto_id, usuario_id, comentario) VALUES (?, ?, ?)");
        $stmt->execute([$produto_id, $_SESSION['usuario_id'], $comentario]);
        
        $_SESSION['mensagem_sucesso'] = "Sucesso! Comentário cadastrado para o produto.";
    }
}
header("Location: painel.php");
exit;