<?php
$host = 'localhost';
$dbname = 'stockcontrol';
$user = 'root'; // O utilizador padrão do XAMPP costuma ser 'root'
$pass = ''; // A palavra-passe padrão do XAMPP costuma ser vazia

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro na ligação à base de dados: " . $e->getMessage());
}
?>