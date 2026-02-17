<?php
// Inclui as classes necessárias
require_once("../classes/Categoria.php"); // Classe Categoria
require_once("../config/Config.php");

/**
 * Classe responsável por rotear as requisições HTTP
 * para os métodos da classe Categoria (CRUD e Listagem).
 */
class CategoriaRouter {

    private $categoria; // Instância da classe Categoria
    private $config;

    /**
     * Construtor: Inicializa as dependências.
     */
    public function __construct() {
        // Assume que a classe Categoria está em ../classes/Categoria.php
        $this->categoria = new Categoria(); 
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

        // 2. Processa dados para requisições GET (para read, listAll, listSix, filter)
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
    // 	MÉTODOS DE ROTEAMENTO (ACTIONS)
    //---------------------------------------------------------

    /**
     * Cria uma nova categoria.
     */
    private function create($data) {
        // Lógica de Upload de foto: Prioriza $_FILES, se não houver, usa nulo.
        if (isset($_FILES['foto'])) {
            // Assumimos que o método upload da sua classe Config/Utility está adaptado para categorias
            $uploadedFileName = $this->config->upload($_FILES['foto'], 'categorias'); 
            if ($uploadedFileName) {
                $this->categoria->foto = $uploadedFileName;
            } else {
                return $this->response(false, "Erro ao fazer upload da foto.");
            }
        } else {
            // Se o upload for via JSON (não recomendado para arquivos, mas aceita nome da foto)
            $this->categoria->foto = $data['foto'] ?? null; 
        }

        // Atribui as demais propriedades (apenas nome, descricao, status)
        $this->categoria->nome 		= $data['nome'] ?? null;
        $this->categoria->descricao = $data['descricao'] ?? null;
        $this->categoria->status 	= $data['status'] ?? 1; // Default status 1 (ativo)

        // Validação básica para campos obrigatórios (nome)
        if (empty($this->categoria->nome)) {
            return $this->response(false, "O campo 'nome' é obrigatório.");
        }

        if ($this->categoria->create()) {
            return $this->response(true, "Categoria criada com sucesso");
        }
        return $this->response(false, "Erro ao criar categoria. O nome pode já existir.");
    }

    /**
     * Atualiza uma categoria existente.
     */
    private function update($data) {
        if (!isset($data['id'])) {
            return $this->response(false, "ID da categoria não informado para atualização.");
        }

        // Lógica de Upload de foto: Trata foto existente ou nova.
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $uploadedFileName = $this->config->upload($_FILES['foto'], 'categorias');
            if ($uploadedFileName) {
                $this->categoria->foto = $uploadedFileName;
                // NOTA: A lógica para DELETAR a foto antiga deve estar na classe Categoria (método update), 
                // para garantir que a foto só seja deletada se o update no DB for bem-sucedido.
            } else {
                return $this->response(false, "Erro ao fazer upload da nova foto.");
            }
        } else {
            // Mantém o nome da foto existente (deve ser enviado no POST/JSON se não houver novo upload)
            $this->categoria->foto = $data['foto'] ?? null; 
        }

        // Atribui as propriedades
        $this->categoria->id 		= $data['id'];
        $this->categoria->nome 		= $data['nome'] ?? null;
        $this->categoria->descricao = $data['descricao'] ?? null;
        $this->categoria->status 	= $data['status'] ?? 1;

        if ($this->categoria->update()) {
            return $this->response(true, "Categoria atualizada com sucesso");
        }
        return $this->response(false, "Erro ao atualizar categoria. Verifique o ID ou se o nome já existe.");
    }

    /**
     * Deleta uma categoria.
     */
    private function delete($data) {
        if (!isset($data['id'])) {
            return $this->response(false, "ID da categoria não informado para exclusão.");
        }
        
        $this->categoria->id = $data['id'];

        if ($this->categoria->delete()) {
            // NOTA: A lógica para DELETAR o arquivo da foto deve estar na classe Categoria (método delete).
            return $this->response(true, "Categoria excluída com sucesso");
        }
        return $this->response(false, "Erro ao excluir categoria. Pode haver subcategorias ou produtos associados.");
    }

    /**
     * Lista todas as categorias.
     */
    private function listAll($data = []) {
        $dados = $this->categoria->listAll();
        return json_encode([
            "success" => true,
            "data" => $dados
        ]);
    }

    /**
     * Lista as 6 primeiras categorias.
     */
    private function listSix($data = []) {
        $dados = $this->categoria->listSix();
        return json_encode([
            "success" => true,
            "data" => $dados
        ]);
    }

    /**
     * Lê uma única categoria pelo ID.
     */
    private function read($data) {
        if (!isset($data['id'])) {
            return $this->response(false, "ID não informado");
        }
        $dados = $this->categoria->read($data['id']);
        return json_encode([
            "success" => $dados ? true : false,
            "data" => $dados,
            "message" => $dados ? "Categoria encontrada." : "Categoria não encontrada."
        ]);
    }

    /**
     * Filtra categorias por palavra-chave (nome ou descrição).
     */
    private function filter($data) {
        if (!isset($data['keyword'])) {
            return $this->response(false, "Palavra-chave não informada");
        }
        $dados = $this->categoria->filter($data['keyword']);
        return json_encode([
            "success" => true,
            "data" => $dados
        ]);
    }

    //---------------------------------------------------------
    // 	MÉTODO UTILITÁRIO
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
$router = new CategoriaRouter();
echo $router->handleRequest();