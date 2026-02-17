<?php
require_once '../config/Config.php';

class Noticia {

    private $db;
    public $id;
    public $user_id;
    public $foto;
    public $titulo;
    public $descricao;
    public $status; 
    public $createdAt;
    public $updatedAt;

    public function __construct() {
        $config = new Config();
        $this->db = $config->dbConnect();
    }

    /**
     * Cria uma nova notícia no banco de dados.
     * @return bool Retorna TRUE em caso de sucesso, FALSE em caso de falha.
     */
    public function create() {
        // Prepara a consulta SQL para inserção de dados
        $stmt = $this->db->prepare("
            INSERT INTO noticias (user_id, foto, titulo, descricao, status) 
            VALUES (?, ?, ?, ?, ?)
        ");
        
        // Liga os parâmetros (tipos: i=integer, s=string)
        // O status é um booleano que geralmente é tratado como inteiro (0 ou 1) no MySQL
        $stmt->bind_param(
            "isssi",
            $this->user_id,
            $this->foto,
            $this->titulo,
            $this->descricao,
            $this->status
        );
        
        return $stmt->execute();
    }

    /**
     * Lê uma notícia específica pelo ID e inclui o nome do autor (join com users).
     * @param int $id O ID da notícia a ser lida.
     * @return array|null Retorna um array associativo com os dados da notícia ou null se não for encontrada.
     */
    public function read($id) {
        $stmt = $this->db->prepare("
            SELECT n.*, u.nome as autor 
            FROM noticias n
            INNER JOIN users u ON n.user_id = u.id
            WHERE n.id = ?
        ");
        
        $stmt->bind_param("i", $id);
        $stmt->execute();
        
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Atualiza os dados de uma notícia existente.
     * @return bool Retorna TRUE em caso de sucesso, FALSE em caso de falha.
     */
    public function update() {
        $stmt = $this->db->prepare("
            UPDATE noticias 
            SET user_id = ?, foto = ?, titulo = ?, descricao = ?, status = ? 
            WHERE id = ?
        ");
        
        // Liga os parâmetros: os 5 campos para atualizar + o ID (where)
        $stmt->bind_param(
            "isssii",
            $this->user_id,
            $this->foto,
            $this->titulo,
            $this->descricao,
            $this->status,
            $this->id // O ID é usado no WHERE
        );
        
        return $stmt->execute();
    }

    /**
     * Deleta uma notícia do banco de dados.
     * O ID da notícia deve estar preenchido na propriedade $this->id.
     * @return bool Retorna TRUE em caso de sucesso, FALSE em caso de falha.
     */
    public function delete() {
        $stmt = $this->db->prepare("DELETE FROM noticias WHERE id = ?");
        $stmt->bind_param("i", $this->id);
        
        return $stmt->execute();
    }

    /**
     * Lista todas as notícias (com autor) ordenadas pela data de criação (mais recente primeiro).
     * @return array Retorna um array de arrays associativos com todas as notícias.
     */
    public function listAll() {
        $result = $this->db->query("
            SELECT n.*, u.nome as autor 
            FROM noticias n
            INNER JOIN users u ON n.user_id = u.id
            ORDER BY n.createdAt DESC
        ");
        
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Lista um número limitado de notícias recentes.
     * @param int $limit O número máximo de notícias a retornar.
     * @return array Retorna um array de arrays associativos.
     */
    public function listRecentsByLimit($limit) {
        $stmt = $this->db->prepare("
            SELECT n.*, u.nome as autor 
            FROM noticias n
            INNER JOIN users u ON n.user_id = u.id
            ORDER BY n.createdAt DESC
            LIMIT ?
        ");
        
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Filtra notícias pelo título ou descrição usando LIKE.
     * @param string $search A string de busca.
     * @return array Retorna um array de arrays associativos com as notícias que correspondem à busca.
     */
    public function filter($search) {
        // Adiciona wildcards (%) para o LIKE do SQL
        $search = "%{$search}%";
        
        $stmt = $this->db->prepare("
            SELECT n.*, u.nome as autor 
            FROM noticias n
            INNER JOIN users u ON n.user_id = u.id
            WHERE n.titulo LIKE ? OR n.descricao LIKE ?
            ORDER BY n.createdAt DESC
        ");
        
        // Liga a string de busca duas vezes (para titulo e descricao)
        $stmt->bind_param("ss", $search, $search);
        $stmt->execute();
        
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}