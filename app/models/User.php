<?php
class User {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Cadastra um novo usuário com token de confirmação
     * @param array $dados Dados do usuário [nome, email, senha, telefone, etc...]
     * @return int|false ID do usuário criado ou false em caso de erro
     */
    public function createWithToken(array $dados) {
        try {
            // Validação básica dos campos obrigatórios
            if (empty($dados['email']) || empty($dados['senha']) || empty($dados['nome'])) {
                throw new Exception("Campos obrigatórios faltando!");
            }

            // Gera token e data de expiração (24 horas)
            $token = bin2hex(random_bytes(32));
            $token_expira = date('Y-m-d H:i:s', strtotime('+24 hours'));

            $sql = "INSERT INTO usuarios (
                nome, email, senha, telefone, endereco, cpf_cnpj, 
                cep, bairro, estado, cidade, numero, complemento, token_expira, 
                token_confirmacao, email_confirmado, celular, tipo
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, ?, ?)";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                $dados['nome'],
                $dados['email'],
                password_hash($dados['senha'], PASSWORD_DEFAULT),
                $dados['telefone'] ?? null,  // Permite telefone ser nulo
                $dados['endereco'] ?? null,  // Permite endereço ser nulo
                $dados['cpf_cnpj'] ?? null,  // Corrigido para usar 'cpf_cnpj'
                $dados['cep'] ?? null,       // Permite cep ser nulo
                $dados['bairro'] ?? null,    // Permite bairro ser nulo
                $dados['estado'] ?? null,    // Permite estado ser nulo
                $dados['cidade'] ?? null,    // Permite cidade ser nulo
                $dados['numero'] ?? null,    // Permite numero ser nulo
                $dados['complemento'] ?? null, // Permite complemento ser nulo
                $token_expira,  // token_expira gerado previamente
                $token,         // token_confirmacao gerado previamente
                $dados['celular'] ?? null,    // Permite celular ser nulo
                $dados['tipo'] ?? null        // Permite tipo ser nulo
            ]);

            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            error_log("Erro ao criar usuário: " . $e->getMessage());
            
            // Trata erros específicos de duplicata
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                if (strpos($e->getMessage(), 'email') !== false) {
                    throw new Exception("Este e-mail já está cadastrado!");
                }
                if (strpos($e->getMessage(), 'cpf_cnpj') !== false) {
                    throw new Exception("Este CPF/CNPJ já está cadastrado!");
                }
            }
            
            throw new Exception("Erro ao cadastrar usuário. Tente novamente.");
        }
    }

    /**
     * Verifica e ativa conta com token
     * @param string $token Token de confirmação
     * @return bool Sucesso da operação
     */
    public function verifyEmail($token) {
        try {
            // Primeiro verifica se o token é válido e não expirou
            $sql = "SELECT id FROM usuarios WHERE token_confirmacao = ? AND token_expira > NOW()";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$token]);
            
            if ($stmt->rowCount() === 0) {
                return false;
            }

            // Atualiza o usuário como confirmado
            $sql = "UPDATE usuarios SET 
                    email_confirmado = 1,
                    token_confirmacao = NULL,
                    token_expira = NULL
                    WHERE token_confirmacao = ?";
                    
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([$token]);
        } catch (PDOException $e) {
            error_log("Erro ao verificar email: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verifica se e-mail já está cadastrado
     * @param string $email Email a verificar
     * @return bool
     */
    public function emailExists($email) {
        $sql = "SELECT id FROM usuarios WHERE email = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$email]);
        return (bool) $stmt->fetch();
    }

    /**
     * Verifica credenciais de login
     * @param string $email
     * @param string $senha
     * @return array|false Dados do usuário ou false se inválido
     */
    public function verifyCredentials($email, $senha) {
        $sql = "SELECT * FROM usuarios WHERE email = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($senha, $user['senha'])) {
            return $user;
        }
        return false;
    }

    /**
     * Busca usuário por email
     * @param string $email
     * @return array|false
     */
    public function findByEmail($email) {
        $sql = "SELECT * FROM usuarios WHERE email = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Verifica se CPF/CNPJ já existe
     * @param string $documento
     * @return bool
     */
    public function documentExists($documento) {
        $documento = preg_replace('/[^0-9]/', '', $documento);
        $sql = "SELECT id FROM usuarios WHERE cpf_cnpj = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$documento]);
        return (bool) $stmt->fetch();
    }
}
?>