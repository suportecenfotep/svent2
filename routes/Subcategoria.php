<?php
// Inclui as classes necessárias
require_once("../classes/Subcategoria.php"); // Altera para a classe Subcategoria
require_once("../config/Config.php");

/**
 * Classe responsável por rotear as requisições HTTP
 * para os métodos da classe Subcategoria (CRUD e Listagem).
 */
class SubcategoriaRouter {

    private $subcategoria; // Instância da classe Subcategoria
    private $config;

    /**
     * Construtor: Inicializa as dependências.
     */
    public function __construct() {
        // Assume que a classe Subcategoria está em ../classes/Subcategoria.php
        $this->subcategoria = new Subcategoria(); 
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
            // Tenta obter dados JSON
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

        // 2. Processa dados para requisições GET (para read, listAll, filter, listByCategoria)
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
     * Cria uma nova subcategoria.
     */
    private function create($data) {
        // Atribui as propriedades
        $this->subcategoria->categoria_id = $data['categoria_id'] ?? null;
        $this->subcategoria->nome         = $data['nome'] ?? null;
        $this->subcategoria->descricao    = $data['descricao'] ?? null;
        $this->subcategoria->status       = $data['status'] ?? 1;

        // Validação básica para campos obrigatórios
        if (empty($this->subcategoria->categoria_id) || empty($this->subcategoria->nome)) {
            return $this->response(false, "Os campos 'categoria_id' e 'nome' são obrigatórios.");
        }

        if ($this->subcategoria->create()) {
            return $this->response(true, "Subcategoria criada com sucesso");
        }
        return $this->response(false, "Erro ao criar subcategoria. O nome pode já existir para esta categoria.");
    }

    /**
     * Atualiza uma subcategoria existente.
     */
    private function update($data) {
        if (!isset($data['id'])) {
            return $this->response(false, "ID da subcategoria não informado para atualização.");
        }

        // Atribui as propriedades
        $this->subcategoria->id           = $data['id'];
        $this->subcategoria->categoria_id = $data['categoria_id'] ?? null;
        $this->subcategoria->nome         = $data['nome'] ?? null;
        $this->subcategoria->descricao    = $data['descricao'] ?? null;
        $this->subcategoria->status       = $data['status'] ?? 1;

        // Validação básica
        if (empty($this->subcategoria->categoria_id) || empty($this->subcategoria->nome)) {
            return $this->response(false, "Os campos 'categoria_id' e 'nome' são obrigatórios.");
        }

        if ($this->subcategoria->update()) {
            return $this->response(true, "Subcategoria atualizada com sucesso");
        }
        return $this->response(false, "Erro ao atualizar subcategoria. Verifique o ID e os dados.");
    }

    /**
     * Deleta uma subcategoria.
     */
    private function delete($data) {
        if (!isset($data['id'])) {
            return $this->response(false, "ID da subcategoria não informado para exclusão.");
        }
        
        $this->subcategoria->id = $data['id'];

        if ($this->subcategoria->delete()) {
            return $this->response(true, "Subcategoria excluída com sucesso");
        }
        return $this->response(false, "Erro ao excluir subcategoria. Pode haver produtos associados.");
    }

    /**
     * Lista todas as subcategorias.
     */
    private function listAll($data = []) {
        $dados = $this->subcategoria->listAll();
        return json_encode([
            "success" => true,
            "data" => $dados
        ]);
    }
    
    /**
     * Lista subcategorias pelo ID da Categoria.
     */
    private function listByCategoria($data) {
        if (!isset($data['categoria_id'])) {
            return $this->response(false, "ID da categoria não informado.");
        }
        $categoria_id = $data['categoria_id'];
        
        $dados = $this->subcategoria->listByCategoria($categoria_id);
        return json_encode([
            "success" => true,
            "data" => $dados,
            "message" => "Subcategorias listadas para Categoria ID {$categoria_id}."
        ]);
    }

    /**
     * Lê uma única subcategoria pelo ID.
     */
    private function read($data) {
        if (!isset($data['id'])) {
            return $this->response(false, "ID não informado");
        }
        $dados = $this->subcategoria->read($data['id']);
        return json_encode([
            "success" => $dados ? true : false,
            "data" => $dados
        ]);
    }

    /**
     * Filtra subcategorias por palavra-chave (nome, descrição ou nome da categoria).
     */
    private function filter($data) {
        if (!isset($data['keyword'])) {
            return $this->response(false, "Palavra-chave não informada");
        }
        $dados = $this->subcategoria->filter($data['keyword']);
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
$router = new SubcategoriaRouter();
echo $router->handleRequest();