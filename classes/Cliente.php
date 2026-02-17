<?php
require_once '../config/Config.php';

class Cliente {

    private $db;

    public $id;
    public $foto;
    public $nome;
    public $telefone;
    public $email;
    public $senha;
    public $endereco;

    // 📍 Localização (APENAS PAÍS)
    public $pais_id;

    // Status
    public $status; // 1 = Ativo, 0 = Inativo
    public $online; // 1 = Online, 0 = Offline

    public $createdAt;
    public $updatedAt;

    public function __construct() {
        $config = new Config();
        $this->db = $config->dbConnect();
    }

    //---------------------------------------------------------
    //  CRUD - CLIENTES
    //---------------------------------------------------------

    /**
     * Cria um novo cliente
     */
    public function create() {
        $stmt = $this->db->prepare("
            INSERT INTO clientes (
                foto, nome, telefone, email, senha, endereco,
                pais_id,
                status, online
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $senhaHash = password_hash($this->senha, PASSWORD_BCRYPT);

        // 6 strings + 3 inteiros = ssssssiii
        $stmt->bind_param(
            "ssssssiii",
            $this->foto,
            $this->nome,
            $this->telefone,
            $this->email,
            $senhaHash,
            $this->endereco,
            $this->pais_id,
            $this->status,
            $this->online
        );

        return $stmt->execute();
    }

    /**
     * Lê um cliente pelo ID
     */
    public function read($id) {
        $stmt = $this->db->prepare("
            SELECT 
                c.*,
                p.nome AS pais_nome
            FROM clientes c
            LEFT JOIN paises p ON c.pais_id = p.id
            WHERE c.id = ?
        ");

        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Atualiza dados do cliente (exceto senha e foto)
     */
    public function update() {
        $stmt = $this->db->prepare("
            UPDATE clientes SET
                nome = ?,
                telefone = ?,
                email = ?,
                endereco = ?,
                status = ?,
                online = ?,
                pais_id = ?
            WHERE id = ?
        ");

        // 4 strings + 4 inteiros = ssssiiii
        $stmt->bind_param(
            "ssssiiii",
            $this->nome,
            $this->telefone,
            $this->email,
            $this->endereco,
            $this->status,
            $this->online,
            $this->pais_id,
            $this->id
        );

        return $stmt->execute();
    }

    /**
     * Deleta cliente
     */
    public function delete() {
        $stmt = $this->db->prepare("DELETE FROM clientes WHERE id = ?");
        $stmt->bind_param("i", $this->id);
        return $stmt->execute();
    }

    //---------------------------------------------------------
    //  LISTAGEM
    //---------------------------------------------------------

    /**
     * Lista todos os clientes
     */
    public function listAll() {
        $query = "
            SELECT
                c.id, c.foto, c.nome, c.telefone, c.email, c.endereco,
                c.online, c.status, c.createdAt,
                p.nome AS pais_nome
            FROM clientes c
            LEFT JOIN paises p ON c.pais_id = p.id
            ORDER BY c.createdAt DESC
        ";

        $result = $this->db->query($query);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Filtra clientes
     */
    public function filter($search) {
        $search = "%{$search}%";
        $stmt = $this->db->prepare("
            SELECT * FROM clientes
            WHERE nome LIKE ? OR email LIKE ? OR telefone LIKE ?
            ORDER BY createdAt DESC
        ");
        $stmt->bind_param("sss", $search, $search, $search);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    //---------------------------------------------------------
    //  AUTENTICAÇÃO
    //---------------------------------------------------------

    /**
     * Login
     */
    public function login() {
        $stmt = $this->db->prepare("
            SELECT * FROM clientes
            WHERE email = ? AND status = 1
            LIMIT 1
        ");

        $stmt->bind_param("s", $this->email);
        $stmt->execute();
        $cliente = $stmt->get_result()->fetch_assoc();

        if ($cliente && password_verify($this->senha, $cliente['senha'])) {

            $update = $this->db->prepare("
                UPDATE clientes SET online = 1 WHERE id = ?
            ");
            $update->bind_param("i", $cliente['id']);
            $update->execute();

            unset($cliente['senha']);
            $cliente['online'] = 1;

            return $cliente;
        }

        return false;
    }

    /**
     * Logout
     */
    public function logout($id) {
        $stmt = $this->db->prepare("
            UPDATE clientes SET online = 0 WHERE id = ?
        ");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    /**
     * Atualiza senha
     */
    public function updatePassword($id, $novaSenha) {
        $senhaHash = password_hash($novaSenha, PASSWORD_BCRYPT);
        $stmt = $this->db->prepare("
            UPDATE clientes SET senha = ? WHERE id = ?
        ");
        $stmt->bind_param("si", $senhaHash, $id);
        return $stmt->execute();
    }

    /**
     * Atualiza foto
     */
    public function updateFoto($id, $fotoPath) {
        $stmt = $this->db->prepare("
            UPDATE clientes SET foto = ? WHERE id = ?
        ");
        $stmt->bind_param("si", $fotoPath, $id);
        return $stmt->execute();
    }
}
