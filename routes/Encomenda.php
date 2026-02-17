<?php
require_once("../config/Config.php");
require_once("../classes/Encomenda.php");
require_once("../classes/ItensEncomenda.php");
require_once("../classes/Carrinho.php");

/**
 * Router que gerencia requisições relacionadas às encomendas.
 */
class EncomendaRouter
{
    private $encomenda;
    private $carrinho;

    public function __construct()
    {
        $this->encomenda = new Encomenda();
        $this->carrinho = new Carrinho();
    }

    /**
     * Ponto de entrada: processa a requisição e chama o método adequado.
     */
    public function handleRequest()
    {
        header('Content-Type: application/json');

        $method = $_SERVER["REQUEST_METHOD"];
        $data = [];
        $action = null;

        // POST / PUT
        if ($method === "POST" || $method === "PUT") {
            $raw = file_get_contents("php://input");
            $json = json_decode($raw, true);
            $data = $json ?: $_POST;
            $action = $data["action"] ?? null;
        }

        // GET
        if ($method === "GET" && isset($_GET["action"])) {
            $action = $_GET["action"];
            $data = $_GET;
        }

        if ($action && method_exists($this, $action)) {
            unset($data["action"]);
            return $this->{$action}($data);
        }

        return $this->response(false, "Ação inválida ou método não suportado.");
    }

    // =====================================================
    // AÇÕES PRINCIPAIS
    // =====================================================

    private function create($data)
    {
        if (!isset($data["cliente_id"], $data["subtotal"], $data["itens"])) {
            return $this->response(false, "Campos obrigatórios ausentes: cliente_id, subtotal e itens.");
        }

        $cliente_id = (int)$data["cliente_id"];
        $itens = $data["itens"];

        if (empty($itens) || !is_array($itens)) {
            return $this->response(false, "Nenhum item foi enviado.");
        }

        $this->encomenda->cliente_id = $cliente_id;
        $this->encomenda->lat = isset($data["lat"]) ? (float)$data["lat"] : null;
        $this->encomenda->lng = isset($data["lng"]) ? (float)$data["lng"] : null;
        $this->encomenda->subtotal = (float)$data["subtotal"];
        $this->encomenda->status = $data["status"] ?? "Novo";

        if ($this->encomenda->create($itens)) {
            $encomenda_id = $this->encomenda->id;
            $this->carrinho->clearCart($cliente_id);

            return $this->response(true, "Encomenda criada com sucesso.", [
                "encomenda_id" => $encomenda_id
            ]);
        }

        return $this->response(false, "Erro ao criar encomenda. Nenhuma alteração foi salva.");
    }

    private function read($data)
    {
        if (empty($data["id"])) {
            return $this->response(false, "ID não informado.");
        }

        $dados = $this->encomenda->read((int)$data["id"]);
        return $this->response(
            (bool)$dados,
            $dados ? "Encomenda encontrada." : "Encomenda não encontrada.",
            $dados
        );
    }

    private function update($data)
    {
        if (empty($data["id"]) || empty($data["cliente_id"]) || empty($data["subtotal"])) {
            return $this->response(false, "Campos obrigatórios ausentes.");
        }

        $this->encomenda->id = (int)$data["id"];
        $this->encomenda->cliente_id = (int)$data["cliente_id"];
        $this->encomenda->lat = isset($data["lat"]) ? (float)$data["lat"] : null;
        $this->encomenda->lng = isset($data["lng"]) ? (float)$data["lng"] : null;
        $this->encomenda->subtotal = (float)$data["subtotal"];

        $ok = $this->encomenda->update();
        return $this->response($ok, $ok ? "Encomenda atualizada com sucesso." : "Erro ao atualizar encomenda.");
    }

    private function delete($data)
    {
        if (empty($data["id"])) {
            return $this->response(false, "ID não informado.");
        }

        $this->encomenda->id = (int)$data["id"];
        $ok = $this->encomenda->delete();

        return $this->response($ok, $ok ? "Encomenda excluída." : "Erro ao excluir encomenda.");
    }

    private function updateStatus($data)
    {
        if (empty($data["id"]) || empty($data["status"])) {
            return $this->response(false, "ID e novo status são obrigatórios.");
        }

        $ok = $this->encomenda->updateStatus((int)$data["id"], $data["status"]);
        return $this->response($ok, $ok ? "Status atualizado." : "Erro ao atualizar status.");
    }

    // =====================================================
    // LISTAGENS
    // =====================================================

    private function listAll()
    {
        $dados = $this->encomenda->listAll();
        return $this->response(true, "Lista de encomendas carregada.", $dados);
    }

    private function listByCliente($data)
    {
        if (empty($data["cliente_id"])) {
            return $this->response(false, "cliente_id é obrigatório.");
        }

        $dados = $this->encomenda->listByCliente((int)$data["cliente_id"]);
        return $this->response(true, "Lista de encomendas do cliente.", $dados);
    }

    private function listByStatus($data)
    {
        if (empty($data["status"])) {
            return $this->response(false, "Status é obrigatório.");
        }

        $dados = $this->encomenda->listByStatus($data["status"]);
        return $this->response(true, "Lista de encomendas por status.", $dados);
    }

    private function filter($data)
    {
        if (empty($data["keyword"])) {
            return $this->response(false, "Palavra-chave é obrigatória.");
        }

        $dados = $this->encomenda->filter($data["keyword"]);
        return $this->response(true, "Resultados da pesquisa.", $dados);
    }

    // =====================================================
    // RESPOSTA PADRÃO
    // =====================================================

    private function response($success, $message, $data = null)
    {
        return json_encode([
            "success" => $success,
            "message" => $message,
            "data" => $data
        ]);
    }
}

// =====================================================
// EXECUÇÃO
// =====================================================
$router = new EncomendaRouter();
echo $router->handleRequest();
