<?php
require_once __DIR__ . '/../../config.php'; // Ou o arquivo correto que contém routes.php

// Inicia a sessão apenas se ainda não estiver ativa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once dirname(__DIR__) . '/models/User.php'; 
require_once dirname(__DIR__) . '/config/database.php'; 

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST["acao"]) && $_POST['acao'] === 'login'){
    $userModel = new User($pdo);

    // Validações básicas
    if(empty($_POST['email'] || empty($_POST['senha']))){
        $_SESSION['erro'] = "Preencha todos os campos!";
        header("Location: " . PUBLIC_PATH . "/login.php");
        exit;
    }

    // Verifica credenciais
    $usuario = $userModel->verifyCredentials($_POST['email'], $_POST['senha']);

    if($usuario){
        // Login bem-sucedido
        $_SESSION['usuario'] = [
            'id' => $usuario['id'],
            'nome' => $usuario['nome'],
            'email' => $usuario['email'],
            'tipo' => $usuario['tipo']
        ];
        if ($usuario && !$usuario['email_confirmado']) {
            $_SESSION['erro'] = "Confirme seu e-mail antes de fazer login!";
            header("Location: login.php");
            exit;
        }

        $_SESSION['sucesso'] = "Login realizado com sucesso!";
        
        // Redireciona conforme o tipo de usuário
        switch ($usuario['tipo']) {
            case 'adotante':
                header("Location: " . BASE_PATH . "/pagina_adotante.php"); // ex do caminho que ainda será feito
                break;
            case 'doador':
                header("Location: " . BASE_PATH . "/pagina_doador.php"); // ex do caminho que ainda será feito
                break;
            case 'ong':
                header("Location: " . BASE_PATH . "/pagina_ong.php"); // ex do caminho que ainda será feito
                break;
            default:
                header("Location: " . BASE_PATH . "/dashboard.php"); // leva para area de login do admin
                break;
        }
    }else {
        $_SESSION['erro'] = "Email ou senha incorreto";
        header("Location: " . PUBLIC_PATH . "/login.php");
    }
    exit;



    if(isset($_POST['acao']) && $_POST['acao'] === 'logout'){
        // Logout
        session_unset();
        session_destroy();
        $_SESSION['sucesso'] = "Você saiu com segurança!";
        header("Location: " . PUBLIC_PATH . "/login.php");
        exit;
    }
}
?>