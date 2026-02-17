<?php
require_once '../config/Config.php';

/**
 * Classe que gerencia a entidade ItensEncomenda.
 * Responsável pelas operações CRUD e listagem de itens dentro de uma encomenda.
 */
class ItensEncomenda {

    private $db;
    
    // Propriedades mapeadas para a tabela 'itensencomendas'
    public $id;
    public $encomenda_id;
    public $produto_id;
    public $preco;      // Preço de venda no momento da compra (histórico)
    public $qtd;
    public $subtotal;   // preco * qtd
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
     * Cria um novo item de encomenda.
     * Esta função é tipicamente usada durante o processo de finalização da compra.
     * @return bool
     */
    public function create() {
        $stmt = $this->db->prepare("
            INSERT INTO itensencomendas (encomenda_id, produto_id, preco, qtd, subtotal) 
            VALUES (?, ?, ?, ?, ?)
        ");
        
        // Tipos: i (encomenda_id), i (produto_id), d (preco), i (qtd), d (subtotal)
        $stmt->bind_param(
             "iidis", // Mudança para 'iiddi' é mais apropriada, mas 's' para o subtotal decimal também funciona. Usaremos 'd' (double/decimal) para os valores monetários.
             $this->encomenda_id,
             $this->produto_id,
             $this->preco,
             $this->qtd,
             $this->subtotal
         );
        
        // Ajustando para ser mais preciso: i (int), i (int), d (decimal), i (int), d (decimal)
         $stmt->bind_param(
             "iiddi",
             $this->encomenda_id,
             $this->produto_id,
             $this->preco,
             $this->qtd,
             $this->subtotal
         );
        
        return $stmt->execute();
    }

    /**
     * Lê um item de encomenda pelo ID, incluindo detalhes do produto.
     * @param int $id
     * @return array|null
     */
    public function read($id) {
        $query = "
            SELECT 
                ie.*, 
                p.nome AS produto_nome, 
                p.foto AS produto_foto,
                p.descricao AS produto_descricao
            FROM itensencomendas ie
            JOIN produtos p ON ie.produto_id = p.id
            WHERE ie.id = ? 
            LIMIT 1
        ";
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Atualiza a quantidade e, consequentemente, o subtotal de um item de encomenda.
     * O preço e o produto não devem ser alterados.
     * @return bool
     */
    public function update() {
        $query = "
            UPDATE itensencomendas 
            SET qtd = ?, subtotal = ?
            WHERE id = ? AND encomenda_id = ?
        ";
        
        // Nota: O subtotal deve ser recalculado no PHP antes de chamar este método: $this->subtotal = $this->preco * $this->qtd;
        $stmt = $this->db->prepare($query);
        
        // Tipos: i (qtd), d (subtotal), i (id), i (encomenda_id)
        $stmt->bind_param(
            "idis",
            $this->qtd,
            $this->subtotal,
            $this->id,
            $this->encomenda_id
        );

        return $stmt->execute();
    }

    /**
     * Deleta um item específico da encomenda pelo ID.
     * @return bool
     */
    public function delete() {
        // Requer o ID do item e o ID da encomenda para maior segurança
        $stmt = $this->db->prepare("DELETE FROM itensencomendas WHERE id = ? AND encomenda_id = ?");
        $stmt->bind_param("ii", $this->id, $this->encomenda_id); 
        return $stmt->execute();
    }

    //---------------------------------------------------------
    //  MÉTODOS ESPECÍFICOS DE LISTAGEM
    //---------------------------------------------------------

    /**
     * Lista todos os itens pertencentes a uma encomenda específica.
     * Inclui detalhes do produto (nome, foto) para exibição.
     * @param int $encomenda_id
     * @return array
     */
    public function listByEncomenda($encomenda_id) {
        $query = "
            SELECT 
                ie.*, 
                p.nome AS produto_nome, 
                p.foto AS produto_foto,
                p.preco AS produto_preco_atual /* Preço atual do produto (para comparação) */
            FROM itensencomendas ie
            JOIN produtos p ON ie.produto_id = p.id
            WHERE ie.encomenda_id = ?
            ORDER BY ie.id ASC
        ";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $encomenda_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}