<?php 
$host = "localhost"; 
$port = "3306"; // mudar a porta conforme o xampp
$dbname = "site_animal"; 
$user = "root"; 
$pass = ""; 

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}
?>
