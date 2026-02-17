<?php

require_once '../config/Config.php';

class Categoria {

    private $db;
    public $id;
    public $foto;
    public $nome;
    public $descricao;
    public $status; // TINYINT(1)
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
     * Cria uma nova categoria.
     * @return bool
     */
    public function create() {
        $stmt = $this->db->prepare("
            INSERT INTO categorias (foto, nome, descricao, status) 
            VALUES (?, ?, ?, ?)
        ");
        
        // Tipos de ligação: s=string (foto), s=string (nome), s=string (descricao), i=integer (status)
        $stmt->bind_param(
            "sssi",
            $this->foto,
            $this->nome,
            $this->descricao,
            $this->status
        );
        
        return $stmt->execute();
    }

    /**
     * Lê uma categoria pelo ID.
     * @param int $id
     * @return array|null
     */
    public function read($id) {
        $stmt = $this->db->prepare("SELECT * FROM categorias WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Atualiza uma categoria existente.
     * @return bool
     */
    public function update() {
        $query = "
            UPDATE categorias 
            SET foto = ?, nome = ?, descricao = ?, status = ?
            WHERE id = ?
        ";
        $stmt = $this->db->prepare($query);

        // Tipos: s (foto), s (nome), s (descricao), i (status), i (id)
        $stmt->bind_param(
            "sssii",
            $this->foto,
            $this->nome,
            $this->descricao,
            $this->status,
            $this->id
        );

        return $stmt->execute();
    }

    /**
     * Deleta uma categoria pelo ID.
     * @return bool
     */
    public function delete() {
        $stmt = $this->db->prepare("DELETE FROM categorias WHERE id = ?");
        $stmt->bind_param("i", $this->id);
        return $stmt->execute();
    }

    //---------------------------------------------------------
    //  OPERAÇÕES DE LISTAGEM E FILTRO
    //---------------------------------------------------------

    /**
     * Lista todas as categorias ordenadas pelo nome.
     * @return array
     */
    public function listAll() {
        $result = $this->db->query("SELECT * FROM categorias ORDER BY nome ASC");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Lista no máximo as 6 primeiras categorias ativas, ordenadas.
     * Útil para exibição na página inicial ou menus principais.
     * @return array
     */
    public function listSix() {
        $query = "
            SELECT * FROM categorias
            WHERE status = 1  /* Assumindo 1 = ativo */
            ORDER BY nome ASC
            LIMIT 6
        ";
        $result = $this->db->query($query);
        return $result->fetch_all(MYSQLI_ASSOC);
    }


    /**
     * Filtra categorias por nome ou descrição.
     * @param string $search
     * @return array
     */
    public function filter($search) {
        // Prepara o termo de pesquisa para a cláusula LIKE
        $searchPattern = "%{$search}%";
        
        $stmt = $this->db->prepare("
            SELECT * FROM categorias 
            WHERE nome LIKE ? OR descricao LIKE ?
            ORDER BY nome ASC
        ");
        
        // Dois parâmetros de pesquisa (s, s)
        $stmt->bind_param("ss", $searchPattern, $searchPattern);
        $stmt->execute();
        
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}