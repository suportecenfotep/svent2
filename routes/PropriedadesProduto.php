<?php
// Inclui as classes necessárias
require_once("../classes/PropriedadesProduto.php"); // Classe PropriedadesProduto
require_once("../config/Config.php"); // Necessário apenas para o construtor, mas mantido por consistência

/**
 * Classe responsável por rotear as requisições HTTP
 * para os métodos da classe PropriedadesProduto (CRUD e listagem).
 */
class PropriedadesProdutoRouter {

    private $propriedadesProduto; // Instância da classe PropriedadesProduto

    /**
     * Construtor: Inicializa as dependências.
     */
    public function __construct() {
        // Assume que a classe PropriedadesProduto está em ../classes/PropriedadesProduto.php
        $this->propriedadesProduto = new PropriedadesProduto(); 
        // Não precisamos da Config aqui, pois não há upload/delete de arquivos,
        // mas mantemos o padrão de dependência.
    }

    /**
     * Roteia a requisição HTTP (GET, POST, PUT ou DELETE) para o método de ação apropriado.
     */
    public function handleRequest() {
        // Define o cabeçalho de resposta como JSON
        header('Content-Type: application/json');
        
        $method = $_SERVER['REQUEST_METHOD'];
        $data = [];
        $action = null; 

        // 1. Coleta os dados baseados no método HTTP
        if ($method === 'POST') {
            // Para CREATE (POST)
            $data = $_POST;
        } elseif ($method === 'PUT') {
            // Para UPDATE (PUT) - Lê dados do corpo da requisição
            parse_str(file_get_contents("php://input"), $data);
        } elseif ($method === 'GET' || $method === 'DELETE') {
            // Para READ/LIST/DELETE (via query string)
            $data = $_GET; 
        }

        // 2. Extrai e valida a ação
        if (isset($data['action']) && is_string($data['action'])) {
            $action = $data['action'];
            unset($data['action']); 
        }

        // 3. Executa a ação se ela for válida
        if ($action && method_exists($this, $action)) {
            return $this->{$action}($data);
        }
        
        // 4. Resposta para requisição inválida ou sem ação suportada
        return $this->response(false, "Ação inválida ou método não suportado");
    }

    //---------------------------------------------------------
    //  MÉTODOS DE ROTEAMENTO (ACTIONS)
    //---------------------------------------------------------

    /**
     * Cria uma nova propriedade para um produto. (Método POST)
     */
    private function create($data) {
        if (!isset($data['produto_id']) || !isset($data['propriedade']) || !isset($data['valor'])) {
            return $this->response(false, "Campos obrigatórios ausentes (produto_id, propriedade, valor).");
        }
        
        // Atribui as propriedades
        $this->propriedadesProduto->produto_id  = $data['produto_id'];
        $this->propriedadesProduto->propriedade = trim($data['propriedade']);
        $this->propriedadesProduto->valor       = trim($data['valor']);
        // Status opcional, mantém o default 1 se não for passado
        $this->propriedadesProduto->status      = isset($data['status']) ? (int)$data['status'] : 1; 

        // Executa a criação
        if ($this->propriedadesProduto->create()) {
            return $this->response(true, "Propriedade adicionada com sucesso."); 
        }
        
        return $this->response(false, "Erro ao salvar o registro da propriedade no banco. Verifique se a propriedade já existe para este produto.");
    }

    /**
     * Lê uma única propriedade pelo ID. (Método GET)
     */
    private function read($data) {
        if (!isset($data['id'])) {
            return $this->response(false, "ID da propriedade não informado.");
        }
        $dados = $this->propriedadesProduto->read($data['id']);
        
        return $this->response(
            $dados ? true : false,
            $dados ? "Propriedade encontrada." : "Propriedade não encontrada.",
            $dados
        );
    }

    /**
     * Atualiza uma propriedade existente. (Método PUT)
     */
    private function update($data) {
        if (!isset($data['id'])) {
            return $this->response(false, "ID da propriedade não informado para atualização.");
        }

        // 1. Lê a propriedade atual para garantir que os campos não são perdidos
        $current = $this->propriedadesProduto->read($data['id']);
        if (!$current) {
            return $this->response(false, "Propriedade não encontrada para atualização.");
        }

        // 2. Atribui propriedades (usa o valor novo ou o valor atual)
        $this->propriedadesProduto->id          = $data['id'];
        $this->propriedadesProduto->produto_id  = $data['produto_id'] ?? $current['produto_id'];
        $this->propriedadesProduto->propriedade = $data['propriedade'] ?? $current['propriedade'];
        $this->propriedadesProduto->valor       = $data['valor'] ?? $current['valor'];
        $this->propriedadesProduto->status      = isset($data['status']) ? (int)$data['status'] : (int)$current['status'];

        // 3. Executa a atualização
        if ($this->propriedadesProduto->update()) {
            return $this->response(true, "Propriedade atualizada com sucesso");
        }
        return $this->response(false, "Erro ao atualizar a propriedade. Verifique duplicidade.");
    }

    /**
     * Deleta uma propriedade pelo ID. (Método DELETE)
     */
    private function delete($data) {
        if (!isset($data['id'])) {
            return $this->response(false, "ID da propriedade não informado para exclusão.");
        }
        
        $this->propriedadesProduto->id = $data['id'];

        if ($this->propriedadesProduto->delete()) {
            return $this->response(true, "Propriedade excluída com sucesso");
        }
        
        return $this->response(false, "Erro ao excluir propriedade do banco de dados.");
    }

    /**
     * Lista todas as propriedades de um produto específico. (Método GET)
     * Requer 'produto_id' no $data.
     */
    private function listByProduto($data) {
        if (!isset($data['produto_id'])) {
            return $this->response(false, "ID do produto é obrigatório para listar propriedades.");
        }
        
        $produtoId = $data['produto_id'];
        $dados = $this->propriedadesProduto->listByProduto($produtoId);
        
        return $this->response(true, "Lista de propriedades por produto.", $dados);
    }

    /**
     * Lista todas as propriedades ativas de um produto específico. (Método GET)
     * Requer 'produto_id' no $data.
     */
    private function listActiveByProduto($data) {
        if (!isset($data['produto_id'])) {
            return $this->response(false, "ID do produto é obrigatório para listar propriedades ativas.");
        }
        
        $produtoId = $data['produto_id'];
        $dados = $this->propriedadesProduto->listActiveByProduto($produtoId);
        
        return $this->response(true, "Lista de propriedades ativas por produto.", $dados);
    }

    /**
     * Lista todas as propriedades de todos os produtos. (Método GET)
     */
    private function listAll($data = []) {
        $dados = $this->propriedadesProduto->listAll();
        return $this->response(true, "Lista completa de propriedades.", $dados);
    }

    //---------------------------------------------------------
    //  MÉTODO UTILITÁRIO
    //---------------------------------------------------------

    /**
     * Retorna uma resposta padronizada em formato JSON.
     */
    private function response($success, $message, $data = null) {
        // Usa JSON_UNESCAPED_UNICODE para garantir que acentos e caracteres especiais não sejam codificados
        return json_encode([
            "success" => $success,
            "message" => $message,
            "data" => $data
        ], JSON_UNESCAPED_UNICODE);
    }
}

// ---------------------------------------------------------

// Ponto de entrada do script: Executa o router
$router = new PropriedadesProdutoRouter();
echo $router->handleRequest();