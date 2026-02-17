<?php
// Inclui as classes necessárias. Certifique-se de que o caminho está correto.
require_once("../classes/Carrinho.php"); // Classe Carrinho que você criou
require_once("../classes/Cliente.php"); // Classe Cliente para possíveis verificações de autenticação (opcional)
require_once("../config/Config.php"); // Classe Config para utilitários (embora o CarrinhoRouter não use o upload)

/**
 * Classe responsável por rotear as requisições HTTP
 * para os métodos da classe Carrinho (CRUD e listByCliente).
 */
class CarrinhoRouter {

    private $carrinho;

    /**
     * Construtor: Inicializa as dependências.
     */
    public function __construct() {
        $this->carrinho = new Carrinho();
    }

    /**
     * Roteia a requisição HTTP (GET ou POST) para o método de ação apropriado.
     */
    public function handleRequest() {
        // Define o cabeçalho de resposta como JSON
        header('Content-Type: application/json');
        
        $method = $_SERVER['REQUEST_METHOD'];
        $data = [];

        // 1. Processa dados para requisições POST (CREATE/UPDATE/DELETE/CLEAR)
        if ($method === 'POST') {
            $json_data = json_decode(file_get_contents("php://input"), true);
            $data = $json_data ? $json_data : $_POST;
            
            // Verifica a ação e executa o método
            if (isset($data['action']) && method_exists($this, $data['action'])) {
                return $this->{$data['action']}($data);
            }
        }

        // 2. Processa dados para requisições GET (READ/LISTBYCLIENTE)
        if ($method === 'GET' && isset($_GET['action'])) {
            $action = $_GET['action'];
            $data = $_GET; // Os dados para GET vêm de $_GET
            
            if (method_exists($this, $action)) {
                return $this->$action($data);
            }
        }

        // 3. Resposta para requisição inválida
        return $this->response(false, "Ação inválida ou método não suportado");
    }

    //---------------------------------------------------------
    //  MÉTODOS DE ROTEAMENTO (ACTIONS)
    //---------------------------------------------------------

    /**
     * Adiciona ou atualiza a quantidade de um produto no carrinho.
     * Mapeia para o método createOrUpdate da classe Carrinho.
     */
    private function createOrUpdate($data) {
        // Validação básica
        if (!isset($data['cliente_id']) || !isset($data['produto_id']) || !isset($data['qtd']) || $data['qtd'] <= 0) {
            return $this->response(false, "Dados incompletos ou quantidade inválida.");
        }

        // Atribui propriedades
        $this->carrinho->cliente_id = $data['cliente_id'];
        $this->carrinho->produto_id = $data['produto_id'];
        $this->carrinho->qtd        = (int)$data['qtd'];

        if ($this->carrinho->createOrUpdate()) {
            return $this->response(true, "Produto adicionado/atualizado no carrinho com sucesso.");
        }
        return $this->response(false, "Erro ao adicionar/atualizar produto no carrinho.");
    }

    /**
     * Atualiza a quantidade de um item no carrinho.
     * Mapeia para o método updateQtd da classe Carrinho.
     */
    private function updateQtd($data) {
        if (!isset($data['id']) || !isset($data['cliente_id']) || !isset($data['qtd'])) {
            return $this->response(false, "ID do item, ID do cliente e nova quantidade são obrigatórios.");
        }
        
        $this->carrinho->id         = $data['id'];
        $this->carrinho->cliente_id = $data['cliente_id'];
        $this->carrinho->qtd        = (int)$data['qtd'];

        if ($this->carrinho->qtd <= 0) {
             // Redireciona para o método de exclusão se a quantidade for zero ou negativa
             return $this->delete($data); 
        }

        if ($this->carrinho->updateQtd()) {
            return $this->response(true, "Quantidade atualizada com sucesso.");
        }
        return $this->response(false, "Erro ao atualizar a quantidade. Item pode não existir.");
    }

    /**
     * Remove um item específico do carrinho.
     * Mapeia para o método delete da classe Carrinho.
     */
    private function delete($data) {
        if (!isset($data['id']) || !isset($data['cliente_id'])) {
            return $this->response(false, "ID do item e ID do cliente são obrigatórios para exclusão.");
        }
        
        $this->carrinho->id         = $data['id'];
        $this->carrinho->cliente_id = $data['cliente_id'];

        if ($this->carrinho->delete()) {
            return $this->response(true, "Item removido do carrinho com sucesso.");
        }
        return $this->response(false, "Erro ao remover item do carrinho. Item pode não existir.");
    }

    /**
     * Lista todos os itens no carrinho de um cliente.
     * Mapeia para o método listByCliente da classe Carrinho.
     */
    private function listByCliente($data) {
        if (!isset($data['cliente_id'])) {
            return $this->response(false, "ID do cliente é obrigatório.");
        }

        $this->carrinho->cliente_id = $data['cliente_id'];
        $dados = $this->carrinho->listByCliente();

        if ($dados === false) {
             return $this->response(false, "Erro ao buscar itens do carrinho.");
        }

        // Calcula o total geral do carrinho
        $total_geral = array_sum(array_column($dados, 'subtotal_linha'));
        
        return json_encode([
            "success" => true,
            "message" => "Itens do carrinho listados com sucesso.",
            "data" => $dados,
            "total_carrinho" => number_format($total_geral, 2, '.', '')
        ]);
    }

    /**
     * Limpa completamente o carrinho de um cliente.
     * Mapeia para o método clearCart da classe Carrinho.
     */
    private function clearCart($data) {
        if (!isset($data['cliente_id'])) {
            return $this->response(false, "ID do cliente é obrigatório.");
        }

        if ($this->carrinho->clearCart($data['cliente_id'])) {
            return $this->response(true, "Carrinho limpo com sucesso.");
        }
        return $this->response(false, "Erro ao limpar o carrinho.");
    }

    //---------------------------------------------------------
    //  MÉTODO UTILITÁRIO
    //---------------------------------------------------------

    /**
     * Retorna uma resposta padronizada em formato JSON.
     */
    private function response($success, $message, $data = null) {
        return json_encode([
            "success" => $success,
            "message" => $message,
            "data" => $data
        ]);
    }
}

// ---------------------------------------------------------

// Ponto de entrada do script: Executa o router
$router = new CarrinhoRouter();
echo $router->handleRequest();