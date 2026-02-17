<?php
// Inclui as classes necessárias
require_once("../classes/Pais.php"); // Altera para a classe Pais
require_once("../config/Config.php");

/**
 * Classe responsável por rotear as requisições HTTP
 * para os métodos da classe Pais (CRUD e Listagem).
 */
class PaisRouter {

    private $pais; // Instância da classe Pais
    private $config;

    /**
     * Construtor: Inicializa as dependências.
     */
    public function __construct() {
        // Assume que a classe Pais está em ../classes/Pais.php
        $this->pais = new Pais(); 
        $this->config = new Config();
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

        // 2. Processa dados para requisições GET (para read, listAll, filter)
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
     * Cria um novo país.
     */
    private function create($data) {
        // Lógica de Upload de foto: Prioriza $_FILES, se não houver, usa nulo.
        if (isset($_FILES['foto'])) {
            $uploadedFileName = $this->config->upload($_FILES['foto']);
            if ($uploadedFileName) {
                $this->pais->foto = $uploadedFileName;
            } else {
                return $this->response(false, "Erro ao fazer upload da foto.");
            }
        } else {
            // Se o upload for via JSON (não recomendado para arquivos, mas aceita nome da foto)
            $this->pais->foto = $data['foto'] ?? null; 
        }

        // Atribui as demais propriedades
        $this->pais->nome       = $data['nome'] ?? null;
        $this->pais->codigo     = $data['codigo'] ?? null;
        $this->pais->moeda      = $data['moeda'] ?? null;
        $this->pais->idioma     = $data['idioma'] ?? null; // Assume que o front-end envia 'PT', 'EN', etc.
        $this->pais->status     = $data['status'] ?? 1;

        // Validação básica para campos obrigatórios (nome e idioma)
        if (empty($this->pais->nome) || empty($this->pais->idioma)) {
            return $this->response(false, "Campos obrigatórios (nome, idioma) não podem estar vazios.");
        }

        if ($this->pais->create()) {
            return $this->response(true, "País criado com sucesso");
        }
        return $this->response(false, "Erro ao criar país. O nome ou código do país pode já existir.");
    }

    /**
     * Atualiza um país existente.
     */
    private function update($data) {
        if (!isset($data['id'])) {
            return $this->response(false, "ID do país não informado para atualização.");
        }

        // Lógica de Upload de foto: Trata foto existente ou nova.
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $uploadedFileName = $this->config->upload($_FILES['foto']);
            if ($uploadedFileName) {
                $this->pais->foto = $uploadedFileName;
            } else {
                return $this->response(false, "Erro ao fazer upload da nova foto.");
            }
        } else {
            // Mantém a foto existente se não houver novo upload
            $this->pais->foto = $data['foto'] ?? null; 
        }

        // Atribui as propriedades
        $this->pais->id         = $data['id'];
        $this->pais->nome       = $data['nome'] ?? null;
        $this->pais->codigo     = $data['codigo'] ?? null;
        $this->pais->moeda      = $data['moeda'] ?? null;
        $this->pais->idioma     = $data['idioma'] ?? null;
        $this->pais->status     = $data['status'] ?? 1;

        if ($this->pais->update()) {
            return $this->response(true, "País atualizado com sucesso");
        }
        return $this->response(false, "Erro ao atualizar país");
    }

    /**
     * Deleta um país.
     */
    private function delete($data) {
        if (!isset($data['id'])) {
            return $this->response(false, "ID do país não informado para exclusão.");
        }
        
        $this->pais->id = $data['id'];

        if ($this->pais->delete()) {
            return $this->response(true, "País excluído com sucesso");
        }
        return $this->response(false, "Erro ao excluir país. Pode haver regiões associadas.");
    }

    /**
     * Lista todos os países.
     */
    private function listAll($data = []) {
        $dados = $this->pais->listAll();
        return json_encode([
            "success" => true,
            "data" => $dados
        ]);
    }

    /**
     * Lê um único país pelo ID.
     */
    private function read($data) {
        if (!isset($data['id'])) {
            return $this->response(false, "ID não informado");
        }
        $dados = $this->pais->read($data['id']);
        return json_encode([
            "success" => $dados ? true : false,
            "data" => $dados
        ]);
    }

    /**
     * Filtra países por palavra-chave.
     */
    private function filter($data) {
        if (!isset($data['keyword'])) {
            return $this->response(false, "Palavra-chave não informada");
        }
        $dados = $this->pais->filter($data['keyword']);
        return json_encode([
            "success" => true,
            "data" => $dados
        ]);
    }

    // O router de País não precisa de login/logout, então removemos esses métodos.

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
$router = new PaisRouter();
echo $router->handleRequest();