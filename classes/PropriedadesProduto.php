<?php

require_once '../config/Config.php';

/**
 * Classe que gerencia a entidade PropriedadesProduto.
 * Inclui lógica para CRUD e o método listByProduto.
 * Tabela: propriedadesProduto (id, produto_id, propriedade, valor, status)
 */
class PropriedadesProduto {

    private $db;
    public $id;
    public $produto_id;  // Chave Estrangeira para a tabela 'produtos'
    public $propriedade; // Ex: Tamanho, Cor, Material
    public $valor;       // Ex: M, Vermelho, Algodão
    public $status;      // 1 (Ativo) / 0 (Inativo)
    public $createdAt;
    public $updatedAt;

    public function __construct() {
        // Inicializa a conexão com o banco de dados.
        $config = new Config();
        $this->db = $config->dbConnect();
        // Define o status padrão
        $this->status = 1;
    }

    //---------------------------------------------------------
    //  OPERAÇÕES CRUD
    //---------------------------------------------------------

    /**
     * Cria uma nova propriedade para um produto.
     * @return bool
     */
    public function create() {
        // Todas as colunas (exceto id e timestamps) são obrigatórias, exceto 'status' que tem default.
        $stmt = $this->db->prepare("
            INSERT INTO propriedadesProduto (produto_id, propriedade, valor, status) 
            VALUES (?, ?, ?, ?)
        ");
        
        // Tipos de ligação: i (produto_id), s (propriedade), s (valor), i (status)
        $stmt->bind_param(
            "issi",
            $this->produto_id,
            $this->propriedade,
            $this->valor,
            $this->status
        );
        
        return $stmt->execute();
    }

    /**
     * Lê uma única propriedade pelo ID.
     * @param int $id
     * @return array|null
     */
    public function read($id) {
        $stmt = $this->db->prepare("
            SELECT *
            FROM propriedadesProduto 
            WHERE id = ?
        ");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Atualiza os dados de uma propriedade existente.
     * @return bool
     */
    public function update() {
        $query = "
            UPDATE propriedadesProduto 
            SET produto_id = ?, propriedade = ?, valor = ?, status = ?
            WHERE id = ?
        ";
        $stmt = $this->db->prepare($query);
        
        // Tipos: i (produto_id), s (propriedade), s (valor), i (status), i (id)
        $stmt->bind_param(
            "issii",
            $this->produto_id,
            $this->propriedade,
            $this->valor,
            $this->status,
            $this->id
        );

        return $stmt->execute();
    }

    /**
     * Deleta uma propriedade pelo ID.
     * @return bool
     */
    public function delete() {
        $stmt = $this->db->prepare("DELETE FROM propriedadesProduto WHERE id = ?");
        $stmt->bind_param("i", $this->id);
        return $stmt->execute();
    }

    //---------------------------------------------------------
    //  OPERAÇÕES DE LISTAGEM ESPECÍFICAS
    //---------------------------------------------------------

    /**
     * Lista todas as propriedades (ativas ou não) associadas a um produto específico.
     * @param int $produtoId
     * @return array
     */
    public function listByProduto($produtoId) {
        $stmt = $this->db->prepare("
            SELECT 
                id, 
                produto_id, 
                propriedade, 
                valor, 
                status, 
                createdAt, 
                updatedAt
            FROM propriedadesProduto 
            WHERE produto_id = ?
            ORDER BY propriedade ASC, valor ASC
        ");
        $stmt->bind_param("i", $produtoId);
        $stmt->execute();
        
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    /**
     * Lista todas as propriedades ativas associadas a um produto específico.
     * @param int $produtoId
     * @return array
     */
    public function listActiveByProduto($produtoId) {
        $stmt = $this->db->prepare("
            SELECT 
                id, 
                propriedade, 
                valor 
            FROM propriedadesProduto 
            WHERE produto_id = ? AND status = 1
            ORDER BY propriedade ASC, valor ASC
        ");
        $stmt->bind_param("i", $produtoId);
        $stmt->execute();
        
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }


    /**
     * Lista todas as propriedades de todos os produtos (Método listAll padrão, se necessário).
     * @return array
     */
    public function listAll() {
        $query = "
            SELECT 
                pp.*,
                p.nome AS produto_nome 
            FROM propriedadesProduto pp
            JOIN produtos p ON pp.produto_id = p.id
            ORDER BY p.nome, pp.propriedade, pp.valor ASC
        ";
        $result = $this->db->query($query);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}