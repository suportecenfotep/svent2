<?php

require_once '../config/Config.php';
// Nota: Não é necessário incluir a classe Produto, pois FotosProduto gerencia sua própria tabela.

/**
 * Classe que gerencia a entidade FotosProduto.
 * Inclui lógica para CRUD e o método listByProduto.
 * Tabela: fotosProduto (id, produto_id, foto, descricao)
 */
class FotosProduto {

    private $db;
    public $id;
    public $produto_id; // Chave Estrangeira para a tabela 'produtos'
    public $foto;       // Nome do arquivo da foto
    public $descricao;  // Descrição opcional da foto
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
     * Cria uma nova foto para um produto.
     * @return bool
     */
    public function create() {
        // A descrição pode ser NULL, mas produto_id e foto são obrigatórios.
        $stmt = $this->db->prepare("
            INSERT INTO fotosProduto (produto_id, foto, descricao) 
            VALUES (?, ?, ?)
        ");
        
        // Tratamento de NULL para descricao
        $descricao = $this->descricao ?? NULL;
        
        // Tipos de ligação: i (produto_id), s (foto), s (descricao)
        $stmt->bind_param(
            "iss",
            $this->produto_id,
            $this->foto,
            $descricao
        );
        
        return $stmt->execute();
    }

    /**
     * Lê uma única foto pelo ID.
     * @param int $id
     * @return array|null
     */
    public function read($id) {
        $stmt = $this->db->prepare("
            SELECT *
            FROM fotosProduto 
            WHERE id = ?
        ");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Atualiza os dados de uma foto existente (principalmente a descrição).
     * @return bool
     */
    public function update() {
        $query = "
            UPDATE fotosProduto 
            SET produto_id = ?, foto = ?, descricao = ?
            WHERE id = ?
        ";
        $stmt = $this->db->prepare($query);
        
        $descricao = $this->descricao ?? NULL;

        // Tipos: i (produto_id), s (foto), s (descricao), i (id)
        $stmt->bind_param(
            "issi",
            $this->produto_id,
            $this->foto,
            $descricao,
            $this->id
        );

        return $stmt->execute();
    }

    /**
     * Deleta uma foto pelo ID.
     * @return bool
     */
    public function delete() {
        // NOTA: A lógica para deletar o arquivo físico da foto deve ser 
        // implementada no Router ou em um Manager, antes de chamar este método.
        $stmt = $this->db->prepare("DELETE FROM fotosProduto WHERE id = ?");
        $stmt->bind_param("i", $this->id);
        return $stmt->execute();
    }

    //---------------------------------------------------------
    //  OPERAÇÕES DE LISTAGEM ESPECÍFICAS
    //---------------------------------------------------------

    /**
     * Lista todas as fotos associadas a um produto específico.
     * @param int $produtoId
     * @return array
     */
    public function listByProduto($produtoId) {
        $stmt = $this->db->prepare("
            SELECT 
                id, 
                produto_id, 
                foto, 
                descricao, 
                createdAt, 
                updatedAt
            FROM fotosProduto 
            WHERE produto_id = ?
            ORDER BY createdAt ASC
        ");
        $stmt->bind_param("i", $produtoId);
        $stmt->execute();
        
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Lista todas as fotos de todos os produtos (Método listAll padrão, se necessário).
     * @return array
     */
    public function listAll() {
        $query = "
            SELECT 
                fp.*,
                p.nome AS produto_nome 
            FROM fotosProduto fp
            JOIN produtos p ON fp.produto_id = p.id
            ORDER BY fp.produto_id, fp.createdAt ASC
        ";
        $result = $this->db->query($query);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}