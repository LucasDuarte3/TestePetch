<?php
require_once __DIR__ . '/../../vendor/autoload.php';

class MailService {
    private $mail;

    public function __construct() {
        $this->mail = new PHPMailer\PHPMailer\PHPMailer(true);
        
        // Configurações do SMTP (ajuste com seus dados)
        $this->mail->isSMTP();
        $this->mail->Host = 'smtp.gmail.com';
        $this->mail->SMTPAuth = true;
        $this->mail->Username = 'senaclpoo@gmail.com'; // Insira seu endereço de e-mail do Gmail
        $this->mail->Password = 'oormdbnavvkuiqgl';// Insira sua senha do Gmail
        $this->mail->SMTPSecure = 'tls';
        $this->mail->Port = 587;
        $this->mail->CharSet = 'UTF-8';
    }

    public function sendConfirmationEmail($toEmail, $toName, $confirmationUrl) {
        try {
            $this->mail->setFrom('seuemail@gmail.com', 'Petch - Confirmação de Cadastro');
            $this->mail->addAddress($toEmail, $toName);
            $this->mail->isHTML(true);
            $this->mail->Subject = 'Confirme seu cadastro no Petch';
            
            $this->mail->Body = "
                <h1>Olá, $toName!</h1>
                <p>Obrigado por se cadastrar no Petch!</p>
                <p>Para ativar sua conta, clique no link abaixo:</p>
                <p><a href='$confirmationUrl' style='background: #4CAF50; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;'>Confirmar E-mail</a></p>
                <p>Ou copie este link: $confirmationUrl</p>
                <p>Este link expira em 24 horas.</p>
                
            ";
            
            $this->mail->AltBody = "Olá $toName!\n\nConfirme seu cadastro acessando: $confirmationUrl";
            
            return $this->mail->send();
        } catch (Exception $e) {
            error_log("Erro ao enviar e-mail: " . $e->getMessage());
            return false;
        }
    }
}
?>