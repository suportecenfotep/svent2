<?php
require_once '../config/Config.php';

class Parceiro {

    private $db;
    public $id;
    public $foto; // Nome do arquivo da foto do parceiro (VARCHAR(255) NOT NULL)
    public $nome; // Nome do parceiro (VARCHAR(255) NOT NULL)
    public $link; // Link do parceiro (VARCHAR(255) Opcional)
    public $createdAt;
    public $updatedAt;

    public function __construct() {
        // Inicializa a conexão com o banco de dados
        $config = new Config();
        $this->db = $config->dbConnect();
    }

    //------------------------------------------
    // MÉTODOS CRUD
    //------------------------------------------

    /**
     * Cria um novo parceiro no banco de dados.
     * @return bool Retorna TRUE em caso de sucesso, FALSE em caso de falha.
     */
    public function create() {
        // Prepara a consulta SQL para inserção de dados na tabela 'parceiros'
        $stmt = $this->db->prepare("
            INSERT INTO parceiros (foto, nome, link) 
            VALUES (?, ?, ?)
        ");
        
        // Liga os parâmetros (tipos: s=string para foto, nome e link)
        $stmt->bind_param(
            "sss",
            $this->foto,
            $this->nome,
            $this->link
        );
        
        return $stmt->execute();
    }

    /**
     * Lê um parceiro específico pelo ID.
     * @param int $id O ID do parceiro a ser lido.
     * @return array|null Retorna um array associativo com os dados do parceiro ou null se não for encontrado.
     */
    public function read($id) {
        $stmt = $this->db->prepare("
            SELECT * FROM parceiros 
            WHERE id = ?
        ");
        
        $stmt->bind_param("i", $id);
        $stmt->execute();
        
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Atualiza os dados de um parceiro existente.
     * @return bool Retorna TRUE em caso de sucesso, FALSE em caso de falha.
     */
    public function update() {
        $stmt = $this->db->prepare("
            UPDATE parceiros 
            SET foto = ?, nome = ?, link = ? 
            WHERE id = ?
        ");
        
        // Liga os parâmetros: os 3 campos para atualizar (sss) + o ID (i)
        $stmt->bind_param(
            "sssi",
            $this->foto,
            $this->nome,
            $this->link,
            $this->id // O ID é usado no WHERE
        );
        
        return $stmt->execute();
    }

    /**
     * Deleta um parceiro do banco de dados.
     * O ID do parceiro deve estar preenchido na propriedade $this->id.
     * @return bool Retorna TRUE em caso de sucesso, FALSE em caso de falha.
     */
    public function delete() {
        $stmt = $this->db->prepare("DELETE FROM parceiros WHERE id = ?");
        $stmt->bind_param("i", $this->id);
        
        return $stmt->execute();
    }

    //------------------------------------------
    // MÉTODOS DE LISTAGEM/FILTRO
    //------------------------------------------

    /**
     * Lista todos os parceiros ordenados pelo nome.
     * @return array Retorna um array de arrays associativos com todos os parceiros.
     */
    public function listAll() {
        $query = "
            SELECT * FROM parceiros
            ORDER BY nome ASC
        ";
        
        $result = $this->db->query($query);
        
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    /**
     * Lista parceiros ativos.
     * Como a tabela não tem campo 'status', este método é idêntico ao listAll.
     * Se você decidir adicionar um campo 'status' à tabela, atualize esta função.
     * @return array Retorna um array de arrays associativos com parceiros.
     */
    public function listActive() {
        // Por padrão, retorna todos, pois não há filtro de status na tabela.
        return $this->listAll(); 
    }

    /**
     * Filtra parceiros por nome ou link.
     * @param string $search A string de busca.
     * @return array Retorna um array de arrays associativos com os parceiros que correspondem à busca.
     */
    public function filter($search) {
        // Adiciona wildcards (%) para o LIKE do SQL
        $search = "%{$search}%";
        
        $stmt = $this->db->prepare("
            SELECT * FROM parceiros
            WHERE nome LIKE ? OR link LIKE ?
            ORDER BY nome ASC
        ");
        
        // Liga a string de busca (duas vezes: nome e link)
        $stmt->bind_param("ss", $search, $search);
        $stmt->execute();
        
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}