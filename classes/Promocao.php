<?php

require_once '../config/Config.php';

/**
 * Classe que gerencia a entidade Promocao.
 * Inclui lógica para CRUD e métodos listAll, filter, listActives, listActivesByProduct.
 * Tabela: promocoes (id, produto_id, desconto, data_inicio, data_fim, user_id)
 */
class Promocao {

    private $db;
    public $id;
    public $produto_id;     // Chave Estrangeira para a tabela 'produtos'
    public $user_id;        // Chave Estrangeira para a tabela 'users' (Quem criou/gerenciou)
    public $desconto;       // Percentagem de desconto (Ex: 10.50)
    public $data_inicio;    // Data de início (Formato: YYYY-MM-DD)
    public $data_fim;       // Data de fim (Formato: YYYY-MM-DD)
    public $createdAt;
    public $updatedAt;

    public function __construct() {
        $config = new Config();
        $this->db = $config->dbConnect();
    }

    //---------------------------------------------------------
    //  OPERAÇÕES CRUD
    //---------------------------------------------------------

    /**
     * Cria uma nova promoção.
     * @return bool
     */
    public function create() {
        $stmt = $this->db->prepare("
            INSERT INTO promocoes (produto_id, user_id, desconto, data_inicio, data_fim) 
            VALUES (?, ?, ?, ?, ?)
        ");
        
        // Tipos de ligação: i (produto_id), i (user_id), d (desconto - decimal), s (data_inicio), s (data_fim)
        $stmt->bind_param(
            "iidss", // O 'd' é para double/decimal, 'i' para int, 's' para string
            $this->produto_id,
            $this->user_id,
            $this->desconto,
            $this->data_inicio,
            $this->data_fim
        );
        
        return $stmt->execute();
    }

    /**
     * Lê uma única promoção pelo ID.
     * @param int $id
     * @return array|null
     */
    public function read($id) {
        $stmt = $this->db->prepare("
            SELECT *
            FROM promocoes 
            WHERE id = ?
        ");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Atualiza os dados de uma promoção existente.
     * @return bool
     */
    public function update() {
        $query = "
            UPDATE promocoes 
            SET produto_id = ?, user_id = ?, desconto = ?, data_inicio = ?, data_fim = ?
            WHERE id = ?
        ";
        $stmt = $this->db->prepare($query);
        
        // Tipos: i (produto_id), i (user_id), d (desconto), s (data_inicio), s (data_fim), i (id)
        $stmt->bind_param(
            "iidssi",
            $this->produto_id,
            $this->user_id,
            $this->desconto,
            $this->data_inicio,
            $this->data_fim,
            $this->id
        );

        return $stmt->execute();
    }

    /**
     * Deleta uma promoção pelo ID.
     * @return bool
     */
    public function delete() {
        $stmt = $this->db->prepare("DELETE FROM promocoes WHERE id = ?");
        $stmt->bind_param("i", $this->id);
        return $stmt->execute();
    }

    //---------------------------------------------------------
    //  MÉTODOS DE LISTAGEM
    //---------------------------------------------------------

    /**
     * Lista todas as promoções, juntando dados do produto e do usuário.
     * @return array
     */
    public function listAll() {
        $query = "
            SELECT 
                p.*,
                prod.nome AS produto_nome,
                prod.foto AS produto_foto,
                prod.preco AS produto_preco,
                u.nome AS user_nome
            FROM promocoes p
            JOIN produtos prod ON p.produto_id = prod.id
            LEFT JOIN users u ON p.user_id = u.id
            ORDER BY p.data_inicio DESC, p.data_fim DESC
        ";
        $result = $this->db->query($query);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    /**
     * Lista apenas as promoções ATIVAS (data_fim >= data atual).
     * @return array
     */
    public function listActives() {
        $query = "
            SELECT 
                p.*,
                prod.nome AS produto_nome
            FROM promocoes p
            JOIN produtos prod ON p.produto_id = prod.id
            WHERE p.data_fim >= CURDATE()
            ORDER BY p.data_fim ASC
        ";
        $result = $this->db->query($query);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Lista todas as promoções ATIVAS de um produto específico.
     * @param int $produtoId
     * @return array
     */
    public function listActivesByProduct($produtoId) {
        $stmt = $this->db->prepare("
            SELECT 
                id, 
                produto_id, 
                desconto, 
                data_inicio, 
                data_fim
            FROM promocoes 
            WHERE produto_id = ? AND data_fim >= CURDATE()
            ORDER BY desconto DESC, data_fim ASC
        ");
        $stmt->bind_param("i", $produtoId);
        $stmt->execute();
        
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Filtra promoções com base em critérios de pesquisa (por produto, data ou desconto).
     * Nota: Este é um método de filtro genérico simplificado.
     * @param string $searchTerm Termo de pesquisa (ex: nome do produto)
     * @param string|null $filterDate 'active', 'expired', ou null
     * @return array
     */
    public function filter($searchTerm = '', $filterDate = null) {
        $sql = "
            SELECT 
                p.*,
                prod.nome AS produto_nome
            FROM promocoes p
            JOIN produtos prod ON p.produto_id = prod.id
            WHERE 1=1
        ";
        $params = [];
        $types = '';
        
        // 1. Filtrar por Termo de Pesquisa (Nome do Produto)
        if (!empty($searchTerm)) {
            $sql .= " AND prod.nome LIKE ?";
            $params[] = '%' . $searchTerm . '%';
            $types .= 's';
        }
        
        // 2. Filtrar por Estado (Ativa ou Expirada)
        if ($filterDate === 'active') {
            $sql .= " AND p.data_fim >= CURDATE()";
        } elseif ($filterDate === 'expired') {
            $sql .= " AND p.data_fim < CURDATE()";
        }

        $sql .= " ORDER BY p.data_inicio DESC";

        $stmt = $this->db->prepare($sql);
        
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Lista promoções ATIVAS com paginação (limit e offset).
     * Inclui dados do produto e do usuário.
     *
     * @param int $limit  Quantos registros retornar
     * @param int $offset Quantos registros pular
     * @return array
     */
    public function listAllActivesByLimit($limit, $offset) {
        $stmt = $this->db->prepare("
            SELECT 
                p.id,
                p.produto_id,
                p.desconto,
                p.data_inicio,
                p.data_fim,
                prod.nome AS produto_nome,
                prod.foto AS produto_foto,
                prod.preco AS produto_preco,
                u.nome AS user_nome
            FROM promocoes p
            JOIN produtos prod ON p.produto_id = prod.id
            LEFT JOIN users u ON p.user_id = u.id
            WHERE p.data_fim >= CURDATE()
            ORDER BY p.data_fim ASC
            LIMIT ? OFFSET ?
        ");

        $stmt->bind_param("ii", $limit, $offset);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

}