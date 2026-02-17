<?php
// Inclui as classes necessárias
require_once("../classes/Regiao.php"); // Altera para a classe Regiao
require_once("../config/Config.php");

/**
 * Classe responsável por rotear as requisições HTTP
 * para os métodos da classe Regiao (CRUD, Listagem e Filtragem).
 */
class RegiaoRouter {

    private $regiao; // Instância da classe Regiao
    private $config;

    /**
     * Construtor: Inicializa as dependências.
     */
    public function __construct() {
        // Assume que a classe Regiao está em ../classes/Regiao.php
        $this->regiao = new Regiao(); 
        $this->config = new Config(); // Mantém o Config, embora não seja usado para upload aqui, é uma dependência comum.
    }

    /**
     * Roteia a requisição HTTP (GET ou POST) para o método de ação apropriado.
     */
    public function handleRequest() {
        // Define o cabeçalho de resposta como JSON
        header('Content-Type: application/json');
        
        $method = $_SERVER['REQUEST_METHOD'];
        $data = [];

        // 1. Processa dados para requisições POST/PUT (para CRUD: create, update, delete)
        if ($method === 'POST' || $method === 'PUT') {
            // Tenta obter dados JSON (para requisições com 'Content-Type: application/json')
            $json_data = json_decode(file_get_contents("php://input"), true);
            if ($json_data) {
                $data = $json_data;
            } else {
                // Caso contrário, usa $_POST (para form-data)
                $data = $_POST;
            }
            
            // Verifica a ação e executa o método
            if (isset($data['action']) && method_exists($this, $data['action'])) {
                return $this->{$data['action']}($data);
            }
        }

        // 2. Processa dados para requisições GET (para read, listAll, listByPais, filter)
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
    //  MÉTODOS DE ROTEAMENTO (ACTIONS)
    //---------------------------------------------------------

    /**
     * Cria uma nova região.
     */
    private function create($data) {
        
        // Atribui as propriedades
        $this->regiao->nome     = $data['nome'] ?? null;
        $this->regiao->pais_id  = $data['pais_id'] ?? null;
        $this->regiao->status   = $data['status'] ?? 1;

        // Validação básica para campos obrigatórios
        if (empty($this->regiao->nome) || empty($this->regiao->pais_id)) {
            return $this->response(false, "Campos obrigatórios (nome, pais_id) não podem estar vazios.");
        }

        if ($this->regiao->create()) {
            return $this->response(true, "Região criada com sucesso");
        }
        return $this->response(false, "Erro ao criar região. O nome da região pode já existir para este país.");
    }

    /**
     * Atualiza uma região existente.
     */
    private function update($data) {
        if (!isset($data['id'])) {
            return $this->response(false, "ID da região não informado para atualização.");
        }

        // Atribui as propriedades
        $this->regiao->id       = $data['id'];
        $this->regiao->nome     = $data['nome'] ?? null;
        $this->regiao->pais_id  = $data['pais_id'] ?? null;
        $this->regiao->status   = $data['status'] ?? 1;

        if ($this->regiao->update()) {
            return $this->response(true, "Região atualizada com sucesso");
        }
        return $this->response(false, "Erro ao atualizar região");
    }

    /**
     * Deleta uma região.
     */
    private function delete($data) {
        if (!isset($data['id'])) {
            return $this->response(false, "ID da região não informado para exclusão.");
        }
        
        $this->regiao->id = $data['id'];

        if ($this->regiao->delete()) {
            return $this->response(true, "Região excluída com sucesso");
        }
        return $this->response(false, "Erro ao excluir região. Pode haver distritos associados.");
    }

    /**
     * Lista todas as regiões.
     */
    private function listAll($data = []) {
        $dados = $this->regiao->listAll();
        return json_encode([
            "success" => true,
            "data" => $dados
        ]);
    }

    /**
     * Lê uma única região pelo ID.
     */
    private function read($data) {
        if (!isset($data['id'])) {
            return $this->response(false, "ID não informado");
        }
        $dados = $this->regiao->read($data['id']);
        return json_encode([
            "success" => $dados ? true : false,
            "data" => $dados
        ]);
    }

    /**
     * Lista regiões por ID do país.
     * Requer o parâmetro 'pais_id' na query string.
     */
    private function listByPais($data) {
        if (!isset($data['pais_id'])) {
            return $this->response(false, "ID do país ('pais_id') não informado para listagem.");
        }
        
        $paisId = (int) $data['pais_id'];

        $dados = $this->regiao->listByPais($paisId);

        return json_encode([
            "success" => true,
            "data" => $dados
        ]);
    }

    /**
     * Filtra regiões por palavra-chave.
     */
    private function filter($data) {
        if (!isset($data['keyword'])) {
            return $this->response(false, "Palavra-chave não informada");
        }
        $dados = $this->regiao->filter($data['keyword']);
        return json_encode([
            "success" => true,
            "data" => $dados
        ]);
    }

    //---------------------------------------------------------
    //  MÉTODO UTILITÁRIO
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
$router = new RegiaoRouter();
echo $router->handleRequest();