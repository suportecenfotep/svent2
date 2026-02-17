<?php

require_once '../config/Config.php';

class Regiao {

    private $db;
    public $id;
    public $pais_id; // Chave estrangeira para a tabela 'paises'
    public $nome;
    public $status; // BOOLEAN (1 para Ativo, 0 para Inativo)
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
     * Cria uma nova região.
     * @return bool
     */
    public function create() {
        $stmt = $this->db->prepare("
            INSERT INTO regioes (pais_id, nome, status) 
            VALUES (?, ?, ?)
        ");
        // Tipos de ligação: i=integer, s=string, i=integer (3 parâmetros)
        $stmt->bind_param(
            "isi",
            $this->pais_id,
            $this->nome,
            $this->status
        );
        return $stmt->execute();
    }

    /**
     * Lê uma região pelo ID.
     * @param int $id
     * @return array|null
     */
    public function read($id) {
        // Inclui o nome do país para facilitar a exibição na UI
        $stmt = $this->db->prepare("
            SELECT r.*, p.nome as nome_pais
            FROM regioes r
            JOIN paises p ON r.pais_id = p.id
            WHERE r.id = ?
        ");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Atualiza uma região.
     * @return bool
     */
    public function update() {
        $query = "
            UPDATE regioes 
            SET pais_id = ?, nome = ?, status = ?
            WHERE id = ?
        ";
        $stmt = $this->db->prepare($query);

        // Tipos: i, s, i, i
        $stmt->bind_param(
            "isii",
            $this->pais_id,
            $this->nome,
            $this->status,
            $this->id
        );

        return $stmt->execute();
    }

    /**
     * Deleta uma região pelo ID.
     * @return bool
     */
    public function delete() {
        $stmt = $this->db->prepare("DELETE FROM regioes WHERE id = ?");
        $stmt->bind_param("i", $this->id);
        return $stmt->execute();
    }

    //---------------------------------------------------------
    //  OPERAÇÕES DE LISTAGEM
    //---------------------------------------------------------

    /**
     * Lista todas as regiões, com o nome do país, ordenadas pelo nome.
     * @return array
     */
    public function listAll() {
        $query = "
            SELECT r.*, p.nome as nome_pais
            FROM regioes r
            JOIN paises p ON r.pais_id = p.id
            ORDER BY r.nome ASC
        ";
        $result = $this->db->query($query);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Lista todas as regiões pertencentes a um país específico.
     * @param int $paisId O ID do país.
     * @return array
     */
    public function listByPais($paisId) {
        $stmt = $this->db->prepare("
            SELECT r.*
            FROM regioes r
            WHERE r.pais_id = ?
            ORDER BY r.nome ASC
        ");
        $stmt->bind_param("i", $paisId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Filtra regiões por nome.
     * @param string $search
     * @return array
     */
    public function filter($search) {
        $search = "%{$search}%";
        $stmt = $this->db->prepare("
            SELECT r.*, p.nome as nome_pais
            FROM regioes r
            JOIN paises p ON r.pais_id = p.id
            WHERE r.nome LIKE ? OR p.nome LIKE ?
            ORDER BY r.nome ASC
        ");
        // Filtra pelo nome da região OU pelo nome do país
        $stmt->bind_param("ss", $search, $search);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}