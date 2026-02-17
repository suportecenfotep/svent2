<?php

require_once '../config/Config.php';

/**
 * Classe que gerencia a entidade Produto.
 * Inclui lógica para CRUD e métodos de listagem e filtro.
 */
class Produto {

    private $db;
    public $id;
    public $foto;
    public $nome;
    public $preco; // Novo campo
    public $descricao;
    public $user_id; // Chave Estrangeira para a tabela 'users' (Pode ser NULL)
    public $categoria_id; // Chave Estrangeira para a tabela 'categorias'
    public $subcategoria_id; // Chave Estrangeira para a tabela 'subcategorias' (Pode ser NULL)
    public $status; // TINYINT(1)
    public $createdAt;
    public $updatedAt;

    public function __construct() {
        // Inicializa a conexão com o banco de dados.
        $config = new Config();
        $this->db = $config->dbConnect();
    }

    //---------------------------------------------------------
    //  OPERAÇÕES CRUD
    //---------------------------------------------------------

    /**
     * Cria um novo produto.
     * @return bool
     */
    public function create() {
        // user_id e subcategoria_id podem ser NULL, por isso usamos 's' para tudo e tratamos o NULL.
        $stmt = $this->db->prepare("
            INSERT INTO produtos (foto, nome, preco, descricao, user_id, categoria_id, subcategoria_id, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        // Tratamento de NULL para user_id e subcategoria_id
        $user_id = $this->user_id ?? NULL;
        $subcategoria_id = $this->subcategoria_id ?? NULL;
        
        // Tipos de ligação: s (foto), s (nome), d (preco), s (descricao), i (user_id), i (categoria_id), i (subcategoria_id), i (status)
        
        $stmt->bind_param(
            "ssdsiiii",
            $this->foto,
            $this->nome,
            $this->preco, // Double/Decimal
            $this->descricao,
            $user_id,
            $this->categoria_id,
            $subcategoria_id,
            $this->status
        );
        
        return $stmt->execute();
    }

    /**
     * Lê um produto pelo ID, incluindo os nomes da Categoria e Subcategoria.
     * @param int $id
     * @return array|null
     */
    public function read($id) {
        $stmt = $this->db->prepare("
            SELECT 
                p.*, 
                c.nome AS categoria_nome, 
                s.nome AS subcategoria_nome,
                u.nome AS user_nome
            FROM produtos p 
            JOIN categorias c ON p.categoria_id = c.id
            LEFT JOIN subcategorias s ON p.subcategoria_id = s.id
            LEFT JOIN users u ON p.user_id = u.id
            WHERE p.id = ?
        ");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Atualiza um produto existente.
     * @return bool
     */
    public function update() {
        $query = "
            UPDATE produtos 
            SET foto = ?, nome = ?, preco = ?, descricao = ?, user_id = ?, categoria_id = ?, subcategoria_id = ?, status = ?
            WHERE id = ?
        ";
        $stmt = $this->db->prepare($query);
        
        $user_id = $this->user_id ?? NULL;
        $subcategoria_id = $this->subcategoria_id ?? NULL;

        // Tipos: s (foto), s (nome), d (preco), s (descricao), i (user_id), i (categoria_id), i (subcategoria_id), i (status), i (id)
        $stmt->bind_param(
            "ssdsiiiii",
            $this->foto,
            $this->nome,
            $this->preco,
            $this->descricao,
            $user_id,
            $this->categoria_id,
            $subcategoria_id,
            $this->status,
            $this->id
        );

        return $stmt->execute();
    }

    /**
     * Deleta um produto pelo ID.
     * @return bool
     */
    public function delete() {
        $stmt = $this->db->prepare("DELETE FROM produtos WHERE id = ?");
        $stmt->bind_param("i", $this->id);
        return $stmt->execute();
    }

    //---------------------------------------------------------
    //  OPERAÇÕES DE LISTAGEM E FILTRO
    //---------------------------------------------------------

    /**
     * Lista todos os produtos, incluindo nomes da Categoria e Subcategoria.
     * @return array
     */
    public function listAll() {
        $query = "
            SELECT 
                p.*, 
                c.nome AS categoria_nome, 
                s.nome AS subcategoria_nome,
                u.nome AS user_nome
            FROM produtos p
            JOIN categorias c ON p.categoria_id = c.id
            LEFT JOIN subcategorias s ON p.subcategoria_id = s.id
            LEFT JOIN users u ON p.user_id = u.id
            ORDER BY p.nome ASC
        ";
        $result = $this->db->query($query);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    /**
     * Filtra produtos por nome, descrição, nome da categoria ou subcategoria.
     * @param string $search
     * @return array
     */
    public function filter($search) {
        // Prepara o termo de pesquisa para a cláusula LIKE
        $searchPattern = "%{$search}%";
        
        $stmt = $this->db->prepare("
            SELECT 
                p.*, 
                c.nome AS categoria_nome, 
                s.nome AS subcategoria_nome,
                u.nome AS user_nome
            FROM produtos p
            JOIN categorias c ON p.categoria_id = c.id
            LEFT JOIN subcategorias s ON p.subcategoria_id = s.id
            LEFT JOIN users u ON p.user_id = u.id
            WHERE p.nome LIKE ? 
                OR p.descricao LIKE ?
                OR c.nome LIKE ?
                OR s.nome LIKE ?
                OR p.subcategoria_id LIKE ?
            ORDER BY p.nome ASC
        ");
        
        // Cinco parâmetros de pesquisa (s, s, s, s, s) - corrigido para s em subcategoria_id para string pattern
        $stmt->bind_param("sssss", $searchPattern, $searchPattern, $searchPattern, $searchPattern, $searchPattern);
        $stmt->execute();
        
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Filtra produtos com paginação (limit e offset),
     * pesquisando por nome, descrição, categoria ou subcategoria.
     *
     * @param string $search  Termo de busca
     * @param int $limit      Quantos produtos retornar
     * @param int $offset     Quantos produtos pular
     * @return array
     */
    public function filterByLimit($search, $limit, $offset, $categoria_id = null) {
        $searchPattern = "%{$search}%";

        $query = "
            SELECT 
                p.*, 
                c.nome AS categoria_nome, 
                s.nome AS subcategoria_nome,
                u.nome AS user_nome
            FROM produtos p
            JOIN categorias c ON p.categoria_id = c.id
            LEFT JOIN subcategorias s ON p.subcategoria_id = s.id
            LEFT JOIN users u ON p.user_id = u.id
            WHERE (
                p.nome LIKE ?
                OR p.descricao LIKE ?
                OR c.nome LIKE ?
                OR s.nome LIKE ?
            )
        ";

        // Filtro opcional por categoria
        if (!empty($categoria_id)) {
            $query .= " AND p.categoria_id = ? ";
        }

        $query .= " ORDER BY p.createdAt DESC LIMIT ? OFFSET ?";

        $stmt = $this->db->prepare($query);

        // Faz o bind dependendo se há categoria_id ou não
        if (!empty($categoria_id)) {
            $stmt->bind_param("sssssii", $searchPattern, $searchPattern, $searchPattern, $searchPattern, $categoria_id, $limit, $offset);
        } else {
            $stmt->bind_param("ssssii", $searchPattern, $searchPattern, $searchPattern, $searchPattern, $limit, $offset);
        }

        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

        /**
     * Pesquisa produtos por nome, descrição, categoria ou subcategoria.
     * Retorna resultados limitados a 50 por padrão.
     *
     * @param string $search Termo de busca
     * @param int $limit (opcional) Limite de resultados
     * @return array
     */
    public function search($search, $limit = 50) {
        $searchPattern = "%{$search}%";

        $stmt = $this->db->prepare("
            SELECT 
                p.id,
                p.nome,
                p.preco,
                p.foto,
                p.descricao,
                c.nome AS categoria_nome,
                s.nome AS subcategoria_nome,
                u.nome AS user_nome
            FROM produtos p
            JOIN categorias c ON p.categoria_id = c.id
            LEFT JOIN subcategorias s ON p.subcategoria_id = s.id
            LEFT JOIN users u ON p.user_id = u.id
            WHERE p.nome LIKE ?
                OR p.descricao LIKE ?
                OR c.nome LIKE ?
                OR s.nome LIKE ?
            ORDER BY p.createdAt DESC
            LIMIT ?
        ");

        // 4 strings + 1 inteiro
        $stmt->bind_param("ssssi", $searchPattern, $searchPattern, $searchPattern, $searchPattern, $limit);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    /**
     * Lista produtos ativos por ID da Subcategoria.
     * Inclui nomes da Categoria e Subcategoria.
     * @param int $subcategoria_id
     * @return array
     */
    public function listBySubcategoria($subcategoria_id) {
        $query = "
            SELECT 
                p.id, p.nome, p.preco, p.foto, 
                c.nome AS categoria_nome, 
                s.nome AS subcategoria_nome
            FROM produtos p
            JOIN categorias c ON p.categoria_id = c.id
            JOIN subcategorias s ON p.subcategoria_id = s.id
            WHERE p.subcategoria_id = ? AND p.status = 1
            ORDER BY p.nome ASC
        ";
        $stmt = $this->db->prepare($query);
        
        // O ID da subcategoria é um inteiro (i)
        $stmt->bind_param("i", $subcategoria_id);
        $stmt->execute();
        
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

       /**
     * Lista produtos com paginação (limit e offset).
     * Pode ser usado para carregamento incremental (Ver Mais...).
     *
     * @param int $limit  Quantos produtos retornar
     * @param int $offset Quantos produtos pular
     * @return array
     */
    public function listByLimit($limit, $offset) {
        $stmt = $this->db->prepare("
            SELECT 
                p.*, 
                c.nome AS categoria_nome, 
                s.nome AS subcategoria_nome,
                u.nome AS user_nome
            FROM produtos p
            JOIN categorias c ON p.categoria_id = c.id
            LEFT JOIN subcategorias s ON p.subcategoria_id = s.id
            LEFT JOIN users u ON p.user_id = u.id
            ORDER BY p.createdAt DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->bind_param("ii", $limit, $offset);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}