<?php
require_once '../config/Config.php';

class Faq {

    private $db;
    public $id;
    public $categoria;
    public $pergunta;
    public $resposta;
    public $ordem;
    public $status;
    public $createdAt;
    public $updatedAt;

    public function __construct() {
        $config = new Config();
        $this->db = $config->dbConnect();
    }

    /**
     * Cria uma nova FAQ.
     */
    public function create() {
        $stmt = $this->db->prepare("
            INSERT INTO faqs (categoria, pergunta, resposta, ordem, status)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->bind_param(
            "sssii",
            $this->categoria,
            $this->pergunta,
            $this->resposta,
            $this->ordem,
            $this->status
        );

        return $stmt->execute();
    }

    /**
     * Lê uma FAQ específica pelo ID.
     */
    public function read($id) {
        $stmt = $this->db->prepare("
            SELECT *
            FROM faqs
            WHERE id = ?
        ");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Atualiza uma FAQ existente.
     */
    public function update() {
        $stmt = $this->db->prepare("
            UPDATE faqs
            SET categoria = ?, pergunta = ?, resposta = ?, ordem = ?, status = ?
            WHERE id = ?
        ");
        $stmt->bind_param(
            "sssiii",
            $this->categoria,
            $this->pergunta,
            $this->resposta,
            $this->ordem,
            $this->status,
            $this->id
        );

        return $stmt->execute();
    }

    /**
     * Deleta uma FAQ pelo ID.
     */
    public function delete() {
        $stmt = $this->db->prepare("DELETE FROM faqs WHERE id = ?");
        $stmt->bind_param("i", $this->id);

        return $stmt->execute();
    }

    /**
     * Lista todas as FAQs.
     */
    public function listAll() {
        $result = $this->db->query("
            SELECT *
            FROM faqs
            ORDER BY categoria ASC, ordem ASC
        ");

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Lista FAQs ativas (status = 1).
     */
    public function listActive() {
        $result = $this->db->query("
            SELECT *
            FROM faqs
            WHERE status = 1
            ORDER BY categoria ASC, ordem ASC
        ");

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Filtra FAQs por texto.
     */
    public function filter($search) {
        $search = "%{$search}%";

        $stmt = $this->db->prepare("
            SELECT *
            FROM faqs
            WHERE categoria LIKE ? 
               OR pergunta LIKE ? 
               OR resposta LIKE ?
            ORDER BY categoria ASC, ordem ASC
        ");
        $stmt->bind_param("sss", $search, $search, $search);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Filtra FAQs por categoria específica.
     */
    public function filterByCategory($categoria) {
        $stmt = $this->db->prepare("
            SELECT *
            FROM faqs
            WHERE categoria = ?
            ORDER BY ordem ASC
        ");
        $stmt->bind_param("s", $categoria);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
