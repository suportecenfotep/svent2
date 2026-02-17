<?php
require_once '../config/Config.php';

class User {

    private $db;
    public $id;
    public $foto;
    public $nome;
    public $identificacao;
    public $telefone;
    public $email;
    public $senha;
    public $nivel;
    public $status;
    public $online;
    public $createdAt;
    public $updatedAt;

    public function __construct() {
        // Inicializa a conexão com o banco de dados.
        $config = new Config();
        $this->db = $config->dbConnect();
    }

    //---------------------------------------------------------
    //  OPERAÇÕES CRUD
    //---------------------------------------------------------

    /**
     * Cria um novo usuário.
     * @return bool
     */
    public function create() {
        $stmt = $this->db->prepare("
            INSERT INTO users (foto, nome, identificacao, telefone, email, senha, nivel, status, online) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $senhaHash = password_hash($this->senha, PASSWORD_BCRYPT);
        // Tipos de ligação: s=string, i=integer (9 parâmetros)
        $stmt->bind_param(
            "sssssssii",
            $this->foto,
            $this->nome,
            $this->identificacao,
            $this->telefone,
            $this->email,
            $senhaHash, // A senha já está hashed
            $this->nivel,
            $this->status,
            $this->online
        );
        return $stmt->execute();
    }

    /**
     * Lê um usuário pelo ID.
     * @param int $id
     * @return array|null
     */
    public function read($id) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Atualiza um usuário. A senha é opcional.
     * @return bool
     */
    public function update() {
        $query = "UPDATE users SET foto = ?, nome = ?, identificacao = ?, telefone = ?, email = ?, nivel = ?, status = ?, online = ?";
        
        if (!empty($this->senha)) {
            $query .= ", senha = ?";
        }

        $query .= " WHERE id = ?";
        $stmt = $this->db->prepare($query);

        if (!empty($this->senha)) {
            $senhaHash = password_hash($this->senha, PASSWORD_BCRYPT);
            // Tipos: 8 strings/ints (s/i) + hash (s) + id (i) = ssssssissi
            $stmt->bind_param(
                "ssssssissi",
                $this->foto,
                $this->nome,
                $this->identificacao,
                $this->telefone,
                $this->email,
                $this->nivel,
                $this->status,
                $this->online,
                $senhaHash,
                $this->id
            );
        } else {
            // Tipos: 8 strings/ints (s/i) + id (i) = ssssssiii (Assumindo que nivel, status e online são tratados como string ou int na sua implementação, e o id é int)
            $stmt->bind_param(
                "ssssssiii",
                $this->foto,
                $this->nome,
                $this->identificacao,
                $this->telefone,
                $this->email,
                $this->nivel,
                $this->status,
                $this->online,
                $this->id
            );
        }

        return $stmt->execute();
    }

    /**
     * Deleta um usuário pelo ID.
     * @return bool
     */
    public function delete() {
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $this->id);
        return $stmt->execute();
    }

    //---------------------------------------------------------
    //  OPERAÇÕES DE LISTAGEM
    //---------------------------------------------------------

    /**
     * Lista todos os usuários ordenados por criação.
     * @return array
     */
    public function listAll() {
        $result = $this->db->query("SELECT * FROM users ORDER BY createdAt DESC");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Filtra usuários por nome, email, identificação ou telefone.
     * @param string $search
     * @return array
     */
    public function filter($search) {
        $search = "%{$search}%";
        $stmt = $this->db->prepare("
            SELECT * FROM users 
            WHERE nome LIKE ? OR email LIKE ? OR identificacao LIKE ? OR telefone LIKE ?
            ORDER BY createdAt DESC
        ");
        $stmt->bind_param("ssss", $search, $search, $search, $search);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    //---------------------------------------------------------
    //  OPERAÇÕES DE AUTENTICAÇÃO
    //---------------------------------------------------------

    /**
     * Realiza o login do usuário e marca como online.
     * @return array|false
     */
    public function login() {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ? AND status = 1 LIMIT 1");
        $stmt->bind_param("s", $this->email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        if ($user && password_verify($this->senha, $user['senha'])) {
            // Atualiza o campo 'online' para 1 (true)
            $update = $this->db->prepare("UPDATE users SET online = 1 WHERE id = ?");
            $update->bind_param("i", $user['id']);
            $update->execute();

            $user['online'] = 1; // Retorna o objeto com o status online atualizado
            return $user;
        }

        return false;
    }

    /**
     * Realiza o logout do usuário (marca como offline).
     * @param int $id O ID do usuário para fazer logout.
     * @return bool
     */
    public function logout($id) {
        $stmt = $this->db->prepare("UPDATE users SET online = 0 WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}