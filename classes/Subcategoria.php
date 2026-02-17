<?php

require_once '../config/Config.php';

/**
 * Classe que gerencia a entidade Subcategoria.
 * Inclui lógica para CRUD e métodos de listagem e filtro.
 */
class Subcategoria {

    private $db;
    public $id;
    public $categoria_id; // Chave Estrangeira para a tabela 'categorias'
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
     * Cria uma nova subcategoria.
     * @return bool
     */
    public function create() {
        $stmt = $this->db->prepare("
            INSERT INTO subcategorias (categoria_id, nome, descricao, status) 
            VALUES (?, ?, ?, ?)
        ");
        
        // Tipos de ligação: i=integer (categoria_id), s=string (nome), s=string (descricao), i=integer (status)
        $stmt->bind_param(
            "issi",
            $this->categoria_id,
            $this->nome,
            $this->descricao,
            $this->status
        );
        
        return $stmt->execute();
    }

    /**
     * Lê uma subcategoria pelo ID.
     * @param int $id
     * @return array|null
     */
    public function read($id) {
        $stmt = $this->db->prepare("
            SELECT s.*, c.nome as categoria_nome 
            FROM subcategorias s 
            JOIN categorias c ON s.categoria_id = c.id
            WHERE s.id = ?
        ");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Atualiza uma subcategoria existente.
     * @return bool
     */
    public function update() {
        $query = "
            UPDATE subcategorias 
            SET categoria_id = ?, nome = ?, descricao = ?, status = ?
            WHERE id = ?
        ";
        $stmt = $this->db->prepare($query);

        // Tipos: i (categoria_id), s (nome), s (descricao), i (status), i (id)
        $stmt->bind_param(
            "issii",
            $this->categoria_id,
            $this->nome,
            $this->descricao,
            $this->status,
            $this->id
        );

        return $stmt->execute();
    }

    /**
     * Deleta uma subcategoria pelo ID.
     * @return bool
     */
    public function delete() {
        $stmt = $this->db->prepare("DELETE FROM subcategorias WHERE id = ?");
        $stmt->bind_param("i", $this->id);
        return $stmt->execute();
    }

    //---------------------------------------------------------
    //  OPERAÇÕES DE LISTAGEM E FILTRO
    //---------------------------------------------------------

    /**
     * Lista todas as subcategorias, incluindo o nome da categoria.
     * @return array
     */
    public function listAll() {
        $query = "
            SELECT 
                s.*, 
                c.nome AS categoria_nome 
            FROM subcategorias s
            JOIN categorias c ON s.categoria_id = c.id
            ORDER BY c.nome ASC, s.nome ASC
        ";
        $result = $this->db->query($query);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    /**
     * Lista subcategorias por ID de Categoria (listByCategoria).
     * @param int $categoria_id O ID da categoria pai.
     * @return array
     */
    public function listByCategoria($categoria_id) {
        $stmt = $this->db->prepare("
            SELECT 
                s.*, 
                c.nome AS categoria_nome
            FROM subcategorias s
            JOIN categorias c ON s.categoria_id = c.id
            WHERE s.categoria_id = ?
            ORDER BY s.nome ASC
        ");
        $stmt->bind_param("i", $categoria_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Filtra subcategorias por nome ou descrição, e inclui o nome da categoria.
     * @param string $search
     * @return array
     */
    public function filter($search) {
        // Prepara o termo de pesquisa para a cláusula LIKE
        $searchPattern = "%{$search}%";
        
        $stmt = $this->db->prepare("
            SELECT 
                s.*, 
                c.nome AS categoria_nome
            FROM subcategorias s
            JOIN categorias c ON s.categoria_id = c.id
            WHERE s.nome LIKE ? 
               OR s.descricao LIKE ?
               OR c.nome LIKE ?
            ORDER BY s.nome ASC
        ");
        
        // Três parâmetros de pesquisa (s, s, s)
        $stmt->bind_param("sss", $searchPattern, $searchPattern, $searchPattern);
        $stmt->execute();
        
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}