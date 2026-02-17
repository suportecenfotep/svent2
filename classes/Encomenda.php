<?php
require_once '../config/Config.php';
require_once 'ItensEncomenda.php';

class Encomenda
{
    private $db;
    private $itensEncomendaManager;

    // Campos da tabela 'encomendas'
    public $id;
    public $cliente_id;
    public $lat;
    public $lng;
    public $subtotal;
    public $status = 'Novo';
    public $createdAt;
    public $updatedAt;

    public function __construct()
    {
        $config = new Config();
        $this->db = $config->dbConnect();
        $this->itensEncomendaManager = new ItensEncomenda();
    }

    // -------------------------------------------------------------------------
    // CRUD
    // -------------------------------------------------------------------------

    /**
     * Cria apenas a encomenda
     */
    private function createEncomendaOnly()
    {
        $stmt = $this->db->prepare("
            INSERT INTO encomendas 
                (cliente_id, lat, lng, subtotal, status)
            VALUES (?, ?, ?, ?, ?)
        ");

        $stmt->bind_param(
            "iddds",
            $this->cliente_id,
            $this->lat,
            $this->lng,
            $this->subtotal,
            $this->status
        );

        if ($stmt->execute()) {
            $this->id = $this->db->insert_id;
            return true;
        }

        file_put_contents(
            "encomenda_error.log",
            "Erro ao criar encomenda: {$stmt->error}" . PHP_EOL,
            FILE_APPEND
        );

        return false;
    }

    /**
     * Cria encomenda + itens
     */
    public function create(array $carrinhoItens)
    {
        if (empty($carrinhoItens)) return false;

        if (!$this->createEncomendaOnly()) {
            return false;
        }

        $this->itensEncomendaManager->encomenda_id = $this->id;

        foreach ($carrinhoItens as $item) {
            $this->itensEncomendaManager->produto_id = (int) $item['produto_id'];
            $this->itensEncomendaManager->preco = (float) $item['preco'];
            $this->itensEncomendaManager->qtd = (int) $item['qtd'];
            $this->itensEncomendaManager->subtotal =
                (float) ($item['subtotal'] ?? ($item['preco'] * $item['qtd']));

            $this->itensEncomendaManager->create();
        }

        return true;
    }

    // -------------------------------------------------------------------------
    // CONSULTAS
    // -------------------------------------------------------------------------

    public function read($id)
    {
        $stmt = $this->db->prepare(
            $this->getBaseQuery() . " WHERE e.id = ? LIMIT 1"
        );
        $stmt->bind_param("i", $id);
        $stmt->execute();

        $encomenda = $stmt->get_result()->fetch_assoc();

        if ($encomenda) {
            $encomenda['itens'] =
                $this->itensEncomendaManager->listByEncomenda($id);
        }

        return $encomenda;
    }

    public function update()
    {
        $stmt = $this->db->prepare("
            UPDATE encomendas
            SET cliente_id = ?, lat = ?, lng = ?, subtotal = ?
            WHERE id = ?
        ");

        $stmt->bind_param(
            "idddi",
            $this->cliente_id,
            $this->lat,
            $this->lng,
            $this->subtotal,
            $this->id
        );

        return $stmt->execute();
    }

    public function delete()
    {
        $stmt = $this->db->prepare("DELETE FROM encomendas WHERE id = ?");
        $stmt->bind_param("i", $this->id);
        return $stmt->execute();
    }

    public function updateStatus($id, $newStatus)
    {
        $stmt = $this->db->prepare("
            UPDATE encomendas SET status = ? WHERE id = ?
        ");
        $stmt->bind_param("si", $newStatus, $id);
        return $stmt->execute();
    }

    // -------------------------------------------------------------------------
    // LISTAGENS
    // -------------------------------------------------------------------------

    private function attachItens(array $encomendas)
    {
        foreach ($encomendas as &$e) {
            $itens =
                $this->itensEncomendaManager->listByEncomenda($e['id']);
            $e['itens'] = $itens;
            $e['total_items'] = count($itens);
        }
        return $encomendas;
    }

    private function getBaseQuery()
    {
        return "
            SELECT 
                e.*,
                c.nome AS cliente_nome,
                c.telefone AS cliente_telefone,
                c.email AS cliente_email,
                c.endereco,
                p.nome AS pais_nome
            FROM encomendas e
            JOIN clientes c ON e.cliente_id = c.id
            LEFT JOIN paises p ON c.pais_id = p.id
        ";
    }

    public function listByCliente($clienteId)
    {
        $stmt = $this->db->prepare(
            $this->getBaseQuery() .
            " WHERE e.cliente_id = ? ORDER BY e.createdAt DESC"
        );
        $stmt->bind_param("i", $clienteId);
        $stmt->execute();

        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        return $this->attachItens($result);
    }

    public function listAll()
    {
        $result = $this->db->query(
            $this->getBaseQuery() . " ORDER BY e.createdAt DESC"
        );
        return $this->attachItens($result->fetch_all(MYSQLI_ASSOC));
    }

    public function listByStatus($status)
    {
        $stmt = $this->db->prepare(
            $this->getBaseQuery() .
            " WHERE e.status = ? ORDER BY e.createdAt DESC"
        );
        $stmt->bind_param("s", $status);
        $stmt->execute();

        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        return $this->attachItens($result);
    }

    public function filter($search)
    {
        $pattern = "%{$search}%";
        $id = is_numeric($search) ? (int) $search : 0;

        $stmt = $this->db->prepare(
            $this->getBaseQuery() . "
            WHERE 
                c.nome LIKE ? 
                OR c.telefone LIKE ? 
                OR c.email LIKE ? 
                OR e.id = ?
            ORDER BY e.createdAt DESC
        "
        );

        $stmt->bind_param("sssi", $pattern, $pattern, $pattern, $id);
        $stmt->execute();

        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        return $this->attachItens($result);
    }
}
