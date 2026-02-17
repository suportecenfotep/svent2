<?php
require_once '../config/Config.php';

class Slideshow {

    private $db;
    public $id;
    public $foto; // Nome do arquivo da imagem
    public $status; // 1 para ativo, 0 para inativo
    public $createdAt;
    public $updatedAt;

    public function __construct() {
        // Inicializa a conexão com o banco de dados através da classe Config
        $config = new Config();
        $this->db = $config->dbConnect();
    }

    //------------------------------------------
    // MÉTODOS CRUD
    //------------------------------------------

    /**
     * Cria um novo slide no banco de dados.
     * @return bool Retorna TRUE em caso de sucesso, FALSE em caso de falha.
     */
    public function create() {
        // Prepara a consulta SQL para inserção de dados na tabela 'slideshow'
        $stmt = $this->db->prepare("
            INSERT INTO slideshow (foto, status) 
            VALUES (?, ?)
        ");
        
        // Liga os parâmetros (tipos: s=string para foto, i=integer para status)
        $stmt->bind_param(
            "si",
            $this->foto,
            $this->status
        );
        
        return $stmt->execute();
    }

    /**
     * Lê um slide específico pelo ID.
     * @param int $id O ID do slide a ser lido.
     * @return array|null Retorna um array associativo com os dados do slide ou null se não for encontrado.
     */
    public function read($id) {
        $stmt = $this->db->prepare("
            SELECT * FROM slideshow 
            WHERE id = ?
        ");
        
        $stmt->bind_param("i", $id);
        $stmt->execute();
        
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Atualiza os dados de um slide existente.
     * Nota: A foto é o único campo além do status que pode ser atualizado.
     * @return bool Retorna TRUE em caso de sucesso, FALSE em caso de falha.
     */
    public function update() {
        $stmt = $this->db->prepare("
            UPDATE slideshow 
            SET foto = ?, status = ? 
            WHERE id = ?
        ");
        
        // Liga os parâmetros: os 2 campos para atualizar + o ID (where)
        $stmt->bind_param(
            "sii",
            $this->foto,
            $this->status,
            $this->id // O ID é usado no WHERE
        );
        
        return $stmt->execute();
    }

    /**
     * Deleta um slide do banco de dados.
     * O ID do slide deve estar preenchido na propriedade $this->id.
     * @return bool Retorna TRUE em caso de sucesso, FALSE em caso de falha.
     */
    public function delete() {
        $stmt = $this->db->prepare("DELETE FROM slideshow WHERE id = ?");
        $stmt->bind_param("i", $this->id);
        
        return $stmt->execute();
    }

    //------------------------------------------
    // MÉTODOS DE LISTAGEM/FILTRO
    //------------------------------------------

    /**
     * Lista todos os slides ordenados pela data de criação.
     * @return array Retorna um array de arrays associativos com todos os slides.
     */
    public function listAll() {
        $result = $this->db->query("
            SELECT * FROM slideshow
            ORDER BY createdAt DESC
        ");
        
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    /**
     * Lista apenas slides com status ativo.
     * @return array Retorna um array de arrays associativos com slides ativos.
     */
    public function listActive() {
        $stmt = $this->db->prepare("
            SELECT * FROM slideshow
            WHERE status = 1
            ORDER BY createdAt DESC
        ");
        
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Filtra slides por parte do nome do arquivo da foto (se necessário).
     * @param string $search A string de busca.
     * @return array Retorna um array de arrays associativos com os slides que correspondem à busca.
     */
    public function filter($search) {
        // Adiciona wildcards (%) para o LIKE do SQL
        $search = "%{$search}%";
        
        $stmt = $this->db->prepare("
            SELECT *
            FROM slideshow
            WHERE foto LIKE ?
            ORDER BY createdAt DESC
        ");
        
        // Liga a string de busca (apenas na foto)
        $stmt->bind_param("s", $search);
        $stmt->execute();
        
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}