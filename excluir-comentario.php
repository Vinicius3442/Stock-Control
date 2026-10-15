<?php
session_start();
require './conn/conexao.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['usuario_id'])) {
    $id = $_POST['id'];
    
    // Verifica se o usuário é admin ou o autor do comentário para permitir exclusão
    $stmt = $pdo->prepare("SELECT usuario_id FROM comentarios WHERE id = ?");
    $stmt->execute([$id]);
    $comentario = $stmt->fetch();

    if ($comentario && ($_SESSION['nivel_acesso'] === 'admin' || $comentario['usuario_id'] == $_SESSION['usuario_id'])) {
        $del = $pdo->prepare("DELETE FROM comentarios WHERE id = ?");
        $del->execute([$id]);
    }
}