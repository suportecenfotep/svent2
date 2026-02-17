<?php
require_once '../config/Config.php';

class Carrinho {

    private $db;    
    public $id;
    public $cliente_id;
    public $produto_id;
    public $qtd;
    public $createdAt;
    public $updatedAt;

    public function __construct() {
        $config = new Config();
        $this->db = $config->dbConnect();
    }

    //---------------------------------------------------------
    //  OPERAÇÕES CRUD (Carrinho)
    //---------------------------------------------------------

    /**
     * Adiciona um produto ao carrinho ou aumenta a quantidade se já existir.
     * Esta é uma operação de "upsert" (INSERT ou UPDATE).
     * @return bool
     */
    public function createOrUpdate() {
        // 1. Verifica se o produto já existe no carrinho do cliente.
        $stmt_check = $this->db->prepare("
            SELECT id, qtd FROM carrinho 
            WHERE cliente_id = ? AND produto_id = ?
            LIMIT 1
        ");
        $stmt_check->bind_param("ii", $this->cliente_id, $this->produto_id);
        $stmt_check->execute();
        $result = $stmt_check->get_result();
        $item = $result->fetch_assoc();
        $stmt_check->close();

        if ($item) {
            // Se o item já existe, apenas atualiza a quantidade.
            $nova_qtd = $item['qtd'] + $this->qtd;
            $stmt_update = $this->db->prepare("
                UPDATE carrinho SET qtd = ? WHERE id = ?
            ");
            $stmt_update->bind_param("ii", $nova_qtd, $item['id']);
            return $stmt_update->execute();
        } else {
            // Se o item não existe, insere um novo registro.
            $stmt_insert = $this->db->prepare("
                INSERT INTO carrinho (cliente_id, produto_id, qtd) 
                VALUES (?, ?, ?)
            ");
            $stmt_insert->bind_param("iii", $this->cliente_id, $this->produto_id, $this->qtd);
            return $stmt_insert->execute();
        }
    }
    
    /**
     * Lê um item do carrinho pelo seu ID.
     * @param int $id
     * @return array|null
     */
    public function read($id) {
        $stmt = $this->db->prepare("
            SELECT * FROM carrinho 
            WHERE id = ? 
            LIMIT 1
        ");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Atualiza a quantidade de um item específico no carrinho.
     * @return bool
     */
    public function updateQtd() {
        $query = "
            UPDATE carrinho 
            SET qtd = ? 
            WHERE id = ? AND cliente_id = ?
        ";
        $stmt = $this->db->prepare($query);

        // Se a quantidade for zero ou menor, a função delete deve ser chamada.
        if ($this->qtd <= 0) {
            // Em um cenário real, você chamaria $this->delete();
            return false; 
        }

        $stmt->bind_param("iii", $this->qtd, $this->id, $this->cliente_id);
        return $stmt->execute();
    }
    
    /**
     * Deleta um item do carrinho pelo ID.
     * @return bool
     */
    public function delete() {
        $stmt = $this->db->prepare("
            DELETE FROM carrinho 
            WHERE id = ? AND cliente_id = ?
        ");
        $stmt->bind_param("ii", $this->id, $this->cliente_id); // Requer o ID do item e do cliente para segurança
        return $stmt->execute();
    }

    // ---------------------------------------------------------
    //  MÉTODOS ESPECÍFICOS DE LISTAGEM
    // ---------------------------------------------------------

    /**
     * Lista todos os itens no carrinho de um cliente específico.
     * Inclui detalhes do produto e calcula o subtotal da linha.
     * * @return array
     */
    public function listByCliente() {
        $query = "
            SELECT 
                c.id as carrinho_id, 
                c.qtd, 
                p.id as produto_id, 
                p.foto, 
                p.nome as produto_nome, 
                p.preco,
                (c.qtd * p.preco) as subtotal_linha
            FROM carrinho c
            JOIN produtos p ON c.produto_id = p.id
            WHERE c.cliente_id = ?
            ORDER BY c.createdAt DESC
        ";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $this->cliente_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    /**
     * Limpa completamente o carrinho de um cliente.
     * @param int $cliente_id
     * @return bool
     */
    public function clearCart($cliente_id) {
        $stmt = $this->db->prepare("DELETE FROM carrinho WHERE cliente_id = ?");
        $stmt->bind_param("i", $cliente_id);
        return $stmt->execute();
    }
}