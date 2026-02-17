<?php

require_once '../config/Config.php';

class Pais {

    private $db;
    public $id;
    public $foto;
    public $nome;
    public $codigo;
    public $moeda;
    public $idioma; // PT, EN, PT-BR
    public $status; // BOOLEAN
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
     * Cria um novo país.
     * @return bool
     */
    public function create() {
        $stmt = $this->db->prepare("
            INSERT INTO paises (foto, nome, codigo, moeda, idioma, status) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        // Tipos de ligação: s=string, i=integer (6 parâmetros)
        // foto, nome, codigo, moeda, idioma (s), status (i)
        $stmt->bind_param(
            "sssssi",
            $this->foto,
            $this->nome,
            $this->codigo,
            $this->moeda,
            $this->idioma,
            $this->status
        );
        return $stmt->execute();
    }

    /**
     * Lê um país pelo ID.
     * @param int $id
     * @return array|null
     */
    public function read($id) {
        $stmt = $this->db->prepare("SELECT * FROM paises WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Atualiza um país.
     * @return bool
     */
    public function update() {
        $query = "
            UPDATE paises 
            SET foto = ?, nome = ?, codigo = ?, moeda = ?, idioma = ?, status = ?
            WHERE id = ?
        ";
        $stmt = $this->db->prepare($query);

        // Tipos: 6 strings/int + id (i) = sssssii
        $stmt->bind_param(
            "sssssii",
            $this->foto,
            $this->nome,
            $this->codigo,
            $this->moeda,
            $this->idioma,
            $this->status,
            $this->id
        );

        return $stmt->execute();
    }

    /**
     * Deleta um país pelo ID.
     * @return bool
     */
    public function delete() {
        $stmt = $this->db->prepare("DELETE FROM paises WHERE id = ?");
        $stmt->bind_param("i", $this->id);
        return $stmt->execute();
    }

    //---------------------------------------------------------
    //  OPERAÇÕES DE LISTAGEM
    //---------------------------------------------------------

    /**
     * Lista todos os países ordenados pelo nome.
     * @return array
     */
    public function listAll() {
        $result = $this->db->query("SELECT * FROM paises ORDER BY nome ASC");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Filtra países por nome, código ou moeda.
     * @param string $search
     * @return array
     */
    public function filter($search) {
        $search = "%{$search}%";
        $stmt = $this->db->prepare("
            SELECT * FROM paises 
            WHERE nome LIKE ? OR codigo LIKE ? OR moeda LIKE ? OR idioma LIKE ?
            ORDER BY nome ASC
        ");
        // Quatro parâmetros de pesquisa (s, s, s, s)
        $stmt->bind_param("ssss", $search, $search, $search, $search);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}