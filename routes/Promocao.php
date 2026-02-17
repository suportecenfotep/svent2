<?php
// Inclui as classes necessárias
require_once("../classes/Promocao.php"); // Classe Promocao
require_once("../config/Config.php"); // Mantido por consistência, se necessário para outras dependências

/**
 * Classe responsável por rotear as requisições HTTP
 * para os métodos da classe Promocao (CRUD e listagem).
 */
class PromocaoRouter {

    private $promocao; // Instância da classe Promocao

    /**
     * Construtor: Inicializa as dependências.
     */
    public function __construct() {
        // Assume que a classe Promocao está em ../classes/Promocao.php
        $this->promocao = new Promocao(); 
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
        return $this->response(false, "Ação inválida ou método não suportado.");
    }

    //---------------------------------------------------------
    //  MÉTODOS DE ROTEAMENTO (ACTIONS)
    //---------------------------------------------------------

    /**
     * Cria uma nova promoção. (Método POST)
     */
    private function create($data) {
        if (!isset($data['produto_id']) || !isset($data['desconto']) || !isset($data['data_inicio']) || !isset($data['data_fim'])) {
            return $this->response(false, "Campos obrigatórios ausentes (produto_id, desconto, data_inicio, data_fim).");
        }
        
        // Atribui as propriedades
        $this->promocao->produto_id     = (int)$data['produto_id'];
        $this->promocao->desconto       = (float)$data['desconto'];
        $this->promocao->data_inicio    = trim($data['data_inicio']);
        $this->promocao->data_fim       = trim($data['data_fim']);
        // user_id é opcional, pode ser NULL no banco
        $this->promocao->user_id        = isset($data['user_id']) ? (int)$data['user_id'] : null; 

        // Executa a criação
        if ($this->promocao->create()) {
            return $this->response(true, "Promoção criada com sucesso."); 
        }
        
        return $this->response(false, "Erro ao salvar o registro da promoção. Verifique a validade dos dados (Ex: Desconto entre 0 e 100).");
    }

    /**
     * Lê uma única promoção pelo ID. (Método GET)
     */
    private function read($data) {
        if (!isset($data['id'])) {
            return $this->response(false, "ID da promoção não informado.");
        }
        $dados = $this->promocao->read($data['id']);
        
        return $this->response(
            $dados ? true : false,
            $dados ? "Promoção encontrada." : "Promoção não encontrada.",
            $dados
        );
    }

    /**
     * Atualiza uma promoção existente. (Método PUT)
     */
    private function update($data) {
        if (!isset($data['id'])) {
            return $this->response(false, "ID da promoção não informado para atualização.");
        }

        // 1. Lê a promoção atual para garantir que os campos não são perdidos
        $current = $this->promocao->read($data['id']);
        if (!$current) {
            return $this->response(false, "Promoção não encontrada para atualização.");
        }

        // 2. Atribui propriedades (usa o valor novo ou o valor atual)
        $this->promocao->id             = (int)$data['id'];
        $this->promocao->produto_id     = $data['produto_id'] ?? $current['produto_id'];
        $this->promocao->user_id        = $data['user_id'] ?? $current['user_id'];
        $this->promocao->desconto       = $data['desconto'] ?? $current['desconto'];
        $this->promocao->data_inicio    = $data['data_inicio'] ?? $current['data_inicio'];
        $this->promocao->data_fim       = $data['data_fim'] ?? $current['data_fim'];

        // 3. Executa a atualização
        if ($this->promocao->update()) {
            return $this->response(true, "Promoção atualizada com sucesso.");
        }
        return $this->response(false, "Erro ao atualizar a promoção. Verifique os dados.");
    }

    /**
     * Deleta uma promoção pelo ID. (Método DELETE)
     */
    private function delete($data) {
        if (!isset($data['id'])) {
            return $this->response(false, "ID da promoção não informado para exclusão.");
        }
        
        $this->promocao->id = $data['id'];

        if ($this->promocao->delete()) {
            return $this->response(true, "Promoção excluída com sucesso.");
        }
        
        return $this->response(false, "Erro ao excluir promoção do banco de dados.");
    }

    //---------------------------------------------------------
    //  MÉTODOS DE LISTAGEM
    //---------------------------------------------------------

    /**
     * Lista todas as promoções de todos os produtos. (Método GET)
     */
    private function listAll($data = []) {
        $dados = $this->promocao->listAll();
        return $this->response(true, "Lista completa de promoções.", $dados);
    }
    
    /**
     * Lista apenas as promoções ATIVAS. (Método GET)
     */
    private function listActives($data = []) {
        $dados = $this->promocao->listActives();
        return $this->response(true, "Lista de promoções ativas.", $dados);
    }

    /**
     * Lista as promoções ATIVAS de um produto específico. (Método GET)
     * Requer 'produto_id' no $data.
     */
    private function listActivesByProduct($data) {
        if (!isset($data['produto_id'])) {
            return $this->response(false, "ID do produto é obrigatório para listar promoções ativas por produto.");
        }
        
        $produtoId = (int)$data['produto_id'];
        $dados = $this->promocao->listActivesByProduct($produtoId);
        
        return $this->response(true, "Lista de promoções ativas para o produto {$produtoId}.", $dados);
    }
    
    /**
     * Filtra as promoções com base em um termo de pesquisa e estado (ativa/expirada). (Método GET)
     */
    private function filter($data) {
        $searchTerm = $data['search_term'] ?? ''; // Nome do produto
        $filterDate = $data['filter_date'] ?? null; // 'active' ou 'expired'

        $dados = $this->promocao->filter($searchTerm, $filterDate);

        $message = "Filtro de promoções aplicado.";
        if (empty($dados)) {
            $message = "Nenhuma promoção encontrada com os critérios de filtro.";
        }

        return $this->response(true, $message, $dados);
    }

    /**
     * Lista promoções ativas com paginação (limit e offset)
     */
    private function listAllActivesByLimit($data) {
        $limit = isset($data['limit']) ? intval($data['limit']) : 10;
        $offset = isset($data['offset']) ? intval($data['offset']) : 0;

        $dados = $this->promocao->listAllActivesByLimit($limit, $offset);

        if (!empty($dados)) {
            return $this->response(true, "Promoções ativas carregadas com sucesso.", $dados);
        }

        return $this->response(true, "Nenhuma promoção ativa encontrada.", []);
    }

    //---------------------------------------------------------
    //  MÉTODO UTILITÁRIO
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
$router = new PromocaoRouter();
echo $router->handleRequest();